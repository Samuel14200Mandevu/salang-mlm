#!/bin/bash

# Ajouter les colonnes une par une
mysql -u admin -p'Amour7526@' salang_mlm_test -e "ALTER TABLE ranks ADD COLUMN level INT NOT NULL DEFAULT 1 AFTER id;"
mysql -u admin -p'Amour7526@' salang_mlm_test -e "ALTER TABLE ranks ADD COLUMN monthly_pv_required INT NOT NULL DEFAULT 0 AFTER min_bv;"
mysql -u admin -p'Amour7526@' salang_mlm_test -e "ALTER TABLE ranks ADD COLUMN team_pv_required INT NOT NULL DEFAULT 0 AFTER monthly_pv_required;"
mysql -u admin -p'Amour7526@' salang_mlm_test -e "ALTER TABLE ranks ADD COLUMN pv_payment_required INT DEFAULT 0 AFTER bonus_percentage;"
mysql -u admin -p'Amour7526@' salang_mlm_test -e "ALTER TABLE ranks ADD COLUMN description TEXT AFTER pv_payment_required;"
mysql -u admin -p'Amour7526@' salang_mlm_test -e "ALTER TABLE ranks ADD COLUMN conditions JSON AFTER description;"
mysql -u admin -p'Amour7526@' salang_mlm_test -e "ALTER TABLE ranks ADD COLUMN commission_types JSON AFTER conditions;"

# Ajouter les colonnes dans users
mysql -u admin -p'Amour7526@' salang_mlm_test -e "ALTER TABLE users ADD COLUMN parrain_id BIGINT UNSIGNED AFTER sponsor_id;"
mysql -u admin -p'Amour7526@' salang_mlm_test -e "ALTER TABLE users ADD COLUMN monthly_pv INT DEFAULT 0 AFTER bv_balance;"
mysql -u admin -p'Amour7526@' salang_mlm_test -e "ALTER TABLE users ADD COLUMN monthly_bv INT DEFAULT 0 AFTER monthly_pv;"
mysql -u admin -p'Amour7526@' salang_mlm_test -e "ALTER TABLE users ADD COLUMN team_pv INT DEFAULT 0 AFTER monthly_bv;"
mysql -u admin -p'Amour7526@' salang_mlm_test -e "ALTER TABLE users ADD COLUMN team_bv INT DEFAULT 0 AFTER team_pv;"
mysql -u admin -p'Amour7526@' salang_mlm_test -e "ALTER TABLE users ADD COLUMN qualified_branches INT DEFAULT 0 AFTER team_bv;"
mysql -u admin -p'Amour7526@' salang_mlm_test -e "ALTER TABLE users ADD COLUMN direct_sponsors_count INT DEFAULT 0 AFTER qualified_branches;"
mysql -u admin -p'Amour7526@' salang_mlm_test -e "ALTER TABLE users ADD COLUMN last_rank_update DATE AFTER direct_sponsors_count;"

# Ajouter les colonnes dans products
mysql -u admin -p'Amour7526@' salang_mlm_test -e "ALTER TABLE products ADD COLUMN pv_value INT NOT NULL DEFAULT 10 AFTER price;"
mysql -u admin -p'Amour7526@' salang_mlm_test -e "ALTER TABLE products ADD COLUMN bv_value INT NOT NULL DEFAULT 10 AFTER pv_value;"

# Ajouter les colonnes dans commissions
mysql -u admin -p'Amour7526@' salang_mlm_test -e "ALTER TABLE commissions ADD COLUMN commission_period_id BIGINT UNSIGNED AFTER package_id;"
mysql -u admin -p'Amour7526@' salang_mlm_test -e "ALTER TABLE commissions ADD COLUMN period VARCHAR(10) AFTER commission_period_id;"
mysql -u admin -p'Amour7526@' salang_mlm_test -e "ALTER TABLE commissions ADD COLUMN calculation_type ENUM('automatic','manual') DEFAULT 'automatic' AFTER period;"
mysql -u admin -p'Amour7526@' salang_mlm_test -e "ALTER TABLE commissions ADD COLUMN generation INT AFTER calculation_type;"

php artisan optimize:clear
php artisan test --filter=AdvancedRankCalculatorTest
