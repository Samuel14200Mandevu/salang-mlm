#!/usr/bin/env bash
# Backup MySQL salang_mlm (prod/dev) — non destructif.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

ENV_FILE="${ENV_FILE:-$ROOT/.env.local}"
if [[ ! -f "$ENV_FILE" ]]; then
    ENV_FILE="$ROOT/.env"
fi

load_env() {
    local key="$1"
    grep -E "^${key}=" "$ENV_FILE" 2>/dev/null | head -1 | cut -d= -f2- | sed 's/^"//;s/"$//'
}

DB_HOST="$(load_env DB_HOST)"
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="$(load_env DB_PORT)"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="$(load_env DB_DATABASE)"
DB_USERNAME="$(load_env DB_USERNAME)"
DB_PASSWORD="$(load_env DB_PASSWORD)"

if [[ "${DB_CONNECTION:-$(load_env DB_CONNECTION)}" != "mysql" ]] || [[ -z "$DB_DATABASE" ]]; then
    echo "❌ DB non MySQL ou DATABASE manquant (fichier: $ENV_FILE)"
    exit 1
fi

if [[ "$DB_DATABASE" != "salang_mlm" ]]; then
    echo "⚠️  Base cible: $DB_DATABASE (attendu salang_mlm pour prod). Continuer ?"
    read -r -p "[y/N] " ans
    [[ "${ans:-N}" =~ ^[yY]$ ]] || exit 1
fi

STAMP="$(date +%Y%m%d_%H%M%S)"
PROJECT_BACKUP="$ROOT/storage/backups"
HOME_BACKUP="${HOME}/Backups_Salang"
mkdir -p "$PROJECT_BACKUP" "$HOME_BACKUP"

FILE_BASE="salang_mlm_PROD_${STAMP}.sql.gz"
PROJECT_FILE="$PROJECT_BACKUP/$FILE_BASE"
HOME_FILE="$HOME_BACKUP/$FILE_BASE"

echo "📦 Dump MySQL → $DB_DATABASE …"

export MYSQL_PWD="$DB_PASSWORD"
mysqldump -u "$DB_USERNAME" -h "$DB_HOST" -P "$DB_PORT" \
    --single-transaction --quick \
    --routines --triggers --events \
    "$DB_DATABASE" | gzip > "$PROJECT_FILE"
unset MYSQL_PWD

cp "$PROJECT_FILE" "$HOME_FILE"

gunzip -t "$PROJECT_FILE"
gunzip -t "$HOME_FILE"

echo "✅ Backup valide"
ls -lh "$PROJECT_FILE" "$HOME_FILE"

# Rotation 30 jours
find "$PROJECT_BACKUP" -name 'salang_mlm_PROD_*.sql.gz' -mtime +30 -delete 2>/dev/null || true
find "$HOME_BACKUP" -name 'salang_mlm_PROD_*.sql.gz' -mtime +30 -delete 2>/dev/null || true

echo "📊 Volumes (extrait) :"
export MYSQL_PWD="$DB_PASSWORD"
mysql -u "$DB_USERNAME" -h "$DB_HOST" -P "$DB_PORT" "$DB_DATABASE" -N -e \
    "SELECT CONCAT('users=', COUNT(*)) FROM users UNION ALL SELECT CONCAT('commission_history=', COUNT(*)) FROM commission_history;"
unset MYSQL_PWD
