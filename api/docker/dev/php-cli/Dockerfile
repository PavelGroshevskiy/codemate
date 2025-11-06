FROM php:8.4-cli-alpine

RUN apk update && apk add unzip

RUN apk add postgresql-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/user/local/pgsql \
    && docker-php-ext-install pdo_pgsql

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/bin --filename=composer --quiet

WORKDIR /app