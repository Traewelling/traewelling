#!/bin/bash
set -e
role=${CONTAINER_ROLE:-app}


if [ "$role" = "launch-all-at-once" ]; then

    echo "Running one item of every role";
    set -m # make job control work

    CONTAINER_ROLE=app ./docker-entrypoint.sh &
    CONTAINER_ROLE=queue ./docker-entrypoint.sh &
    CONTAINER_ROLE=scheduler ./docker-entrypoint.sh &

    fg %1
else

    wait-for-it "$DB_HOST:${DB_PORT:=3306}"
    cd /var/www/html
    runuser -u www-data -- php artisan optimize

    if [ "$role" = "app" ]; then

        echo "Running as app..."

        if [ "${SEED_DB}" = "true" ]; then
            echo "Resetting OAuth keys and seeding database"
            runuser -u www-data -- php artisan migrate:fresh --seed --force
            runuser -u www-data -- php artisan passport:install --force --quiet --no-interaction
        else
            runuser -u www-data -- php artisan migrate --force
        fi

        runuser -u www-data -- php artisan storage:link
        apache2-foreground

    elif [ "$role" = "queue" ]; then

        echo "Running the queue..."
        runuser -u www-data -- php artisan queue:work --queue=default,webhook

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
fi
