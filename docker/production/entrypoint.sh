#!/bin/sh
set -e

cd /var/www/html

if [ -z "$APP_KEY" ]; then
    echo "APP_KEY is not set. Generate one with: docker compose -f docker-compose.prod.yml run --rm --no-deps --entrypoint php app artisan key:generate --show" >&2
    exit 1
fi

if [ -z "$PDF_API_TOKEN" ]; then
    echo "PDF_API_TOKEN is not set." >&2
    exit 1
fi

# Cache config, routes, views and events using the runtime environment.
php artisan optimize

exec "$@"
