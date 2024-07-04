#!/usr/bin/env bash

set -e

pre_run() {
 php artisan down
 git pull
 composer install --no-interaction --no-dev --prefer-dist
}

update_ui() {
    npm ci --no-audit --no-progress && npm run build
}

run_migrations() {
    php artisan migrate --force
}

post_run() {
    php artisan optimize

    if [ -f /etc/systemd/system/traewelling-queue.service ]; then
        sudo systemctl restart traewelling-queue
    fi

    if [ -f /etc/systemd/system/traewelling-queue-webhook.service ]; then
        sudo systemctl restart traewelling-queue-webhook
    fi

    php artisan db:seed --class=Database\\Seeders\\Constants\\PermissionSeeder --force
    php artisan up
}

pre_run
run_migrations
update_ui
post_run
