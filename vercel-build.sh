#!/bin/bash

# Installer PHP et Composer dans l'environnement de build
apt-get update
apt-get install -y php php-cli php-mbstring php-xml php-curl php-zip unzip curl

# Installer Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Exécuter le build de votre projet
composer install --no-dev --optimize-autoloader
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache