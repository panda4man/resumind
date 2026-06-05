FROM php:8.4-fpm

# Install system dependencies + Node.js 22
RUN apt-get update && apt-get install -y \
    zip unzip curl libzip-dev libpng-dev libonig-dev libxml2-dev libcurl4-openssl-dev \
    libicu-dev git supervisor nginx ca-certificates gnupg \
    && curl -fsSL https://deb.nodesource.com/setup_24.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath gd intl opcache

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy application source
COPY . /var/www

# Configure Git safe directory
RUN git config --global --add safe.directory /var/www

# Install PHP dependencies
RUN composer install --optimize-autoloader --no-dev --no-interaction

# Install JS dependencies and build frontend assets
RUN npm ci && npm run build && rm -rf node_modules

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage /var/www/bootstrap/cache

# Remove default nginx config
RUN rm -f /etc/nginx/conf.d/default.conf /etc/nginx/sites-enabled/default

# Copy config files
COPY ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY ./docker/supervisor/supervisord.conf /etc/supervisor/supervisord.conf

# Copy and enable entrypoint
COPY ./docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

# Force PHP-FPM to listen on TCP
RUN echo "listen = 127.0.0.1:9000" >> /usr/local/etc/php-fpm.d/zz-docker.conf

ENTRYPOINT ["/entrypoint.sh"]
