#
# OriginPHP Framework
# Copyright 2018 - 2021 Jamiel Sharief.
#
# Licensed under The MIT License
# The above copyright notice and this permission notice shall be included in all copies or substantial
# portions of the Software.
#
# @copyright    Copyright (c) Jamiel Sharief
# @link         https://www.originphp.com
# @license      https://opensource.org/licenses/mit-license.php MIT License
#
FROM ubuntu:20.04
LABEL maintainer="Jamiel Sharief"
LABEL version="1.0.0-cli"

# Setup Enviroment
ENV DATE_TIMEZONE UTC
ENV DEBIAN_FRONTEND=noninteractive

# Best Practice : Cache Busting - Prevent cache issues run as one command
# @link https://docs.docker.com/develop/develop-images/dockerfile_best-practices/

RUN apt-get update && apt-get install -y \
    curl \
    git \
    mysql-client \
    nano \
    unzip \
    wget \
    zip \
    php \
    bzip2 \
    php-apcu \
    php-cli \
    php-common \
    php-curl \
    php-imap \
    php-intl \
    php-json \
    php-mailparse \
    php-mbstring \
    php-mysql \
    php-opcache \
    php-pear \
    php-readline \
    php-soap \
    php-xml \
    php-zip \
    php-dev \
    postgresql-client \
    php-pgsql \
    php-memcached \
    sqlite3 \ 
    php-sqlite3 \
    p7zip-full \
 && rm -rf /var/lib/apt/lists/*

# Setup directory

ADD ./ /app
WORKDIR /app

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-interaction

# Install X-Debug for PHPUnit Code Coverage (Causes major Performance decrease when extension is enabled)
RUN pecl install xdebug
#RUN echo 'zend_extension="/usr/lib/php/20190902/xdebug.so"' >> /etc/php/7.4/cli/php.ini
#RUN echo 'xdebug.default_enable=0' >> /etc/php/7.4/cli/php.ini

# Instructions to run xdebug temporarily i.e to generate code coverage
# To enable until next restart run these commands in bash
# echo 'zend_extension="/usr/lib/php/20190902/xdebug.so"' >> /etc/php/7.4/cli/php.ini
# echo 'xdebug.default_enable=0' >> /etc/php/7.4/cli/php.ini

RUN echo 'apc.enable_cli=1' >>  /etc/php/7.4/cli/php.ini

RUN pecl install redis
RUN echo 'extension=redis.so' >> /etc/php/7.4/cli/php.ini

CMD ["php", "-a"]