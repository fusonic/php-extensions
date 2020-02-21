#!/bin/sh -e

DIRECTORY="${1:-packages}"

docker build -t extensions-php-cli docker/php-cli
docker run -it -v ${PWD}/${DIRECTORY}:/app --workdir /app --entrypoint /bin/bash extensions-php-cli
