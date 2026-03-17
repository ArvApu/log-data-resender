#!/usr/bin/env sh
set -eu

if [ "${DEV_MODE:-0}" = "1" ] && [ -f /usr/local/etc/php/conf.d/dev.ini ]; then
  mv /usr/local/etc/php/conf.d/dev.ini /usr/local/etc/php/conf.d/zz-dev.ini
else
  rm -f /usr/local/etc/php/conf.d/dev.ini
fi

if [ "${DEV_MODE:-0}" = "1" ] && [ -f /usr/local/etc/php/conf.d/xdebug.ini ]; then
  mv /usr/local/etc/php/conf.d/xdebug.ini /usr/local/etc/php/conf.d/zz-xdebug.ini
else
  rm -f /usr/local/etc/php/conf.d/xdebug.ini
fi
