FROM node:24-alpine AS node-build
COPY . /usr/src/trwl
WORKDIR /usr/src/trwl
RUN npm install && npm run build && rm -rf node_modules

FROM composer:2 AS composer-build
COPY --from=node-build /usr/src/trwl /usr/src/trwl
WORKDIR /usr/src/trwl
RUN composer install --ignore-platform-reqs --no-interaction --no-progress --optimize-autoloader

FROM php:8.5-apache
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN apt update && \
    apt upgrade -y && \
    apt install -y zlib1g-dev libpng-dev libsodium-dev wait-for-it default-mysql-client && \
    docker-php-ext-install gd exif pdo pdo_mysql sodium && \
    a2enmod rewrite && \
    a2enmod http2 && \
    sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf && \
    sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY --from=composer-build --chown=www-data:www-data /usr/src/trwl /var/www/html
COPY docs/hosting/docker/docker-entrypoint.sh /var/www/html/docker-entrypoint.sh
RUN chmod +x /var/www/html/docker-entrypoint.sh

ENTRYPOINT ["/var/www/html/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
