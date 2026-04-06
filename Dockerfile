# Use the official PHP 8.2 image with Apache
FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libpq-dev \
    libicu-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install required PHP extensions (including PDO PostgreSQL for Render DB)
RUN docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd intl zip

# Enable Apache mod_rewrite for Laravel routing
RUN a2enmod rewrite

# Configure Apache to use public/ folder as DocumentRoot
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Configure Apache to listen on PORT environment variable, which Render requires
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy the application code
COPY . /var/www/html

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set proper permissions for Laravel
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Expose Render's default port 8000
ENV PORT=8000
EXPOSE $PORT

# Start command: This will prepare Laravel (caching, migrations) and start Apache
CMD php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan migrate --force && apache2-foreground
