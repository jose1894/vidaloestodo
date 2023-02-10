#!/bin/sh

cd /var/www

composer dump-autoload -o

echo 'Setting permissions...'
# chown -R www-data:www-data /var/www/html/storage
# chmod -R 755 /var/www/html/storage
chmod 777 -R storage bootstrap/cache

echo 'Migrating database...'
php artisan migrate --force
php artisan cache:clear
php artisan route:cache
php artisan config:clear

php artisan storage:link

/etc/init.d/cron start
echo "*/1 * * * * root php /var/www/artisan schedule:run >> /dev/null 2>&1" | tee -a /etc/crontab > /dev/null