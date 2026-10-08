#!/usr/bin/env bash
# Restaure MySQL salang_mlm depuis une archive .sql ou .sql.gz (LOCAL uniquement).
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

if [[ "$(load_env DB_CONNECTION)" != "mysql" ]] || [[ -z "$DB_DATABASE" ]]; then
    echo "❌ Connexion MySQL / DB_DATABASE manquant ($ENV_FILE)"
    exit 1
fi

if [[ "$DB_HOST" != "127.0.0.1" && "$DB_HOST" != "localhost" ]]; then
    echo "❌ Refus : restauration autorisée seulement sur localhost (DB_HOST=$DB_HOST)"
    exit 1
fi

ARCHIVE="${1:-}"
if [[ -z "$ARCHIVE" ]]; then
    if [[ -f "$HOME/Documents/salang/salang_mlm.sql" ]]; then
        ARCHIVE="$HOME/Documents/salang/salang_mlm.sql"
    else
        ARCHIVE="$(ls -t "$HOME/Backups_Salang"/salang_mlm_PROD_*.sql.gz 2>/dev/null | head -1 || true)"
    fi
fi

if [[ -z "$ARCHIVE" || ! -f "$ARCHIVE" ]]; then
    echo "Usage: $0 [/chemin/vers/dump.sql|.sql.gz]"
    exit 1
fi

echo "📁 Archive : $ARCHIVE"
echo "🎯 Base    : $DB_DATABASE @ $DB_HOST"
echo ""

if [[ "$ARCHIVE" == *.gz ]]; then
    gunzip -t "$ARCHIVE"
else
    [[ -s "$ARCHIVE" ]] || { echo "❌ Fichier vide"; exit 1; }
fi

if [[ "${SKIP_BACKUP:-0}" != "1" ]]; then
    echo "💾 Backup de sécurité avant écrasement…"
    if ! ENV_FILE="$ENV_FILE" "$ROOT/scripts/backup-db.sh"; then
        echo "⚠️  Backup local ignoré (base peut-être incomplète). Continuer…"
    fi
else
    echo "⏭️  SKIP_BACKUP=1 — pas de backup local avant import."
fi

export MYSQL_PWD="$DB_PASSWORD"
MYSQL=(mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME")

echo "🗑️  Suppression et recréation de la base ${DB_DATABASE}…"
"${MYSQL[@]}" -e "DROP DATABASE IF EXISTS \`${DB_DATABASE}\`; CREATE DATABASE \`${DB_DATABASE}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo "📥 Import en cours (peut prendre plusieurs minutes)…"
if [[ "$ARCHIVE" == *.gz ]]; then
    gunzip -c "$ARCHIVE" | "${MYSQL[@]}" "$DB_DATABASE"
else
    "${MYSQL[@]}" "$DB_DATABASE" < "$ARCHIVE"
fi

unset MYSQL_PWD

echo ""
echo "✅ Import terminé. Contrôle :"
php -r "
require '$ROOT/vendor/autoload.php';
\$app = require '$ROOT/bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
foreach (['users','commission_history','orders'] as \$t) {
    echo \$t.': '.Illuminate\Support\Facades\DB::table(\$t)->count().PHP_EOL;
}
"

echo ""
echo "Si le schéma est plus ancien que le code : php artisan migrate"
