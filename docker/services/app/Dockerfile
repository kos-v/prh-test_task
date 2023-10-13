FROM php:8.1.22-fpm

ARG HOST_USER_UID=1000
ARG HOST_USER_GID=1000
ARG GIT_USER_NAME="Unnamed User"
ARG GIT_USER_EMAIL="unnamed_user@example.local"

RUN set -ex echo 'Creating user and group from the host' \
    && groupadd -g $HOST_USER_GID dev_user  \
    && useradd -lm -u $HOST_USER_UID -g $HOST_USER_GID host_user

RUN mkdir /project

RUN apt-get update && apt-get install -y \
        curl \
        wget \
        git \
        zlib1g-dev \
        libicu-dev \
        libzip-dev \
        libonig-dev \
        libpq-dev \
        libxml2-dev \
        g++ \
        locales \
        locales-all \
        supervisor

RUN docker-php-ext-install -j$(nproc) iconv mbstring pdo pdo_mysql mysqli zip ctype session simplexml opcache \
        && docker-php-ext-configure intl \
        && docker-php-ext-install intl

RUN git config --system user.name "${GIT_USER_NAME}" \
    && git config --system user.email "${GIT_USER_EMAIL}"

RUN curl -sS https://getcomposer.org/installer \
            | php -- --install-dir=/usr/local/bin --filename=composer

COPY php.ini /usr/local/etc/php/php.ini
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

WORKDIR /project

CMD ["supervisord"]