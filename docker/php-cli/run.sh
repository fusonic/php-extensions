#!/bin/bash -e

cp -n .env.dist .env
source .env

DIRECTORY="${1:-packages}"

docker build --build-arg PHP_VERSION=${PHP_VERSION} \
  --build-arg PHP_DOCKER_RELEASE=${PHP_DOCKER_RELEASE} \
  -t extensions-php-cli docker/php-cli
docker run -it -v ${PWD}:/app \
  --workdir /app/${DIRECTORY} \
  --add-host "host.docker.internal:host-gateway" \
  -e XDEBUG_CONFIG="client_host=host.docker.internal" \
  -e XDEBUG_SESSION="PHPSTORM" \
  --entrypoint /bin/bash extensions-php-cli
