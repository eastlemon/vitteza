# VITTEZA — локальное окружение на Podman.
#
# Многостадийная сборка:
#   vendor   — composer install (--no-dev, без скриптов)
#   frontend — npm ci + vite build (Tailwind 4 + кастомная тема Filament §17)
#   app      — php:8.4-apache + расширения из ТЗ §4.4 + код и ассеты
#
# Сборка:  podman build -t vitteza-app .
# Запуск:  podman compose up -d --build   (см. README-PODMAN.md)

########## Стадия 1: PHP-зависимости (composer) ##########
FROM docker.io/library/php:8.4-cli AS vendor
WORKDIR /app

# composer из официального образа — совместим с PHP 8.4 финальной стадии
COPY --from=docker.io/library/composer:2 /usr/bin/composer /usr/bin/composer

# Распаковка dist-архивов: в php:8.4-cli нет ни расширения zip, ни unzip
# («The zip extension and unzip/7z commands are both missing»). git —
# запасной вариант, если пакет придётся ставить из source.
RUN apt-get update && apt-get install -y --no-install-recommends \
        unzip \
        git \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Сначала только манифесты: слой с зависимостями кэшируется,
# пока не меняются composer.json / composer.lock.
COPY composer.json composer.lock ./
# Скрипты (package:discover, filament:upgrade) сознательно пропущены —
# они требуют полный код и все расширения, выполняем их в финальной стадии.
# --ignore-platform-reqs: расширения (gd/intl/pdo_mysql) появятся в стадии app.
RUN composer install \
        --no-dev \
        --no-interaction \
        --no-progress \
        --optimize-autoloader \
        --no-scripts \
        --ignore-platform-reqs

########## Стадия 2: фронтенд (Node + Vite) ##########
FROM docker.io/library/node:22-bookworm-slim AS frontend
WORKDIR /app

# .npmrc обязателен: ignore-scripts=true отключает lifecycle-скрипты
# optional-зависимостей (@laravel/multiplex).
COPY package.json package-lock.json .npmrc ./
RUN npm ci

# app.css импортирует vendor/filament/filament/dist/theme.css и сканирует
# @source vendor/filament/**/*.blade.php — нужен vendor из стадии 1.
COPY --from=vendor /app/vendor ./vendor
COPY vite.config.js ./
COPY resources ./resources
COPY storage ./storage
RUN npm run build

########## Стадия 3: финальный образ (Apache + mod_php) ##########
FROM docker.io/library/php:8.4-apache AS app

# Расширения из ТЗ §4.4: pdo_mysql (БД), gd (изображения), intl (локаль),
# zip, bcmath, exif; mysqli — для docker/wait-for-db.php (ожидание MySQL).
# mbstring, curl, openssl, sqlite уже собраны в базовом образе.
RUN apt-get update && apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libicu-dev \
        libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" gd intl zip bcmath exif pdo_mysql mysqli \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Apache: mod_rewrite для public/.htaccess; свой vhost с DocumentRoot=public
RUN a2enmod rewrite headers
COPY docker/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-vitteza.ini

WORKDIR /var/www/html

# Код приложения (что попадает в контекст — см. .dockerignore)
COPY . .

# Зависимости и собранный фронтенд из предыдущих стадий
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build

# Скрипты, которые composer обычно запускает после dump-autoload:
# здесь уже есть весь код и все расширения.
# Каталоги storage/framework/* попадают в образ как пустые (в .dockerignore
# исключено только их содержимое) — Laravel требует их для компиляции видов,
# сессий и кеша. mkdir/chown — ДО artisan-скриптов: package:discover пишет
# в storage и bootstrap/cache.
RUN mkdir -p storage/framework/cache/data storage/framework/cache/locks \
        storage/framework/sessions storage/framework/views storage/framework/testing \
        storage/logs storage/app/private storage/app/public \
    && chown -R www-data:www-data storage bootstrap/cache \
    && php artisan package:discover --ansi \
    && php artisan filament:upgrade --ansi

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
COPY docker/wait-for-db.php /usr/local/bin/wait-for-db.php
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
