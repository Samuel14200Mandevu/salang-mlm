#!/bin/bash

# ============================================================
# BUILD SCRIPT POUR LARAVEL CLOUD
# ============================================================

echo "🚀 Début du build..."

# ============================================================
# 1. INSTALLATION DE PHP ET COMPOSER
# ============================================================
echo "📦 Installation de PHP et Composer..."

# Installer PHP et les extensions nécessaires
apt-get update -qq
apt-get install -y -qq php php-cli php-mbstring php-xml php-curl php-zip unzip curl > /dev/null 2>&1

# Installer Composer
curl -sS https://getcomposer.org/installer | php > /dev/null 2>&1
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# ============================================================
# 2. CRÉATION DES DOSSIERS DE CACHE
# ============================================================
echo "📁 Création des dossiers de cache..."

mkdir -p /tmp/storage/framework/{cache,sessions,views}
mkdir -p /tmp/storage/logs
chmod -R 755 /tmp/storage/framework
chmod -R 755 /tmp/storage/logs

# ============================================================
# 3. CONFIGURATION DE L'ENVIRONNEMENT
# ============================================================
export VIEW_COMPILED_PATH=/tmp/storage/framework/views
export LOG_STREAM_PATH=/tmp/storage/logs/laravel.log
export LOG_CHANNEL=null

# ============================================================
# 4. INSTALLATION DES DÉPENDANCES
# ============================================================
echo "📦 Installation des dépendances PHP..."
composer install --no-dev --optimize-autoloader --no-interaction

# ============================================================
# 5. NETTOYAGE DU CACHE
# ============================================================
echo "🧹 Nettoyage du cache..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# ============================================================
# 6. CRÉATION DU LIEN SYMBOLIQUE POUR LES IMAGES
# ============================================================
echo "🔗 Création du lien symbolique pour les images..."
php artisan storage:link || true

# ============================================================
# 7. BUILD DES ASSETS
# ============================================================
echo "⚡ Build des assets..."
npm run build

echo "✅ Build terminé avec succès !"