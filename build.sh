#!/bin/bash

# Installer PHP et Composer
apt-get update
apt-get install -y php php-cli php-mbstring php-xml php-curl php-zip unzip curl

# Installer Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Créer les dossiers nécessaires dans /tmp/
mkdir -p /tmp/storage/framework/{cache,sessions,views}
mkdir -p /tmp/storage/logs
chmod -R 755 /tmp/storage/framework
chmod -R 755 /tmp/storage/logs

# Exécuter le build
composer install --no-dev --optimize-autoloader
npm run build