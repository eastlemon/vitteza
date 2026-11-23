#!/bin/sh
# VITTEZA entrypoint: подготовка окружения и запуск Apache (mod_php).
# Идемпотентен — безопасен при перезапуске контейнера.
set -e

: "${APP_ENV:=local}"
: "${APP_DEBUG:=true}"

WORKDIR=/var/www/html

# 1) .env из шаблона, если его нет (том может быть пустым при первом запуске)
if [ ! -f "$WORKDIR/.env" ]; then
    echo "[entrypoint] .env not found, copying .env.docker"
    cp "$WORKDIR/.env.docker" "$WORKDIR/.env"
fi

# 2) Ключ приложения (нужен при каждом старте: сессии, шифрование)
if ! grep -q "^APP_KEY=" "$WORKDIR/.env" || grep -q "^APP_KEY=$" "$WORKDIR/.env"; then
    echo "[entrypoint] generating APP_KEY"
    php artisan key:generate --force >/dev/null
fi

# 3) Права на записываемые директории (root-овым контейнером в rootless podman
#    часто пишется от www-data, а том мог быть создан root'ом)
chown -R www-data:www-data "$WORKDIR/storage" "$WORKDIR/bootstrap/cache" || true

# 4) Ждём готовности MySQL (compose healthcheck не гарантирует порядок
#    без depends_on condition, а migrate падает без соединения)
php /usr/local/bin/wait-for-db.php

# 5) Миграции + идемпотентные сидеры. FORCE не нужен: APP_ENV=local.
#    VittezaDemoSeeder сам выходит, если данные уже есть; MailDomain и Admin
#    используют updateOrCreate/firstOrCreate — повторный запуск безопасен.
php artisan migrate --force
php artisan db:seed --class=MailDomainSeeder --force
php artisan db:seed --class=AdminUserSeeder --force
php artisan db:seed --class=VittezaDemoSeeder --force

# 6) Статический симлинк на storage (не используется кодом сейчас, но
#    требуется стандартом Laravel и пригодится для вложений из ТЗ Д-6)
php artisan storage:link || true

echo "[entrypoint] starting apache2-foreground"
exec apache2-foreground
