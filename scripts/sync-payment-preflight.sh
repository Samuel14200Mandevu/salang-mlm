#!/usr/bin/env bash
# Lecture seule — vérifie l'état prod avant sync/paiement (Phase 4/5).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

ENV_FILE="${ENV_FILE:-$ROOT/.env.local}"
[[ -f "$ENV_FILE" ]] || ENV_FILE="$ROOT/.env"

load_env() {
  grep -E "^${1}=" "$ENV_FILE" 2>/dev/null | head -1 | cut -d= -f2- | sed 's/^"//;s/"$//'
}

DB_HOST="$(load_env DB_HOST)"; DB_HOST="${DB_HOST:-127.0.0.1}"
DB_USER="$(load_env DB_USERNAME)"
DB_NAME="$(load_env DB_DATABASE)"
export MYSQL_PWD="$(load_env DB_PASSWORD)"

echo "=== Preflight sync/payment (lecture seule) ==="
echo "Env: $ENV_FILE | DB: $DB_NAME @ $DB_HOST"
echo ""

echo "--- Migration 150000 ---"
php artisan migrate:status --no-interaction 2>/dev/null | grep 150000 || echo "(migration 150000 introuvable dans migrate:status)"

echo ""
echo "--- Colonne source_commission_history_id ---"
mysql -h"$DB_HOST" -u"$DB_USER" "$DB_NAME" -e "SHOW COLUMNS FROM commissions LIKE 'source_commission_history_id';" || true

echo ""
echo "--- Comptages ---"
mysql -h"$DB_HOST" -u"$DB_USER" "$DB_NAME" -e "
SELECT 'users' AS t, COUNT(*) AS n FROM users
UNION ALL SELECT 'commission_history', COUNT(*) FROM commission_history
UNION ALL SELECT 'commissions', COUNT(*) FROM commissions
UNION ALL SELECT 'commission_periods', COUNT(*) FROM commission_periods
UNION ALL SELECT 'wallets', COUNT(*) FROM wallets;
"

echo ""
echo "--- Périodes history (34 attendues) ---"
mysql -h"$DB_HOST" -u"$DB_USER" "$DB_NAME" -e "
SELECT COUNT(DISTINCT period) AS distinct_periods,
       ROUND(SUM(amount),2) AS history_gross
FROM commission_history;
"

echo ""
echo "--- Config commission (env) ---"
grep -E '^COMMISSION_' "$ENV_FILE" 2>/dev/null || echo "(aucune COMMISSION_* dans $ENV_FILE → défauts config/commission.php)"

echo ""
echo "✅ Preflight terminé (aucune écriture)."
