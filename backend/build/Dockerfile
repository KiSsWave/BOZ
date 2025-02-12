
FROM php:8.3-cli


RUN apt-get update && \
    apt-get install --yes --no-install-recommends \
    cron openssl libicu-dev libgd-dev && \
    rm -rf /var/lib/apt/lists/*


RUN curl -sSLf \
        -o /usr/local/bin/install-php-extensions \
        https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions && \
    chmod +x /usr/local/bin/install-php-extensions


RUN install-php-extensions curl mongodb intl
RUN install-php-extensions gettext iconv tidy zip sockets
RUN install-php-extensions pgsql mysqli
RUN install-php-extensions pdo_mysql pdo_pgsql
RUN install-php-extensions xdebug
RUN install-php-extensions redis
RUN install-php-extensions gd
RUN install-php-extensions @composer

# Expose port
EXPOSE 80

# Copy custom php.ini configuration
COPY ./build/php.ini /usr/local/etc/php/
