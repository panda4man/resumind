FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    zip unzip curl libzip-dev libpng-dev libonig-dev libxml2-dev libcurl4-openssl-dev \
    libicu-dev git supervisor nginx

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath gd intl

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application source
COPY . /var/www

# Configure Git safe directory
RUN git config --global --add safe.directory /var/www

# Set permissions
RUN chown -R www-data:www-data /var/www && chmod -R 755 /var/www/storage /var/www/bootstrap/cache

# Optimize Laravel
RUN composer install --optimize-autoloader --no-dev \
    && php artisan config:clear \
    && php artisan config:cache

# 🧼 Remove default nginx config to avoid conflicts
RUN rm -f /etc/nginx/conf.d/default.conf || true \
    && rm -f /etc/nginx/sites-enabled/default

# ✅ Copy custom config files
COPY ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY ./docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Expose HTTP
EXPOSE 80

# Force PHP-FPM to listen on TCP so Nginx can connect
RUN echo "listen = 127.0.0.1:9000" >> /usr/local/etc/php-fpm.d/zz-docker.conf

# Run Nginx + PHP-FPM together
CMD ["/usr/bin/supervisord", "-n"]
