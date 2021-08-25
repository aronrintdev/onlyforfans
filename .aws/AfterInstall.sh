#!/bin/bash

cd /var/www/allfans.com/

composer update

php artisan config:cache
php artisan route:cache
php artisan migrate --force

# sudo supervisorctl restart laravel_websockets
# sudo supervisorctl restart queue-finanical-work:*
# sudo supervisorctl restart queue-finanical-summaries-work:*
# sudo supervisorctl restart queue-work:*
