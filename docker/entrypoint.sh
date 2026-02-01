#!/bin/sh
set -e

cd /var/www/html

# When running with volume mount: install deps if vendor or build missing
if [ ! -f vendor/autoload.php ]; then
    composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
fi
if [ ! -d public/build ] || [ -z "$(ls -A public/build 2>/dev/null)" ]; then
    npm install && npm run build
fi

# Ensure storage and bootstrap/cache exist and are writable
mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

exec "$@"
