ARG PHP_VERSION
ARG PHP_DOCKER_RELEASE

FROM php:${PHP_VERSION}-cli-${PHP_DOCKER_RELEASE}

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN apt-get update && \
    apt-get install -y --no-install-recommends git && \
    chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions @composer-2 xdebug zip intl


RUN echo 'alias cs=composer' >> /root/.bashrc

RUN \
    echo "xdebug.mode=debug,coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.discover_client_host=0" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.idekey=PHPSTORM" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN \
    # Set php memory limit
    cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini && \
    sed -i -e "s/^ *memory_limit.*/memory_limit = 1G/g" -e "s/^ *max_execution_time.*/max_execution_time = 0/g" /usr/local/etc/php/php.ini

