#!/bin/bash

cd /var/www/allfans.com/

DIR="/var/www/allfans.com/bootstrap/cache"
if [ ! -d "$DIR" ]; then
  # Take action if $DIR exists. #
  mkdir $DIR
  chmod -R 775 $DIR
  chown www-data:www-data $DIR
fi

DIR="/var/www/allfans.com/storage"
if [ ! -d "$DIR" ]; then
  # Take action if $DIR exists. #
  mkdir $DIR
  chmod -R 775 $DIR
  chown www-data:www-data $DIR
fi

DIR="/var/www/allfans.com/storage/framework"
if [ ! -d "$DIR" ]; then
  # Take action if $DIR exists. #
  mkdir $DIR
  chmod -R 775 $DIR
  chown www-data:www-data $DIR
fi

DIR="/var/www/allfans.com/storage/framework/cache"
if [ ! -d "$DIR" ]; then
  # Take action if $DIR exists. #
  mkdir $DIR
  chmod -R 775 $DIR
  chown www-data:www-data $DIR
fi

DIR="/var/www/allfans.com/storage/framework/sessions"
if [ ! -d "$DIR" ]; then
  # Take action if $DIR exists. #
  mkdir $DIR
  chmod -R 775 $DIR
  chown www-data:www-data $DIR
fi

DIR="/var/www/allfans.com/storage/framework/views"
if [ ! -d "$DIR" ]; then
  # Take action if $DIR exists. #
  mkdir $DIR
  chmod -R  775 $DIR
  chown www-data:www-data $DIR
fi

DIR="/var/www/allfans.com/storage/logs"
if [ ! -d "$DIR" ]; then
  # Take action if $DIR exists. #
  mkdir $DIR
  chmod -R 775 $DIR
  chown www-data:www-data $DIR
fi


composer update

php artisan config:cache
php artisan route:cache
php artisan migrate --force

# sudo supervisorctl restart laravel_websockets
# sudo supervisorctl restart queue-finanical-work:*
# sudo supervisorctl restart queue-finanical-summaries-work:*
# sudo supervisorctl restart queue-work:*
