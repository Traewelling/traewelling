#!/bin/bash
set -e

if [ "$1" = 'apache2-foreground' ]; then
    wait-for-it "$DB_HOST:${DB_PORT:=3306}"
    cd /var/www/html
    runuser -u www-data -- php artisan migrate --force
    runuser -u www-data -- php artisan storage:link
    runuser -u www-data -- php artisan optimize
    apache2-foreground
fi

exec "$@"
