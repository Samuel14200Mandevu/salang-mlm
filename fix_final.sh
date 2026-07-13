#!/bin/bash

echo "=========================================="
echo "   CORRECTION FINALE"
echo "=========================================="

# 1. Supprimer les tables qui posent problème en base
echo "1. Suppression des tables existantes..."
mysql -u admin -p'Amour7526@' salang_mlm -e "DROP TABLE IF EXISTS commission_payments;"
mysql -u admin -p'Amour7526@' salang_mlm -e "DROP TABLE IF EXISTS user_higher_ranks;"
mysql -u admin -p'Amour7526@' salang_mlm -e "DROP TABLE IF EXISTS commission_periods;"
mysql -u admin -p'Amour7526@' salang_mlm -e "DROP TABLE IF EXISTS qualified_branches;"
mysql -u admin -p'Amour7526@' salang_mlm -e "DROP TABLE IF EXISTS user_monthly_ranks;"
mysql -u admin -p'Amour7526@' salang_mlm -e "DROP TABLE IF EXISTS higher_ranks;"
mysql -u admin -p'Amour7526@' salang_mlm -e "DROP TABLE IF EXISTS mlm_settings;"

echo "✅ Tables supprimées"

# 2. Réexécuter les migrations
echo ""
echo "2. Réexécution des migrations..."
php artisan migrate

# 3. Vérifier que tout est OK
echo ""
echo "3. Vérification..."
php artisan tinker --execute="
echo 'higher_ranks: ' . (\Schema::hasTable('higher_ranks') ? 'OK' : 'NOK');
echo 'mlm_settings: ' . (\Schema::hasTable('mlm_settings') ? 'OK' : 'NOK');
echo 'commission_periods: ' . (\Schema::hasTable('commission_periods') ? 'OK' : 'NOK');
echo 'commission_payments: ' . (\Schema::hasTable('commission_payments') ? 'OK' : 'NOK');
echo 'qualified_branches: ' . (\Schema::hasTable('qualified_branches') ? 'OK' : 'NOK');
echo 'user_monthly_ranks: ' . (\Schema::hasTable('user_monthly_ranks') ? 'OK' : 'NOK');
echo 'user_higher_ranks: ' . (\Schema::hasTable('user_higher_ranks') ? 'OK' : 'NOK');
"

# 4. Vérifier les colonnes Socialite
echo ""
echo "4. Vérification des colonnes Socialite..."
php artisan tinker --execute="
echo 'google_id: ' . (\Schema::hasColumn('users', 'google_id') ? 'OK' : 'NOK');
echo 'facebook_id: ' . (\Schema::hasColumn('users', 'facebook_id') ? 'OK' : 'NOK');
echo 'twitter_id: ' . (\Schema::hasColumn('users', 'twitter_id') ? 'OK' : 'NOK');
echo 'instagram_id: ' . (\Schema::hasColumn('users', 'instagram_id') ? 'OK' : 'NOK');
echo 'tiktok_id: ' . (\Schema::hasColumn('users', 'tiktok_id') ? 'OK' : 'NOK');
echo 'last_provider: ' . (\Schema::hasColumn('users', 'last_provider') ? 'OK' : 'NOK');
"

echo ""
echo "✅ Terminé !"
