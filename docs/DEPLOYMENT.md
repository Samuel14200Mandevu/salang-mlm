# Déploiement — Salang MLM

**Routine quotidienne** (local validé → prod) : [ROUTINE_FIN_DE_JOURNEE.md](./ROUTINE_FIN_DE_JOURNEE.md).

## Règles de production

1. **Jamais** `php artisan migrate:fresh` sur `salang_mlm`
2. **Jamais** `php artisan migrate:refresh`
3. **Jamais** `php artisan db:wipe` sur la base prod
4. **Jamais** Dusk avec le `.env` MySQL (toujours `--env=dusk`)
5. **Toujours** un backup avant modification de schéma (`./scripts/backup-db.sh`)
6. **Toujours** vérifier `grep DB_CONNECTION .env` → `mysql` après un run Dusk

Si une migration doit être annulée :

- Utiliser `php artisan migrate:rollback --step=1` (pas `fresh`)
- Vérifier le backup avant rollback
- Tester en staging si possible

## Migrations en prod

```bash
./scripts/backup-db.sh
php artisan migrate --force
php artisan config:cache   # si vous utilisez le cache config en prod
```

## Références

- Sauvegardes : [BACKUP.md](./BACKUP.md)
- Tests browser : [DUSK.md](./DUSK.md)
