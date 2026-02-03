FROM node:20-alpine AS frontend
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.* ./
COPY postcss.config.* tailwind.config.* ./
COPY resources ./resources
COPY public ./public

RUN npm run build


FROM composer:2 AS vendor
WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction \
    --no-progress \
    --no-scripts


FROM harbor.backservice.tech/miuz/base/php-fpm:8.3-fpm-alpine_build.61040
WORKDIR /app

COPY --chown=www:www . .

COPY --from=vendor    /app/vendor       /app/vendor
COPY --from=frontend  /app/public/build /app/public/build

RUN set -eux; \
    mkdir -p storage bootstrap/cache; \
    chown -R www:www /app; \
    php artisan package:discover --ansi; \
    php artisan storage:link || true

USER www

CMD ["php-fpm", "-F"]
