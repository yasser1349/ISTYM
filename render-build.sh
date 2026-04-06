#!/usr/bin/env bash
# exit on error
set -o errexit

echo "Installing composer dependencies..."
composer install --prefer-dist --no-dev --optimize-autoloader --no-interaction

echo "Clearing application cache..."
php artisan optimize:clear

echo "Caching configuration and routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Running database migrations..."
php artisan migrate --force
