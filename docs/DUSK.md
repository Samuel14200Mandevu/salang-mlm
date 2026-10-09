# Tests Dusk — Salang MLM

## Règles absolues

1. **Ne jamais** lancer Dusk avec le `.env` MySQL (`salang_mlm`).
2. **Ne jamais** utiliser `DatabaseMigrations` / `RefreshDatabase` dans `tests/Browser/`.
3. **Toujours** `--env=dusk` pour migrate, serve et dusk.
4. **`migrate:fresh`** uniquement sur `database/dusk.sqlite`, jamais sur MySQL prod.

`tests/DuskTestCase.php` lève une **RuntimeException** si la connexion n’est pas SQLite ou si le nom de base contient `salang_mlm`, `production` ou `prod`.  
`phpunit.dusk.xml` force `DB_DATABASE=database/dusk.sqlite` (évite le `:memory:` de `phpunit.xml`).  
**Ne pas mettre `APP_ENV=testing` dans `.env.dusk`** : Laravel charge alors `.env.testing` (`DB_DATABASE=:memory:`).

## Prérequis

- Chromium / Google Chrome
- `php artisan dusk:chrome-driver --detect`

## Configuration `.env.dusk`

Fichier **local** (ignoré par git via `.env*`). Créer à la racine du projet :

```env
APP_NAME="Salang MLM (Dusk)"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=sqlite
DB_DATABASE=/chemin/absolu/vers/projet/database/dusk.sqlite

CACHE_STORE=array
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
MAIL_MAILER=array

DUSK_DRIVER_URL=http://localhost:9515
```

```bash
php artisan config:clear --env=dusk
php artisan key:generate --env=dusk --force
touch database/dusk.sqlite
php artisan migrate:fresh --force --env=dusk
php artisan db:seed --class=DuskSeeder --env=dusk
```

Compte seed : `test@example.com` / `password`.

## Lancer les tests

Terminal 1 :

```bash
php artisan serve --env=dusk --host=127.0.0.1 --port=8000
```

Terminal 2 :

```bash
php artisan dusk --env=dusk tests/Browser/LoginTest.php tests/Browser/WalletTest.php \
  tests/Browser/CommissionTest.php tests/Browser/PurchaseTest.php --without-tty
```

Ou via le wrapper :

```bash
chmod +x scripts/dusk-safe.sh
./scripts/dusk-safe.sh tests/Browser/LoginTest.php
```

## Vérifier MySQL prod (sans Dusk)

```bash
chmod +x scripts/check-db.sh
./scripts/check-db.sh salang_mlm
```

## Diagnostic

| Problème | Action |
|----------|--------|
| Exception guard MySQL | Créer `.env.dusk`, relancer avec `--env=dusk` |
| Chrome / Chromedriver | `php artisan dusk:chrome-driver --detect` ; sur Linux Snap : `DUSK_CHROME_BINARY=/snap/bin/chromium` |
| Timeout login | Serveur `--env=dusk` sur le même port que `APP_URL` ; `SESSION_DRIVER=file` (pas `array` avec `artisan serve`) |
| Données absentes côté navigateur | `db:seed --class=DuskSeeder --env=dusk` puis redémarrer `serve` |

## Données MySQL vs Dusk

La base MySQL restaurée (2307 users, etc.) **n’est pas** utilisée par Dusk. Les tests Browser utilisent **uniquement** SQLite `database/dusk.sqlite` + `DuskSeeder` (données minimales reproductibles).

## Fichiers .env par environnement

| Fichier | Usage | DB | Versionné ? |
|---------|-------|-----|-------------|
| `.env` | Dev quotidien (MySQL) | `salang_mlm` | ❌ gitignore |
| `.env.local` | Backup dev MySQL | `salang_mlm` | ❌ gitignore |
| `.env.testing` | `php artisan test` | `:memory:` | ❌ gitignore |
| `.env.dusk` | Dusk E2E | `database/dusk.sqlite` | ❌ gitignore |
| `.env.dusk.example` | Template Dusk | — | ✅ versionné |
| `.env.testing.example` | Template testing | — | ✅ versionné |

### Piège à éviter

Laravel Dusk **remplace temporairement `.env`** par `.env.dusk` pendant l’exécution, puis restaure `.env.backup`. Si un run est interrompu, `.env` peut rester en SQLite. Toujours vérifier après un run Dusk :

```bash
grep DB_CONNECTION .env
# → Doit afficher : mysql
```

Si `.env` pointe vers SQLite, restaurer :

```bash
cp .env.local .env
php artisan config:clear
```
