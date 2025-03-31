#!/bin/bash

# Création des répertoires nécessaires
mkdir -p backup-service/build
mkdir -p backups

# Création du fichier Dockerfile
cat > backup-service/Dockerfile << 'EOF'
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

RUN install-php-extensions curl pgsql

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
EOF

# Création du script de backup
cat > backup-service/backup.sh << 'EOF'
#!/bin/bash

# Configuration
BACKUP_DIR="/backups"
DB_HOST="postgres"
DB_PORT="5432"
DB_NAME="boz"
DB_USER="postgres"
DB_PASSWORD=$POSTGRES_PASSWORD  # Utilise la variable d'environnement

# Vérifie si la variable d'environnement est définie
if [ -z "$DB_PASSWORD" ]; then
    echo "Erreur: La variable d'environnement POSTGRES_PASSWORD n'est pas définie"
    exit 1
fi

# Création du timestamp pour le nom de fichier
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="${BACKUP_DIR}/backup_${DB_NAME}_${TIMESTAMP}.sql"

echo "Démarrage de la sauvegarde de la base ${DB_NAME} à $(date)"

# Attente que PostgreSQL soit prêt
until PGPASSWORD=${DB_PASSWORD} pg_isready -h ${DB_HOST} -p ${DB_PORT} -U ${DB_USER}; do
    echo "En attente de la disponibilité de PostgreSQL..."
    sleep 2
done

# Exécution de la sauvegarde
PGPASSWORD=${DB_PASSWORD} pg_dump -h ${DB_HOST} -p ${DB_PORT} -U ${DB_USER} -d ${DB_NAME} > ${BACKUP_FILE}

# Compression du fichier de sauvegarde
gzip ${BACKUP_FILE}
echo "Sauvegarde terminée: ${BACKUP_FILE}.gz"

# Conservation des 4 dernières sauvegardes seulement
cd ${BACKUP_DIR}
ls -tp *.gz | grep -v '/$' | tail -n +5 | xargs -I {} rm -- {} 2>/dev/null || true
echo "Nettoyage des anciennes sauvegardes effectué"

# Envoi d'une notification par email (optionnel)
# mail -s "Sauvegarde BDD BOZ effectuée" votre@email.com < /dev/null
EOF

# Création du fichier de configuration cron
cat > backup-service/backup-cron << 'EOF'
# Exécution hebdomadaire tous les dimanches à 2h du matin
0 2 * * 0 /usr/local/bin/backup.sh >> /var/log/backup.log 2>&1
# Garder une ligne vide à la fin
EOF

# Rendre le script de backup exécutable
chmod +x backup-service/backup.sh

echo "Configuration du service de sauvegarde terminée."
echo "Ajoutez maintenant le service à votre docker-compose.yml et démarrez-le."