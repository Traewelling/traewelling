FROM node:16-alpine as NodeBuildContainer
COPY . /usr/src/trwl
WORKDIR /usr/src/trwl
RUN npm i && npm run prod

FROM php:8-alpine as PHPBuildContainer
RUN apk add zip
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --install-dir=/bin --filename=composer
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
COPY --from=PHPBuildContainer --chown=www-data:www-data /usr/src/trwl /var/www/html
ENTRYPOINT ["/var/www/html/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
