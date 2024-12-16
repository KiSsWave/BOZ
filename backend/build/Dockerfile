# Use an official PHP runtime as a base image
FROM php:8.3-cli

# Basic update and install required packages
RUN apt-get update && \
    apt-get install --yes --force-yes \
    cron openssl libicu-dev

# Installing the docker php extensions installer
RUN curl -sSLf \
        -o /usr/local/bin/install-php-extensions \
        https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions && \
    chmod +x /usr/local/bin/install-php-extensions

# PHP Configuration
RUN install-php-extensions curl mongodb intl
RUN install-php-extensions gettext iconv tidy zip sockets
RUN install-php-extensions pgsql mysqli
RUN install-php-extensions pdo_mysql pdo_pgsql
RUN install-php-extensions xdebug
RUN install-php-extensions redis
RUN install-php-extensions @composer

# Expose port
EXPOSE 80

# Copy custom php.ini configuration
COPY ./build/php.ini /usr/local/etc/php/