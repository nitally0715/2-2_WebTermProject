FROM php:8.2-apache

ENV APACHE_DOCUMENT_ROOT=/var/www/html

RUN docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite

COPY app ${APACHE_DOCUMENT_ROOT}

RUN chown -R www-data:www-data ${APACHE_DOCUMENT_ROOT}
