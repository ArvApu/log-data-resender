#!/usr/bin/env sh
set -eu

apk add --no-cache \
  icu-libs \
  libpq

if [ "${DEV_MODE:-0}" = "1" ]; then
  apk add --no-cache bash vim git unzip curl
fi

apk add --no-cache --virtual .build-deps \
  ${PHPIZE_DEPS} \
  linux-headers \
  icu-dev \
  postgresql-dev

docker-php-ext-install -j"$(nproc)" pdo_pgsql intl
docker-php-ext-enable opcache || true

if [ "${DEV_MODE:-0}" = "1" ]; then
  pecl install xdebug
  docker-php-ext-enable xdebug
fi

apk del .build-deps
