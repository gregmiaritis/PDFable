FROM php:8.3-fpm

ARG UID=1000
ARG GID=1000

RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libicu-dev libonig-dev libsqlite3-dev \
    && docker-php-ext-install pdo_mysql pdo_sqlite zip intl bcmath mbstring opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Node.js + Chromium for Browsershot/Puppeteer (uses system Chromium, no bundled download)
ENV PUPPETEER_SKIP_DOWNLOAD=true \
    PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium

RUN apt-get update && apt-get install -y --no-install-recommends ca-certificates curl gnupg \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y --no-install-recommends \
        nodejs chromium \
        fonts-liberation fonts-noto-core fonts-noto-color-emoji fonts-noto-cjk \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN groupmod -o -g ${GID} www-data && usermod -o -u ${UID} -g www-data www-data \
    && chown www-data:www-data /var/www

COPY php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html
USER www-data

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
