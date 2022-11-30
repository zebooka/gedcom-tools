FROM php:8.2.0RC7-fpm-alpine3.15

COPY --from=composer:2.3 /usr/bin/composer /usr/bin/composer

RUN apk update && apk add \
    ca-certificates \
    openssh \
    git \
    icu-dev

RUN docker-php-ext-configure intl && docker-php-ext-install intl

WORKDIR /app
