ARG PHP_VERSION=8.5

FROM php:${PHP_VERSION}-cli-alpine AS vendor
WORKDIR /app

RUN apk add --no-cache \
    git \
    unzip \
    curl \
  && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy only the files needed for composer install to leverage Docker cache
COPY composer.json composer.lock symfony.lock ./
RUN composer install --prefer-dist --no-scripts --no-progress --no-interaction

COPY . .

RUN composer dump-autoload --classmap-authoritative

FROM php:${PHP_VERSION}-fpm-alpine AS app
WORKDIR /var/www/html

RUN apk add --no-cache \
    icu-libs \
    libpq \
  && apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    icu-dev \
    postgresql-dev \
  && docker-php-ext-install -j$(nproc) pdo_pgsql intl \
  && docker-php-ext-enable opcache || true \
  && apk del .build-deps

COPY docker/php/conf.d/ /usr/local/etc/php/conf.d/
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
