ARG PHP_VERSION

FROM php:${PHP_VERSION}-alpine

ARG COMPOSER_FLAGS

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json ./

RUN composer update ${COMPOSER_FLAGS}

COPY ./src ./src
COPY ./tests ./tests
COPY ./Makefile ./Makefile
COPY ./phpstan.neon ./
