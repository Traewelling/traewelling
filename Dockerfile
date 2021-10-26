FROM node as NodeBuildContainer
COPY . /usr/src/trwl
WORKDIR /usr/src/trwl
RUN npm i
RUN npm run prod

FROM php as PHPBuildContainer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --install-dir=/bin --filename=composer
RUN apt update && apt install -y zlib1g-dev libpng-dev zip
RUN docker-php-ext-install gd exif

COPY --from=NodeBuildContainer /usr/src/trwl /usr/src/trwl
WORKDIR /usr/src/trwl
RUN composer install

FROM php:apache
RUN apt update && apt install -y zlib1g-dev libpng-dev
RUN docker-php-ext-install gd exif pdo pdo_mysql
RUN a2enmod rewrite
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
COPY --from=PHPBuildContainer --chown=www-data:www-data /usr/src/trwl /var/www/html
COPY docker-entrypoint.sh /docker-entrypoint.sh
ENTRYPOINT ["/var/www/html/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
