FROM php:7.4-fpm-alpine

COPY --from=composer:2.3 /usr/bin/composer /usr/bin/composer

RUN apk update && apk add \
    ca-certificates \
    openssh \
    git \
    icu-dev \
    icu-data-full

RUN docker-php-ext-configure intl && docker-php-ext-install intl

WORKDIR /app
