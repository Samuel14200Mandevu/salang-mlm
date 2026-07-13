#!/bin/bash

echo "=========================================="
echo "   SALANG MLM - FIX COMPLET"
echo "=========================================="

# 1. Supprimer la fonction formatSizeUnits de routes/console.php
echo ""
echo "1. Suppression de formatSizeUnits..."
if grep -q "function formatSizeUnits" routes/console.php; then
    sed -i '/function formatSizeUnits/,/^}/d' routes/console.php
    echo "✅ Supprimé"
else
    echo "✅ Déjà supprimé"
fi

# 2. Créer le fichier de configuration pour les tests
echo ""
echo "2. Configuration de phpunit.xml..."
cat > phpunit.xml << 'EOF'
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory>app</directory>
        </include>
    </source>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="APP_MAINTENANCE_DRIVER" value="file"/>
        <env name="BCRYPT_ROUNDS" value="4"/>
        <env name="CACHE_STORE" value="array"/>
        <env name="DB_CONNECTION" value="mysql"/>
        <env name="DB_HOST" value="127.0.0.1"/>
        <env name="DB_PORT" value="3306"/>
        <env name="DB_DATABASE" value="salang_mlm_test"/>
        <env name="DB_USERNAME" value="admin"/>
        <env name="DB_PASSWORD" value="Amour7526@"/>
        <env name="MAIL_MAILER" value="array"/>
        <env name="PULSE_ENABLED" value="false"/>
        <env name="QUEUE_CONNECTION" value="sync"/>
        <env name="SESSION_DRIVER" value="array"/>
        <env name="TELESCOPE_ENABLED" value="false"/>
    </php>
</phpunit>
EOF
echo "✅ phpunit.xml créé"

# 3. Créer la base de données de test
echo ""
echo "3. Création de la base de données de test..."
mysql -u admin -p'Amour7526@' -e "CREATE DATABASE IF NOT EXISTS salang_mlm_test;"
echo "✅ Base de données créée"

# 4. Copier la structure de la base de données
echo ""
echo "4. Copie de la structure de la base de données..."
mysqldump -u admin -p'Amour7526@' --no-data salang_mlm > /tmp/salang_mlm_structure.sql
mysql -u admin -p'Amour7526@' salang_mlm_test < /tmp/salang_mlm_structure.sql
echo "✅ Structure copiée"

# 5. Copier les données de base
echo ""
echo "5. Copie des données de base..."
mysqldump -u admin -p'Amour7526@' --no-create-info salang_mlm ranks higher_ranks mlm_settings roles permissions role_has_permissions > /tmp/salang_mlm_data.sql
mysql -u admin -p'Amour7526@' salang_mlm_test < /tmp/salang_mlm_data.sql
echo "✅ Données copiées"

# 6. Vider le cache
echo ""
echo "6. Vidage du cache..."
php artisan optimize:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan cache:clear
echo "✅ Cache vidé"

# 7. Recharger l'autoload
echo ""
echo "7. Rechargement de l'autoload..."
composer dump-autoload
echo "✅ Autoload rechargé"

# 8. Exécuter les tests
echo ""
echo "8. Exécution des tests..."
echo "=========================================="
echo ""

# Tests Unitaires
echo "--- TESTS UNITAIRE ---"
php artisan test --testsuite=Unit --stop-on-failure

# Tests Feature
echo ""
echo "--- TESTS FEATURE ---"
php artisan test --testsuite=Feature --stop-on-failure

echo ""
echo "=========================================="
echo "   ✅ FIX TERMINÉ !"
echo "=========================================="