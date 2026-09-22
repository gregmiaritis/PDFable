#!/bin/sh
set -e

cd /var/www/html

if [ -f composer.json ] && [ ! -d vendor ]; then
    composer install --no-interaction --prefer-dist
fi

if [ -f .env.example ] && [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate --force
fi

exec "$@"
