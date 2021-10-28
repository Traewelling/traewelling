FROM node:16-alpine as NodeBuildContainer
COPY . /usr/src/trwl
WORKDIR /usr/src/trwl
RUN npm i && npm run prod

FROM composer:2 as ComposerBuildContainer
COPY --from=NodeBuildContainer /usr/src/trwl /usr/src/trwl
WORKDIR /usr/src/trwl
RUN composer install --ignore-platform-reqs

FROM php:8-apache
RUN apt update && apt install -y zlib1g-dev libpng-dev wait-for-it
RUN docker-php-ext-install gd exif pdo pdo_mysql
RUN a2enmod rewrite
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
COPY --from=ComposerBuildContainer --chown=www-data:www-data /usr/src/trwl /var/www/html
ENTRYPOINT ["/var/www/html/docker-entrypoint.sh"]
CMD ["apache2-foreground"]

LABEL org.opencontainers.image.title="The Traewelling container image"
LABEL org.opencontainers.image.description="Easy-to-use deployment image of the Traewelling project"
LABEL org.opencontainers.image.url="https://github.com/Traewelling/traewelling#readme"
LABEL org.opencontainers.image.source="https://github.com/Traewelling/traewelling.git"
LABEL org.opencontainers.image.authors="Jonas Möller <jonas@traewelling.de>"
LABEL org.opencontainers.image.vendor="The Traewelling team <hi@traewelling.de>"
LABEL org.opencontainers.image.license="AGPL-3.0"
LABEL org.opencontainers.image.base.name="docker.io/library/php:8-apache"
