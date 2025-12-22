# Laravel 10 on PHP 8.1 + Apache for Railway
FROM php:8.1-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libonig-dev \
    libpq-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip \
    && a2enmod rewrite \
    && a2dismod mpm_event mpm_worker \
    && a2enmod mpm_prefork \
    && rm -rf /var/lib/apt/lists/*

# Configure Apache to serve from /public and allow .htaccess
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && printf "<Directory /var/www/html/public>\n    AllowOverride All\n</Directory>\n" > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

WORKDIR /var/www/html

# Install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm composer-setup.php

# Leverage Docker layer caching for dependencies
COPY composer.json composer.lock* ./
RUN composer install --prefer-dist --no-interaction --optimize-autoloader --no-scripts

# Copy application source
COPY . .

# Run composer scripts after full app is copied
RUN composer run-script post-autoload-dump

# Ensure storage and cache directories are writable
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80

# Run PHP's built-in web server on port 80 with a router
CMD ["php", "-S", "0.0.0.0:80", "-t", "public", "server.php"]
