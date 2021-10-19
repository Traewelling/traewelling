#!/bin/bash
set -e

if [ "$1" = 'apache2-foreground' ]; then
    cd /var/www/html
    runuser -u www-data -- php artisan migrate
    runuser -u www-data -- php artisan optimize
    apache2-foreground;
fi

exec "$@"
