ARG PHP_VERSION=8.5
ARG DEV_MODE=0

FROM php:${PHP_VERSION}-cli-alpine AS vendor
ARG DEV_MODE
WORKDIR /app

RUN apk add --no-cache \
    git \
    unzip \
    curl \
  && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy only the files needed for composer install to leverage Docker cache
COPY composer.json composer.lock symfony.lock ./

RUN if [ "$DEV_MODE" = "1" ]; then \
      composer install --prefer-dist --no-scripts --no-progress --no-interaction; \
    else \
      composer install --prefer-dist --no-dev --no-scripts --no-progress --no-interaction; \
    fi

COPY . .

RUN if [ "$DEV_MODE" = "1" ]; then \
      composer dump-autoload; \
    else \
      composer dump-autoload --classmap-authoritative; \
    fi

FROM php:${PHP_VERSION}-fpm-alpine AS app
ARG DEV_MODE
WORKDIR /var/www/html

COPY docker/php/install-php-ext.sh /usr/local/bin/install-php-ext.sh
RUN chmod +x /usr/local/bin/install-php-ext.sh \
  && /usr/local/bin/install-php-ext.sh

COPY docker/php/conf.d/ /usr/local/etc/php/conf.d/
COPY docker/php/configure-php-ini.sh /usr/local/bin/configure-php-ini.sh
RUN chmod +x /usr/local/bin/configure-php-ini.sh \
  && /usr/local/bin/configure-php-ini.sh
COPY docker/php/fpm/zz-app.conf /usr/local/etc/php-fpm.d/zz-app.conf

COPY --from=vendor /app /var/www/html

RUN mkdir -p var \
  && chown -R www-data:www-data var

EXPOSE 9000

CMD ["php-fpm"]

FROM nginx:1.25-alpine AS web
WORKDIR /var/www/html

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=vendor /app/public /var/www/html/public
