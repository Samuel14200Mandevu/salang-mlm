# Optimisation des images

## Format WebP

Les assets marketing dans `public/images/` sont servis en **WebP** avec repli PNG/JPG pour les navigateurs non compatibles.

## Conversion

```bash
chmod +x scripts/convert-images-to-webp.sh
./scripts/convert-images-to-webp.sh public/images
```

Options :

- **Qualité** : 80 (défaut, 2ᵉ argument du script)
- **Moteur** : `cwebp` si installé (`sudo apt-get install webp`), sinon **PHP GD** (`scripts/convert-webp.php`)
- **cwebp** : méthode 6, multi-threading (`-m 6 -mt`)
- Les originaux sont copiés dans `storage/backups/images-YYYYMMDD_HHMMSS/` avant conversion

## Composant Blade

```blade
<x-ui.image
    src="images/salang_logo.png"
    alt="Logo Salang"
    width="200"
    height="48"
    :priority="true"
    :lazy="false"
/>
```

Génère :

- `<picture>` + `<source type="image/webp">` si le `.webp` existe
- Fallback PNG/JPG
- `loading="lazy"` par défaut ; `priority` → `fetchpriority="high"` + `loading="eager"`

## Règles

| Contexte | Attributs |
|----------|-----------|
| Logo header / LCP | `:priority="true"`, `width` + `height` |
| Contenu below the fold | `loading="lazy"` (défaut) |
| Toujours | `alt` descriptif, dimensions explicites si possible |

## Preload LCP

Sur `welcome.blade.php` : preload `salang_logo.webp` (logo) et `site.webp` (hero background).

## Backups

- Conversion batch : `storage/backups/images-*`
- Base MySQL : `scripts/backup-db.sh` (voir `docs/BACKUP.md`)
