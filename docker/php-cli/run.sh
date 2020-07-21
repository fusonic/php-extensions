#!/bin/bash -e

source .env

DIRECTORY="${1:-packages}"
IP_ADDRESS=`ip -o route get to 8.8.8.8 | sed -n 's/.*src \([0-9.]\+\).*/\1/p'`

docker build --build-arg PHP_VERSION=${PHP_VERSION} --build-arg PHP_DOCKER_RELEASE=${PHP_DOCKER_RELEASE} -t extensions-php-cli docker/php-cli
docker run -it -v ${PWD}:/app \
  --workdir /app/${DIRECTORY} \
  -e "XDEBUG_CONFIG=\"remote_host=${IP_ADDRESS}\"" \
  --entrypoint /bin/bash extensions-php-cli
