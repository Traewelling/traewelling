#!/bin/bash
set -e
role=${CONTAINER_ROLE:-app}

if [ ${SEED_DB} == "true" ]; then
    echo "Seeding database"
    runuser -u www-data -- php artisan migrate:fresh --seed --force
fi

wait-for-it "$DB_HOST:${DB_PORT:=3306}"
cd /var/www/html
runuser -u www-data -- php artisan optimize

if [ "$role" = "app" ]; then

    echo "Running as app..."
    runuser -u www-data -- php artisan migrate --force
    runuser -u www-data -- php artisan storage:link
    apache2-foreground

elif [ "$role" = "queue" ]; then

    echo "Running the queue..."
    runuser -u www-data -- php artisan queue:work

elif [ "$role" = "scheduler" ]; then

    echo "Running as scheduler..."
    while true; do
        runuser -u www-data -- php artisan schedule:run --verbose --no-interaction
        sleep 60
    done

else
    echo "Could not match the container role \"$role\""
    exit 1
fi
