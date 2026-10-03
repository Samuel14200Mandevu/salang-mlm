#!/usr/bin/env bash
# Phase 5 — UNE période (sync + simulate + payment). NE PAS lancer sans validation Samuel.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

PERIOD="${1:-}"
if [[ -z "$PERIOD" ]]; then
  echo "Usage: $0 YYYY-MM"
  echo "Exemple: $0 2023-11"
  exit 1
fi

if [[ "${CONFIRM_PHASE5:-}" != "yes" ]]; then
  echo "❌ Définir CONFIRM_PHASE5=yes pour exécuter (écritures MySQL)."
  echo "   Dry-run seulement : ./scripts/sync-payment-dry-run.sh --period=$PERIOD"
  exit 1
fi

echo "=== Phase 5 période $PERIOD ==="
./scripts/backup-db.sh

php artisan commissions:sync-from-history --period="$PERIOD" --ensure-periods
php artisan commissions:simulate-payments --period="$PERIOD"

read -r -p "Lancer generate-payments pour $PERIOD ? [y/N] " ans
[[ "${ans:-N}" =~ ^[yY]$ ]] || exit 0

php artisan commissions:generate-payments "$PERIOD"

echo "=== Post-checks (lecture) ==="
ENV_FILE="${ENV_FILE:-$ROOT/.env.local}"
load_env() { grep -E "^${1}=" "$ENV_FILE" | head -1 | cut -d= -f2- | sed 's/^"//;s/"$//'; }
export MYSQL_PWD="$(load_env DB_PASSWORD)"
mysql -h"$(load_env DB_HOST)" -u"$(load_env DB_USERNAME)" "$(load_env DB_DATABASE)" -e "
SELECT period, status, total_commissions, total_paid FROM commission_periods WHERE period='$PERIOD';
SELECT COUNT(*) AS commissions_paid FROM commissions WHERE period='$PERIOD' AND status='paid';
SELECT COUNT(*) AS users FROM users;
"
