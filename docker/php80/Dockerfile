FROM php:8.0-fpm-alpine

COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

RUN apk update && apk add \
    ca-certificates \
    openssh \
    git \
    icu-dev \
    icu-data-full

RUN docker-php-ext-configure intl && docker-php-ext-install intl

WORKDIR /app
