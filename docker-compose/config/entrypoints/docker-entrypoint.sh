#!/bin/sh

cd /var/www

composer install
composer dump-autoload -o

echo 'Setting permissions...'
# chown -R www-data:www-data /var/www/html/storage
# chmod -R 755 /var/www/html/storage
chmod 777 -R storage bootstrap/cache

echo 'Migrating database...'
# php artisan migrate --force
php artisan cache:clear
php artisan route:cache
php artisan config:clear

php artisan storage:link