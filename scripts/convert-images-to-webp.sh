#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

IMAGES_DIR="${1:-public/images}"
QUALITY="${2:-80}"
BACKUP_DIR="storage/backups/images-$(date +%Y%m%d_%H%M%S)"

if [[ ! -d "$IMAGES_DIR" ]]; then
    echo "❌ Dossier introuvable : $IMAGES_DIR"
    exit 1
fi

if command -v cwebp &>/dev/null; then
    CONVERTER=cwebp
elif php -r "exit(extension_loaded('gd') && function_exists('imagewebp') ? 0 : 1);" 2>/dev/null; then
    CONVERTER=php
else
    echo "❌ Installez webp (cwebp) ou activez GD imagewebp en PHP."
    exit 1
fi

echo "🖼️  Conversion WebP (qualité: $QUALITY, moteur: $CONVERTER)"
echo "📂 Source : $IMAGES_DIR"
echo "💾 Backup : $BACKUP_DIR"
echo ""

mkdir -p "$BACKUP_DIR"

if [[ "$CONVERTER" == php ]]; then
    while IFS= read -r -d '' file; do
        cp "$file" "$BACKUP_DIR/"
    done < <(find "$IMAGES_DIR" -type f \( -iname "*.png" -o -iname "*.jpg" -o -iname "*.jpeg" \) -print0)
    php "$ROOT/scripts/convert-webp.php" "$IMAGES_DIR" "$QUALITY"
    echo ""
    echo "⚠️  Originaux backupés dans : $BACKUP_DIR"
    exit 0
fi

count=0
skipped=0
saved=0

while IFS= read -r -d '' file; do
    output="${file%.*}.webp"

    if [[ -f "$output" && "$output" -nt "$file" ]]; then
        echo "⏭️  Skip : $(basename "$file")"
        skipped=$((skipped + 1))
        continue
    fi

    cp "$file" "$BACKUP_DIR/"

    original_size=$(stat -c%s "$file" 2>/dev/null || stat -f%z "$file")

    if cwebp -q "$QUALITY" -m 6 -mt "$file" -o "$output" -quiet 2>/dev/null; then
        new_size=$(stat -c%s "$output" 2>/dev/null || stat -f%z "$output")
        gain=$((original_size - new_size))
        saved=$((saved + gain))
        count=$((count + 1))
        pct=0
        if [[ "$original_size" -gt 0 ]]; then
            pct=$((gain * 100 / original_size))
        fi
        printf "✅ %-40s %5d KB → %5d KB (-%d%%)\n" \
            "$(basename "$file")" \
            "$((original_size / 1024))" \
            "$((new_size / 1024))" \
            "$pct"
    else
        echo "❌ Erreur : $(basename "$file")"
    fi
done < <(find "$IMAGES_DIR" -type f \( -iname "*.png" -o -iname "*.jpg" -o -iname "*.jpeg" \) -print0)

echo ""
echo "🎉 Conversion terminée"
echo "   Converties : $count"
echo "   Skippées   : $skipped"
echo "   Gain total : $((saved / 1024)) KB"
echo ""
echo "⚠️  Originaux backupés dans : $BACKUP_DIR"
