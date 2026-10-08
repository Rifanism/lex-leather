# syntax=docker/dockerfile:1

# --- Stage 1: frontend assets (Vite + Tailwind v3) ---
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY postcss.config.js tailwind.config.js vite.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm run build

# --- Stage 2: composer dependencies ---
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --optimize-autoloader

# --- Stage 3: runtime (nginx + php-fpm in one container) ---
FROM php:8.4-fpm-alpine
RUN apk add --no-cache nginx supervisor oniguruma-dev sqlite-dev $PHPIZE_DEPS \
    && docker-php-ext-install pdo_sqlite mbstring opcache \
    && apk del $PHPIZE_DEPS \
    && rm -rf /var/cache/apk/*

WORKDIR /var/www/html
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build
COPY . .

# Writable paths for www-data (storage is partly replaced by a disk symlink at boot).
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 10000
ENTRYPOINT ["/entrypoint.sh"]
