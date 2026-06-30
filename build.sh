#!/bin/bash

# Installer PHP et Composer
apt-get update
apt-get install -y php php-cli php-mbstring php-xml php-curl php-zip unzip curl

# Installer Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Créer les dossiers de cache dans /tmp/
mkdir -p /tmp/storage/framework/{cache,sessions,views}
mkdir -p /tmp/storage/logs
chmod -R 755 /tmp/storage/framework
chmod -R 755 /tmp/storage/logs

# Configurer l'environnement
export VIEW_COMPILED_PATH=/tmp/storage/framework/views
export LOG_STREAM_PATH=/tmp/storage/logs/laravel.log
export LOG_CHANNEL=null

# Installer les dépendances
composer install --no-dev --optimize-autoloader

# Effacer le cache de configuration
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Build des assets
npm run build