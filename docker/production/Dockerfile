FROM php:8.3-fpm AS base

RUN apt-get update && apt-get install -y --no-install-recommends libzip-dev libicu-dev \
    && docker-php-ext-install zip intl bcmath opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

FROM base AS vendor

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN apt-get update && apt-get install -y --no-install-recommends git unzip && rm -rf /var/lib/apt/lists/*

COPY src/composer.json src/composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --no-scripts --no-autoloader --prefer-dist

COPY src/ ./
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative \
    && php artisan package:discover --ansi

FROM node:22-slim AS node_modules

ENV PUPPETEER_SKIP_DOWNLOAD=true
WORKDIR /app
COPY src/package.json src/package-lock.json ./
RUN npm ci --omit=dev --omit=optional --no-audit --no-fund \
    && find node_modules -mindepth 1 -maxdepth 1 -type d -empty -delete

FROM nginx:1.29-alpine AS nginx

COPY docker/production/nginx.conf /etc/nginx/conf.d/default.conf
COPY src/public /var/www/html/public

FROM base AS app

ENV PUPPETEER_SKIP_DOWNLOAD=true \
    PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium

RUN apt-get update && apt-get install -y --no-install-recommends \
        chromium fonts-liberation fonts-noto-core fonts-noto-color-emoji fonts-noto-cjk \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=node:22-slim /usr/local/bin/node /usr/local/bin/node

COPY docker/production/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/production/www.conf /usr/local/etc/php-fpm.d/zz-app.conf
COPY docker/production/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

COPY --chown=www-data:www-data src/ ./
COPY --chown=www-data:www-data --from=vendor /var/www/html/vendor ./vendor
COPY --chown=www-data:www-data --from=vendor /var/www/html/bootstrap/cache ./bootstrap/cache
COPY --chown=www-data:www-data --from=node_modules /app/node_modules ./node_modules

RUN chown www-data:www-data /var/www \
    && chmod -R ug+rwX storage bootstrap/cache

USER www-data

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
