#!/usr/bin/env bash
# Lance Dusk avec --env=dusk et rappelle de vérifier .env après le run.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

if grep -q '^DB_CONNECTION=mysql' .env 2>/dev/null; then
    echo "✓ .env : MySQL (dev) avant Dusk"
else
    echo "⚠️  .env ne pointe pas vers MySQL — sauvegarder ou restaurer (.env.local) avant de continuer."
    read -r -p "Continuer quand même ? [y/N] " ans
    [[ "${ans:-N}" =~ ^[yY]$ ]] || exit 1
fi

php artisan config:clear --env=dusk
php artisan dusk --env=dusk "$@" --without-tty

echo ""
echo "Après Dusk, vérifier .env dev :"
echo "  grep DB_CONNECTION .env   # → mysql"
echo "Si SQLite : cp .env.local .env && php artisan config:clear"
