#!/usr/bin/env bash
# Dry-run sync history → commissions (aucune écriture si --dry-run).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "=== Dry-run: commissions:sync-from-history ==="
php artisan commissions:sync-from-history --dry-run --ensure-periods "$@"

echo ""
echo "Note: simulate-payments nécessite commission_periods + commissions réels"
echo "      (après migration 150000 et sync réel d'au moins une période)."
echo "      Voir docs/OPS_RUNBOOK_SYNC_PAYMENT.md"
