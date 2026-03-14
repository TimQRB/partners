FROM composer/composer:2-bin AS composer

FROM dunglas/frankenphp:1-php8.4-bookworm AS base

RUN apt update && apt -y install \
    unzip

RUN install-php-extensions \
    opcache \
    mbstring \
    intl \
    dom \
    ctype \
    curl \
    phar \
    openssl \
    xml \
    xmlwriter \
    simplexml \
    pdo \
    pdo_mysql

# COPY docker/php-max-execution.ini /usr/local/etc/php/conf.d/99-maxexec.ini

ENV LC_ALL=C.UTF-8

#
# Development
#

FROM base AS dev
ARG USER_ID=1000
ARG GROUP_ID=1000
ARG USER_NAME=appuser
ARG GROUP_NAME=appuser

RUN install-php-extensions \
    xdebug

COPY --from=composer /composer /usr/bin/composer

# Based on https://frankenphp.dev/docs/docker/#running-as-a-non-root-user
RUN \
    groupadd --gid 1000 appuser; \
    useradd --gid 1000 --uid 1000 appuser; \
    setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp; \
    chown -R appuser:appuser /data/caddy && chown -R appuser:appuser /config/caddy

USER appuser

#
# Production
#

FROM base AS prod-builder
COPY --from=composer /composer /usr/bin/composer
COPY .. /app
RUN --mount=type=cache,target=/tmp/cache \
    composer install --no-dev --no-progress --no-interaction --classmap-authoritative && \
    rm composer.lock composer.json

FROM base AS prod
ENV APP_ENV=prod
COPY --from=prod-builder --chown=www-data:www-data /app /app
USER www-data
