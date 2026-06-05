#!/bin/bash
set -e

cd /var/www

# Ensure storage dirs exist (bind mount may be empty on first run)
mkdir -p storage/logs \
         storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/views \
         bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Wait for DB TCP port to accept connections
echo "Waiting for database..."
until (echo > /dev/tcp/${DB_HOST:-db}/${DB_PORT:-3306}) 2>/dev/null; do
    sleep 2
done
echo "Database ready."

# Generate APP_KEY if not provided
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Run migrations
php artisan migrate --force

# Create storage symlink
php artisan storage:link --force 2>/dev/null || true

# Cache config/routes/views with real runtime env
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec /usr/bin/supervisord -n -c /etc/supervisor/supervisord.conf
