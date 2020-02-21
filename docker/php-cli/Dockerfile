FROM php:7.4-cli

RUN apt-get update && \
    apt-get install -y --no-install-recommends git zip unzip && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer
