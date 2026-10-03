# Tests Dusk

## Prérequis

- Chromium / Google Chrome
- `php artisan dusk:chrome-driver --detect`

## Configuration (SQLite, pas MySQL prod)

Créer `.env.dusk` à la racine :

```env
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=sqlite
DB_DATABASE=database/dusk.sqlite
SESSION_DRIVER=file
CACHE_STORE=file
```

Puis :

```bash
php artisan key:generate --env=dusk --force
rm -f database/dusk.sqlite && touch database/dusk.sqlite
php artisan migrate:fresh --force --env=dusk
php artisan serve --env=dusk --host=127.0.0.1 --port=8000 &
php artisan dusk tests/Browser/LoginTest.php tests/Browser/WalletTest.php \
  tests/Browser/CommissionTest.php tests/Browser/PurchaseTest.php
```

**Ne pas** utiliser `DatabaseMigrations` dans les tests Browser : cela peut lancer `migrate:fresh` sur la base par défaut (MySQL).
