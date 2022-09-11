ARG PHP_VERSION
FROM composer:2 AS build

ARG BUILD_DEPENDENCIES=1

WORKDIR /app

COPY vendor ./vendor

COPY composer.json composer.lock ./

RUN if [ ${BUILD_DEPENDENCIES} -eq 1 ]; then \
    rm -fr vendor && \
    composer install -o --no-interaction --no-progress --ignore-platform-reqs --no-scripts --ansi; \
    fi

FROM webdevops/php-dev:${PHP_VERSION}

ARG APP_ENV="dev"
ARG APP_USER="application"
ARG APP_GROUP="application"
ARG APP_ROOT="/app"
ARG EXTRA_TOOLS=""

ENV APP_USER ${APP_USER}
ENV APP_GROUP ${APP_GROUP}
ENV APP_ROOT ${APP_ROOT}
ENV APP_ROOT_NAMESPACE 'ZFekete\SweefyBundle'

ENV DEBIAN_FRONTEND "noninteractive"

RUN apt-get update -y \
    && apt-get upgrade -y \
    && apt-get install -y curl nano vim htop

COPY ./docker/config /

RUN mkdir -p ${APP_ROOT} \
    && chown -R ${APP_USER}:${APP_GROUP} ${APP_ROOT}

RUN echo "${APP_USER} ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers

WORKDIR ${APP_ROOT}

COPY --chown=${APP_USER}:${APP_GROUP} . .

COPY --from=build --chown=${APP_USER}:${APP_GROUP} /app/vendor ./vendor

RUN chmod +x vendor/bin/*
