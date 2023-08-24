#!/usr/bin/env bash

set -e

docker run \
    --interactive \
    --tty \
    --rm \
    --user $(id -u ${USER}):$(id -g ${USER}) \
    --volume "$(pwd)":/tmp/pwd \
    --workdir /tmp/pwd \
    php:8.2-cli \
    php "$@"
