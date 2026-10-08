#!/usr/bin/env bash
# Copie PRODUCTION → LOCAL (inverse de push-db-to-production.sh).
# Copie la base MySQL PRODUCTION (Laravel Cloud) → MySQL local salang_mlm.
# Lecture seule sur la prod. Ne modifie jamais la base distante.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

PROD_ENV="${PROD_ENV:-$ROOT/.env.production}"
LOCAL_ENV="${LOCAL_ENV:-$ROOT/.env.local}"
if [[ ! -f "$LOCAL_ENV" ]]; then
    LOCAL_ENV="$ROOT/.env"
fi

load_env() {
    local file="$1" key="$2"
    grep -E "^${key}=" "$file" 2>/dev/null | head -1 | cut -d= -f2- | sed 's/^"//;s/"$//'
}

for f in "$PROD_ENV" "$LOCAL_ENV"; do
    [[ -f "$f" ]] || { echo "❌ Fichier manquant : $f"; exit 1; }
done

P_HOST="$(load_env "$PROD_ENV" DB_HOST)"
P_PORT="$(load_env "$PROD_ENV" DB_PORT)"; P_PORT="${P_PORT:-3306}"
P_DB="$(load_env "$PROD_ENV" DB_DATABASE)"
P_USER="$(load_env "$PROD_ENV" DB_USERNAME)"
P_PASS="$(load_env "$PROD_ENV" DB_PASSWORD)"

L_HOST="$(load_env "$LOCAL_ENV" DB_HOST)"; L_HOST="${L_HOST:-127.0.0.1}"
L_PORT="$(load_env "$LOCAL_ENV" DB_PORT)"; L_PORT="${L_PORT:-3306}"
L_DB="$(load_env "$LOCAL_ENV" DB_DATABASE)"
L_USER="$(load_env "$LOCAL_ENV" DB_USERNAME)"
L_PASS="$(load_env "$LOCAL_ENV" DB_PASSWORD)"

if [[ -z "$P_HOST" || -z "$P_DB" || -z "$P_USER" || -z "$P_PASS" ]]; then
    echo "❌ Credentials prod incomplets dans $PROD_ENV"
    exit 1
fi

if [[ "$L_HOST" != "127.0.0.1" && "$L_HOST" != "localhost" ]]; then
    echo "❌ Refus : la cible locale doit être localhost (DB_HOST=$L_HOST)"
    exit 1
fi

if [[ "$L_DB" != "salang_mlm" ]]; then
    echo "❌ Refus : base locale attendue salang_mlm (actuel : $L_DB)"
    exit 1
fi

STAMP="$(date +%Y%m%d_%H%M%S)"
DUMP="$ROOT/storage/backups/salang_mlm_FROM_PROD_${STAMP}.sql.gz"
mkdir -p "$(dirname "$DUMP")"

echo "📡 Export prod (lecture seule) : $P_DB @ $P_HOST"
export MYSQL_PWD="$P_PASS"
mysqldump -h "$P_HOST" -P "$P_PORT" -u "$P_USER" \
    --single-transaction --quick --routines --triggers --events \
    --set-gtid-purged=OFF \
    "$P_DB" | gzip > "$DUMP"
unset MYSQL_PWD

gunzip -t "$DUMP"
echo "💾 Dump prod : $DUMP"

echo "📥 Restauration locale…"
"$ROOT/scripts/restore-db.sh" "$DUMP"

echo ""
echo "🔧 Réconciliation migrations (schéma code vs dump prod)…"
DRY_RUN=0 ENV_FILE="$LOCAL_ENV" "$ROOT/scripts/reconcile-migrations.sh"
php artisan migrate --force

echo ""
echo "✅ Base locale alignée sur la production Cloud."
