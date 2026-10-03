#!/usr/bin/env bash
# Vérifie les volumes critiques sur MySQL salang_mlm (sans migrate:fresh).

set -euo pipefail

DB="${1:-salang_mlm}"
USER="${MYSQL_USER:-admin}"

echo "Vérification intégrité base ${DB}..."

mysql -u "${USER}" -p "${DB}" -e "
SELECT 'users' AS t, COUNT(*) AS n FROM users
UNION ALL SELECT 'orders', COUNT(*) FROM orders
UNION ALL SELECT 'commissions', COUNT(*) FROM commissions
UNION ALL SELECT 'commission_history', COUNT(*) FROM commission_history
UNION ALL SELECT 'commission_periods', COUNT(*) FROM commission_periods;
" 2>/dev/null || {
    echo "Impossible de se connecter. Définir MYSQL_USER ou passer: ./scripts/check-db.sh salang_mlm"
    exit 1
}

echo ""
echo "Si une table critique est à 0, ne pas lancer migrate:fresh sans --env=dusk."
