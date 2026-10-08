#!/usr/bin/env bash
# ⚠️  ÉCRASE la base MySQL PRODUCTION (Laravel Cloud) avec le dump LOCAL salang_mlm.
# Toujours : backup prod → fichier, puis import local → prod.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

PROD_ENV="${PROD_ENV:-$ROOT/.env.production}"
LOCAL_ENV="${LOCAL_ENV:-$ROOT/.env.local}"

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

if [[ "$L_DB" != "salang_mlm" || "$P_DB" != "salang_mlm" ]]; then
    echo "❌ Bases attendues : salang_mlm (local et prod)"
    exit 1
fi

STAMP="$(date +%Y%m%d_%H%M%S)"
PROD_BACKUP="$ROOT/storage/backups/salang_mlm_PROD_BEFORE_PUSH_${STAMP}.sql.gz"
LOCAL_DUMP="$ROOT/storage/backups/salang_mlm_LOCAL_FOR_PUSH_${STAMP}.sql.gz"
mkdir -p "$(dirname "$PROD_BACKUP")"

echo "⚠️  Cette opération REMPLACE toute la base production Cloud."
echo "   Confirmer en tapant exactement : PUSH-PROD"
read -r -p "> " confirm
[[ "$confirm" == "PUSH-PROD" ]] || { echo "Annulé."; exit 1; }

if [[ "${SKIP_PROD_BACKUP:-0}" == "1" ]]; then
    echo "⏭️  SKIP_PROD_BACKUP=1 — pas de dump prod (utiliser un backup existant si besoin)."
else
    echo "💾 Backup prod (sécurité)…"
    export MYSQL_PWD="$P_PASS"
    for attempt in 1 2 3; do
        if mysqldump -h "$P_HOST" -P "$P_PORT" -u "$P_USER" \
            --single-transaction --quick --skip-lock-tables \
            --skip-triggers --skip-routines --skip-events \
            --set-gtid-purged=OFF \
            "$P_DB" | gzip > "$PROD_BACKUP"; then
            break
        fi
        echo "   Tentative $attempt/3 échouée, nouvel essai dans 5s…"
        sleep 5
        [[ "$attempt" -eq 3 ]] && { unset MYSQL_PWD; exit 1; }
    done
    unset MYSQL_PWD
    gunzip -t "$PROD_BACKUP"
    echo "   → $PROD_BACKUP"
fi

echo "📤 Export local…"
export MYSQL_PWD="$L_PASS"
# Pas de triggers/routines : DEFINER local (admin@localhost) refusé sur Laravel Cloud.
mysqldump -h "$L_HOST" -P "$L_PORT" -u "$L_USER" \
    --single-transaction --quick \
    --skip-triggers --skip-routines --skip-events \
    --set-gtid-purged=OFF \
    "$L_DB" | gzip > "$LOCAL_DUMP"
unset MYSQL_PWD
gunzip -t "$LOCAL_DUMP"
echo "   → $LOCAL_DUMP"

echo "🚀 Import local → production (plusieurs minutes)…"
gunzip -c "$LOCAL_DUMP" \
    | sed -E 's/DEFINER=[^*]*\*/\*/g' \
    | MYSQL_PWD="$P_PASS" mysql -h "$P_HOST" -P "$P_PORT" -u "$P_USER" "$P_DB"

echo ""
echo "✅ Production mise à jour depuis le local."
echo "   Vérifier sur Cloud : php artisan migrate --force si des migrations manquent côté prod."
