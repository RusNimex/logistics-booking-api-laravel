FROM php:8.4-fpm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libicu-dev \
        libzip-dev \
    && docker-php-ext-install \
        intl \
        pdo_mysql \
        zip \
    && pecl install redis xdebug \
    && docker-php-ext-enable redis xdebug \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

COPY ./php.ini /usr/local/etc/php/conf.d/zz-custom.ini
COPY ./xdebug.ini /usr/local/etc/php/conf.d/zz-xdebug.ini
