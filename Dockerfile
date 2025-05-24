# Use PHP 8.2 FPM Alpine as base image
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    linux-headers \
    bash \
    nodejs \
    npm \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    postgresql-dev \
    supervisor

# Install PHP extensions
RUN docker-php-ext-install pdo_pgsql bcmath exif pcntl gd sockets

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory
COPY . .

# Install dependencies
RUN composer install --no-interaction --no-dev --optimize-autoloader
RUN npm install && npm run build

# Set permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Copy Supervisor config
COPY ./docker/supervisor/supervisord.conf /etc/supervisor/supervisord.conf

# Expose PHP-FPM port
EXPOSE 9000

# Start Supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]