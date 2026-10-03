#!/usr/bin/env bash
# Convertit PNG/JPEG de public/images en WebP (cwebp ou Node/sharp).

set -euo pipefail

IMAGES_DIR="${1:-public/images}"
QUALITY="${2:-80}"
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

convert_with_cwebp() {
    local file="$1"
    local output="${file%.*}.webp"
    if [[ -f "$output" && "$output" -nt "$file" ]]; then
        echo "⏭️  Skip: $(basename "$file") (déjà converti)"
        return 0
    fi
    local original_size new_size
    original_size=$(stat -c%s "$file" 2>/dev/null || stat -f%z "$file")
    if cwebp -q "$QUALITY" "$file" -o "$output" -quiet 2>/dev/null; then
        new_size=$(stat -c%s "$output" 2>/dev/null || stat -f%z "$output")
        echo "✅ $(basename "$file"): $((original_size / 1024)) KB → $((new_size / 1024)) KB"
    else
        echo "❌ Erreur cwebp: $(basename "$file")"
        return 1
    fi
}

convert_with_sharp() {
    node <<'NODE'
const fs = require('fs');
const path = require('path');
const sharp = require('sharp');

const dir = process.env.IMAGES_DIR || 'public/images';
const quality = Number(process.env.QUALITY || 80);

async function run() {
    const files = [];
    function walk(d) {
        for (const ent of fs.readdirSync(d, { withFileTypes: true })) {
            const p = path.join(d, ent.name);
            if (ent.isDirectory()) walk(p);
            else if (/\.(png|jpe?g)$/i.test(ent.name)) files.push(p);
        }
    }
    walk(dir);
    for (const file of files) {
        const output = file.replace(/\.(png|jpe?g)$/i, '.webp');
        if (fs.existsSync(output) && fs.statSync(output).mtimeMs > fs.statSync(file).mtimeMs) {
            console.log(`⏭️  Skip: ${path.basename(file)}`);
            continue;
        }
        const before = fs.statSync(file).size;
        await sharp(file).webp({ quality }).toFile(output);
        const after = fs.statSync(output).size;
        console.log(`✅ ${path.basename(file)}: ${Math.round(before / 1024)} KB → ${Math.round(after / 1024)} KB`);
    }
}
run().catch((e) => {
    console.error(e);
    process.exit(1);
});
NODE
}

echo "🖼️  Conversion WebP (qualité: $QUALITY) dans $IMAGES_DIR"
echo ""

if command -v cwebp >/dev/null 2>&1; then
    find "$IMAGES_DIR" -type f \( -iname '*.png' -o -iname '*.jpg' -o -iname '*.jpeg' \) | while read -r file; do
        convert_with_cwebp "$file" || true
    done
elif node -e "require('sharp')" 2>/dev/null; then
    export IMAGES_DIR QUALITY
    convert_with_sharp
else
    echo "❌ Installez webp (cwebp) ou exécutez: npm install --save-dev sharp"
    exit 1
fi

echo ""
echo "🎉 Conversion terminée."
