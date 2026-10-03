# Checklist de production — Salang MLM

## Backend

- [ ] Tests : `php artisan test` → 54/54
- [ ] Tests Dusk : 4/4 (SQLite dédié, **pas** MySQL prod)

```bash
php artisan migrate:fresh --force --env=dusk
php artisan serve --env=dusk --host=127.0.0.1 --port=8000 &
php artisan dusk tests/Browser/LoginTest.php tests/Browser/WalletTest.php \
  tests/Browser/CommissionTest.php tests/Browser/PurchaseTest.php
```
- [ ] Migrations : `php artisan migrate --force`
- [ ] Cache : `php artisan config:cache route:cache view:cache`
- [ ] Queue : `php artisan queue:restart`
- [ ] Scheduler : crontab `* * * * * php artisan schedule:run`

## Frontend

- [ ] Build : `npm run build`
- [ ] Bundle initial JS+CSS : ~224 KB (hors chunk `charts.js`)
- [ ] Images WebP : `ls public/images/*.webp`
- [ ] Manifest PWA : `public/site.webmanifest`

## Environnement

- [ ] `.env` : `APP_ENV=production`, `APP_DEBUG=false`
- [ ] `LOG_CHANNEL=stack`
- [ ] Credentials base de données production
- [ ] `FLEXPAY_WEBHOOK_SECRET` (et autres webhooks actifs)

## Sécurité

- [ ] HTTPS forcé
- [ ] CSRF actif sur les formulaires web
- [ ] Rate limiting API / login
- [ ] Webhooks HMAC + idempotence
- [ ] Logs sans tokens ni en-têtes sensibles

## Monitoring & backup

- [ ] Outil d’erreurs (Sentry, etc.)
- [ ] Sauvegarde DB quotidienne + test de restauration

## Documentation

- [ ] [README.md](../README.md)
- [ ] [docs/API.md](API.md)
- [ ] [docs/MLM_SYSTEM.md](MLM_SYSTEM.md)
- [ ] [CHANGELOG.md](../CHANGELOG.md)
