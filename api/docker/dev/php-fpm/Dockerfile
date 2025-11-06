FROM php:8.4-fpm-alpine

RUN apk add postgresql-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/user/local/pgsql \
    && docker-php-ext-install pdo_pgsql bcmath

WORKDIR /app