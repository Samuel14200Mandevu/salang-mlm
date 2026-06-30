#!/bin/bash

# Installer PHP et Composer
apt-get update
apt-get install -y php php-cli php-mbstring php-xml php-curl php-zip unzip curl

# Installer Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Exécuter le build
composer install --no-dev --optimize-autoloader
npm run build