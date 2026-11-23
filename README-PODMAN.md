# VITTEZA — локальный запуск на Podman

Окружение состоит из двух контейнеров:

| Контейнер | Образ | Назначение |
|---|---|---|
| `vitteza-app` | `localhost/vitteza-app` (сборка из `Containerfile`) | Laravel 13 + Filament 5, Apache + mod_php |
| `vitteza-mysql` | `mysql:8.4` | База данных MySQL 8.4 |

Приложение собирается в один самодостаточный образ (многостадийная сборка):
`vendor` (composer, без dev-пакетов) → `frontend` (npm + Vite, тема Filament §17) →
`app` (PHP 8.4 + Apache). Код в образе, данные — в именованных томах
(`mysql_data`, `app_storage`).

## Быстрый старт

```bash
git clone https://github.com/eastlemon/vitteza.git
cd vitteza
podman compose up -d --build
```

После первого старта (сборка ~3–5 минут, инициализация MySQL ~30 секунд)
приложение доступно: **http://localhost:8080**

Панель администратора: **http://localhost:8080/admin**

| Учётная запись | Логин | Пароль |
|---|---|---|
| Администратор | `admin@vitteza.ru` | `password` (переменная `APP_ADMIN_PASSWORD`) |
| Демо-логист | `ivanov@vitteza.ru` | `password` (переменная `APP_DEMO_PASSWORD`) |

Менять пароли до запуска — через переменные в `docker-compose.yml` или файл
`.env` (создаётся из `.env.docker` при первом старте автоматически).

## Что делает entrypoint при старте контейнера

`docker/entrypoint.sh` выполняется при каждом запуске и идемпотентен:

1. Создаёт `.env` из `.env.docker`, если его нет.
2. Генерирует `APP_KEY`, если он пуст.
3. Выдаёт права `www-data` на `storage/` и `bootstrap/cache/`.
4. Ждёт готовности MySQL (до 60 сек, скрипт `docker/wait-for-db.php`).
5. Накатывает миграции (`php artisan migrate`).
6. Запускает сидеры: `MailDomainSeeder` (домен `vitteza.ru`), `AdminUserSeeder`
   (админ), `VittezaDemoSeeder` (демо-логисты, водители, заказчики, 8 рейсов).
   Все идемпотентны — повторный запуск ничего не ломает.
7. Выполняет `storage:link` и запускает Apache.

## Полезные команды

```bash
podman compose logs -f app                      # логи приложения
podman compose logs -f mysql                    # логи MySQL
podman exec -it vitteza-app php artisan tinker  # tinker внутри контейнера
podman compose up -d --build                    # пересборка после изменения кода
podman compose down                             # остановить (данные остаются)
podman compose down -v                          # остановить и стереть данные БД
```

Сброс базы с нуля:

```bash
podman compose down -v
podman compose up -d --build
```

## Конфигурация

Переменные окружения приложения задаются в `docker-compose.yml` (секция
`environment` сервиса `app`) и перекрывают `.env`. Основные:

| Переменная | По умолчанию | Описание |
|---|---|---|
| `APP_PORT` | `8080` | Порт приложения на хосте |
| `APP_URL` | `http://localhost:8080` | Внешний URL (ссылки, Vite) |
| `APP_ADMIN_PASSWORD` | `password` | Пароль администратора |
| `APP_DEMO_PASSWORD` | `password` | Пароль демо-логистов |
| `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` | `vitteza` / `vitteza` / `vitteza_dev_password` | Доступ к БД приложения |
| `MYSQL_ROOT_PASSWORD` | `root_dev_password` | root для MySQL |

Порт MySQL наружу не пробрасывается — база доступна только внутри сети compose.
Если нужен внешний доступ (например, для DBeaver), раскомментируйте секцию
`ports` у сервиса `mysql`:

```yaml
    ports:
      - "3306:3306"
```

## Разработка без пересборки образа

По умолчанию код запечён в образ. Для активной разработки раскомментируйте
монтирование кода в `docker-compose.yml` (секция `volumes` сервиса `app`):

```yaml
    volumes:
      - ./:/var/www/html:Z
      - app_storage:/var/www/html/storage
```

После этого изменения PHP-кода подхватываются сразу (opcache
`validate_timestamps=1`), а для ассетов используйте Vite-сборку на хосте:

```bash
npm ci && npm run dev     # dev-сервер Vite, hot reload
# или
npm run build             # прод-сборка в public/build
```

Флаг `:Z` обязателен для rootless-podman (SELinux-метки).

## Устранение неполадок

- **«MySQL did not become ready»** — увеличьте `DB_WAIT_TIMEOUT` (по умолчанию
  60 сек) в `environment` сервиса `app` на медленных машинах.
- **Порт 8080 занят** — `APP_PORT=9090 podman compose up -d`.
- **Странное поведение после смены паролей** — сидеры не перезаписывают
  существующего админа (`updateOrCreate` по email). Поменяйте пароль руками:
  `podman exec -it vitteza-app php artisan tinker` →
  `App\Models\User::where('email','admin@vitteza.ru')->update(['password' => 'новый'])`.
- **Права на томах rootless** — entrypoint сам делает `chown www-data`, при
  проблемах: `podman unshare chown -R 33:33 ./storage ./bootstrap/cache`.
- **Обновился образ MySQL** — `podman compose pull mysql && podman compose up -d --build`.

## Отличия от продакшена (ТЗ §4, §20.2)

Локальное окружение не включает почтовый контур (Postfix/Dovecot/OpenDKIM) и
Nginx с TLS: `MAIL_MAILER=log` складывает письма в лог контейнера. Миграции
почтовых таблиц при этом накатываются (схема §7.3), и ресурс «Сотрудники»
создаёт ящики в БД. Для полного почтового контура используйте боевую
инфраструктуру VDS по ТЗ.
