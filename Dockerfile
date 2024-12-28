FROM php:7.2-apache as php7
RUN docker-php-ext-install mysqli

WORKDIR /var/www/html
# COPY php .


FROM php:8.2-apache as php8
RUN docker-php-ext-install mysqli

WORKDIR /var/www/html
# COPY php .
