#!/bin/bash
set -e

cd /var/www

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
