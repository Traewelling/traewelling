FROM node:18-alpine as NodeBuildContainer
COPY . /usr/src/trwl
WORKDIR /usr/src/trwl
RUN npm i && npm run prod

FROM composer:2 as ComposerBuildContainer
COPY --from=NodeBuildContainer /usr/src/trwl /usr/src/trwl
WORKDIR /usr/src/trwl
RUN composer install --ignore-platform-reqs --no-interaction --no-progress --no-suggest --optimize-autoloader

FROM php:8.1.7-apache
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN apt update && \
    apt upgrade -y && \
    apt install -y zlib1g-dev libpng-dev wait-for-it && \
    docker-php-ext-install gd exif pdo pdo_mysql && \
    a2enmod rewrite && \
    a2enmod http2 && \
    sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf && \
    sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY --from=ComposerBuildContainer --chown=www-data:www-data /usr/src/trwl /var/www/html

ENTRYPOINT ["/var/www/html/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
