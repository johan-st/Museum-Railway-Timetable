FROM wordpress:php8.2-apache

COPY docker/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

RUN a2enmod rewrite
