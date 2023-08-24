#!/usr/bin/env bash

set -e

TAG=stefanfisk/php-spx:latest
NAME=stefanfisk-php-spx
ARGS="$@"

if [[ $# -ne 0 ]] ; then
    NAME="$NAME-$(date +%s)"
    ARGS="php $ARGS"
fi

docker build \
    --quiet \
    --tag $TAG \
    - >/dev/null <<EOF
FROM php:8.2-apache

RUN apt-get update
RUN apt-get install -y --no-install-recommends \
    git \
    zlib1g-dev;

ENV SPX_ENABLED=1

RUN git clone https://github.com/NoiseByNorthwest/php-spx.git --branch release/latest --single-branch php-spx
RUN cd php-spx; \
    phpize; \
    ./configure; \
    make; \
    make install;
RUN docker-php-ext-enable spx
RUN { \
    echo 'spx.http_enabled=1'; \
    echo 'spx.http_key="dev"'; \
    echo 'spx.http_ip_whitelist=*'; \
    echo 'spx.data_dir=/var/php/.php-spx'; \
} > /usr/local/etc/php/conf.d/xdebug.ini

RUN chmod -R 777 /var/www
RUN echo '<?php header("Location: /?SPX_KEY=dev&SPX_UI_URI=/");' > /var/www/html/index.php

RUN mkdir /var/php
WORKDIR /var/php
EOF

docker run \
    --sysctl net.ipv4.ip_unprivileged_port_start=0 \
    --interactive \
    --tty \
    --rm \
    --user $(id -u ${USER}):$(id -g ${USER}) \
    --env SPX_ENABLED=1 \
    --env SPX_AUTO_START=${SPX_AUTO_START:-1} \
    --env SPX_BUILTINS=${SPX_BUILTINS} \
    --env SPX_DEPTH=${SPX_DEPTH} \
    --env SPX_SAMPLING_PERIOD=${SPX_SAMPLING_PERIOD} \
    --env SPX_METRICS=${SPX_METRICS} \
    --env SPX_REPORT=${SPX_REPORT:-full} \
    --env SPX_FP_FOCUS=${SPX_FP_FOCUS} \
    --env SPX_FP_INC=${SPX_FP_INC} \
    --env SPX_FP_REL=${SPX_FP_REL} \
    --env SPX_FP_LIMIT=${SPX_FP_LIMIT} \
    --env SPX_FP_LIVE=${SPX_FP_LIVE} \
    --env SPX_FP_COLOR=${SPX_FP_COLOR} \
    --env SPX_TRACE_SAFE=${SPX_TRACE_SAFE} \
    --env SPX_TRACE_FILE=${SPX_TRACE_FILE} \
    --volume "$(pwd)":/var/php \
    --name $NAME \
    $TAG \
    $ARGS
