ARG PHP_VERSION

FROM --platform=linux/amd64 php:8.4-apache AS stage-amd64-php804

FROM --platform=linux/arm64 php:8.4-apache AS stage-arm64-php804

ARG TARGETARCH

FROM stage-$TARGETARCH-php$PHP_VERSION AS final

# Default to 33 for Windows compatibility, can be overridden for Mac
ARG USER_ID=33

RUN apt-get update && apt-get install -y \
    git zip unzip \
    libicu-dev libzip-dev libonig-dev libpq-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install bcmath intl mbstring opcache pdo pdo_pgsql zip

# enable x-debug
RUN (pecl list | grep -q xdebug || pecl install xdebug) && \
    docker-php-ext-enable xdebug || true

# install composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

# configure php
COPY php.ini /usr/local/etc/php/php.ini

# setup apache
RUN rm -f /etc/apache2/sites-enabled/000-default.conf
COPY vhost.conf /etc/apache2/sites-enabled/app.conf

RUN a2enmod rewrite \
    && a2enmod headers \
    && a2enmod expires

# create user home-directory so we can ssh into the container if we need to
RUN mkdir -p /home/www-data \
    && chown -R www-data:www-data /home/www-data \
    && usermod --shell /bin/bash -u ${USER_ID} -d /home/www-data www-data

EXPOSE 80

CMD ["apache2-foreground"]

WORKDIR "/var/www/html"
