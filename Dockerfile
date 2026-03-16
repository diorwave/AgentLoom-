# AI Workflow Platform - PHP 8.3 + Laravel
FROM php:8.3-cli-alpine AS base

RUN apk add --no-cache libpq-dev icu-dev libzip-dev oniguruma-dev

RUN docker-php-ext-install -j$(nproc) pdo_pgsql pgsql pcntl bcmath intl zip opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /app
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist 2>/dev/null || true
COPY . .
RUN composer dump-autoload --optimize

CMD ["php", "artisan", "queue:work", "--tries=3"]
