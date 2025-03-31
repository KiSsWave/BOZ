FROM php:8.3-cli

# Installation des outils nécessaires pour la sauvegarde
RUN apt-get update && \
    apt-get install --yes --no-install-recommends \
    postgresql-client \
    cron \
    gzip \
    curl \
    openssl \
    libicu-dev \
    libgd-dev && \
    rm -rf /var/lib/apt/lists/*

# Installer les extensions PHP comme dans votre Dockerfile original
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

# Création du répertoire pour les backups
RUN mkdir -p /backups

# Copie du script de backup
COPY backup.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/backup.sh

# Configuration de cron
COPY backup-cron /etc/cron.d/backup-cron
RUN chmod 0644 /etc/cron.d/backup-cron && \
    crontab /etc/cron.d/backup-cron

# Point d'entrée pour démarrer cron en premier plan
CMD ["cron", "-f"]