# Sauvegarde base `salang_mlm`

Base **production / dev réelle** (~2307 utilisateurs). Toute opération destructive est interdite sans backup vérifié.

## Backup manuel (urgence)

```bash
mkdir -p ~/Backups_Salang

mysqldump -u admin -p \
  --single-transaction --quick \
  --routines --triggers --events \
  salang_mlm | gzip > ~/Backups_Salang/salang_mlm_PROD_$(date +%Y%m%d_%H%M%S).sql.gz

gunzip -t ~/Backups_Salang/salang_mlm_PROD_*.sql.gz && echo "Backup valide"
ls -lh ~/Backups_Salang/
```

## Script automatique

```bash
chmod +x scripts/backup-db.sh
./scripts/backup-db.sh
```

- Lit les credentials depuis `.env.local` (sinon `.env`)
- Écrit dans `storage/backups/` **et** `~/Backups_Salang/`
- Vérifie l’archive avec `gunzip -t`
- Supprime les fichiers de plus de **30 jours** dans ces deux dossiers
- Affiche un extrait : `users`, `commission_history`

## Cron (quotidien 03:00)

Adapter le chemin du projet :

```cron
0 3 * * * /home/samuel-mandevu/salang-mlm-ecommerce/scripts/backup-db.sh >> /var/log/salang-backup.log 2>&1
```

Installer : `crontab -e`

## Restauration (procédure)

**Tester d’abord sur une base vide**, jamais directement sur prod sans backup récent.

```bash
# 1. Backup de sécurité avant restauration
./scripts/backup-db.sh

# 2. Choisir l’archive
ARCHIVE=~/Backups_Salang/salang_mlm_PROD_YYYYMMDD_HHMMSS.sql.gz
gunzip -t "$ARCHIVE"

# 3. Restaurer (base déjà créée, charset utf8mb4)
gunzip -c "$ARCHIVE" | mysql -u admin -p salang_mlm

# 4. Contrôle
./scripts/check-db.sh salang_mlm
```

Attendu après restauration typique : **users ≈ 2307**, **commission_history ≈ 11854**.

## Emplacements

| Emplacement | Rôle |
|-------------|------|
| `~/Backups_Salang/` | Copie hors projet (USB / sync Drive) |
| `storage/backups/` | Copie locale projet (gitignore) |

## Triple protection anti-catastrophe

1. **Dusk** : SQLite `database/dusk.sqlite` + guard dans `DuskTestCase` (voir `docs/DUSK.md`)
2. **Artisan** : `BlockMigrateFreshProvider` bloque `migrate:fresh` / `refresh` / `db:wipe` sur `salang_mlm`
3. **Backups** : script + cron + copie `~/Backups_Salang/`

Forcer une commande destructive (hors prod) : `DB_ALLOW_DESTRUCTIVE=yes php artisan …`  
Sur prod `salang_mlm` : **toujours bloqué**, même avec cette variable.
