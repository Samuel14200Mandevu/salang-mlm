#!/usr/bin/env bash
# Réconciliation migrations prod (restauration SQL vs table migrations).
# Par défaut : DRY-RUN (affiche les INSERT sans écrire).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

DRY_RUN="${DRY_RUN:-1}"
ENV_FILE="${ENV_FILE:-$ROOT/.env.local}"

load_env() {
    grep -E "^$1=" "$ENV_FILE" 2>/dev/null | head -1 | cut -d= -f2- | sed 's/^"//;s/"$//'
}

DB_HOST="$(load_env DB_HOST)"; DB_HOST="${DB_HOST:-127.0.0.1}"
DB_DATABASE="$(load_env DB_DATABASE)"; DB_DATABASE="${DB_DATABASE:-salang_mlm}"
DB_USERNAME="$(load_env DB_USERNAME)"
DB_PASSWORD="$(load_env DB_PASSWORD)"

if [[ -z "$DB_USERNAME" || -z "$DB_PASSWORD" ]]; then
    echo "❌ Credentials MySQL manquants ($ENV_FILE)"
    exit 1
fi

echo "🔍 Réconciliation migrations (DRY_RUN=$DRY_RUN)"
echo "   Base : $DB_DATABASE @ $DB_HOST"
echo ""

if [[ "$DRY_RUN" != "1" ]]; then
    echo "📦 Backup obligatoire..."
    "$ROOT/scripts/backup-db.sh"
    echo ""
    echo "▶ Migrations à exécuter (tables manquantes)..."
    php artisan migrate --path=database/migrations/2026_07_13_082018_create_user_higher_ranks_table.php --force
    php artisan migrate --path=database/migrations/2026_10_03_120001_create_personal_access_tokens_table.php --force
    echo ""
fi

MARK_RAN=(
    2026_07_13_082015_create_higher_ranks_table
    2026_07_13_082015_create_mlm_settings_table
    2026_07_13_082016_create_commission_payments_table
    2026_07_13_082016_create_commission_periods_table
    2026_07_13_082017_create_qualified_branches_table
    2026_07_13_082018_create_user_monthly_ranks_table
    2026_07_13_082019_add_socialite_columns_to_users_table
    2026_07_13_082019_create_notifications_table
    2026_07_13_082321_add_foreign_keys_to_commission_payments
    2026_07_13_082321_add_foreign_keys_to_user_higher_ranks
    2026_07_13_101129_add_pv_bv_to_order_items_table
    2026_07_21_120659_add_flexpay_columns_to_transactions_table
    2026_10_03_120000_add_genealogy_and_monthly_pv_to_users_table
    2026_10_03_130000_create_pv_history_and_mlm_test_schema
    2026_10_03_140000_add_flexpay_columns_to_transactions_table
)

SQL="INSERT INTO migrations (migration, batch) VALUES "
values=()
for m in "${MARK_RAN[@]}"; do
    values+=("( '$m', 999 )")
done
SQL+=$(IFS=,; echo "${values[*]}")
SQL+=" ON DUPLICATE KEY UPDATE batch=batch;"

if [[ "$DRY_RUN" == "1" ]]; then
    echo "📝 [DRY-RUN] Marquera ${#MARK_RAN[@]} migrations (batch 999) :"
    printf '  - %s\n' "${MARK_RAN[@]}"
    echo ""
    echo "Pour exécuter : DRY_RUN=0 $0"
    exit 0
fi

export MYSQL_PWD="$DB_PASSWORD"
echo "📝 Marquage migrations déjà appliquées..."
mysql -u "$DB_USERNAME" -h "$DB_HOST" "$DB_DATABASE" -e "$SQL"
unset MYSQL_PWD

echo ""
php artisan migrate:status --no-interaction | grep -i pending || echo "✅ Aucune pending"
