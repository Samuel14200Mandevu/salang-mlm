# Routine fin de journée — livrer le local en production

Quand une journée de travail est **correcte en local** (code + `php artisan migrate` sur MySQL dev + `npm run build`), suivre **toujours** cette séquence pour que la prod reflète le même comportement.

La prod **ne reçoit pas** une copie de votre base locale : on livre le **code** et les **migrations** ; les utilisateurs et données MySQL prod restent en place.

## 1. Vérifier en local

```bash
grep DB_CONNECTION .env          # doit être mysql (salang_mlm), pas dusk
php artisan migrate              # sans --force en dev ; idempotent OK
npm run build
php artisan test                 # si vous touchez la logique métier
```

Optionnel (avant grosse migration) :

```bash
./scripts/backup-db.sh           # backup MySQL prod avant schéma (voir DEPLOYMENT.md)
```

## 2. Versionner et pousser

```bash
git status
git add …
git commit -m "…"
git push origin <branche>
```

Travail sur une branche feature → **PR vers `main`**. Attendre le check **Dusk Tests** vert sur GitHub.

## 3. Fusionner dans `main`

- Sur GitHub : merger la PR, **ou**
- `gh pr merge <numéro> --merge`

Le push sur `main` déclenche le **déploiement Laravel Cloud** (push-to-deploy).

| Ressource | Valeur |
|-----------|--------|
| App Cloud | `salang-mlm` |
| Environnement | `production` (`env-a227ad2f-d5cb-4387-b31e-d062e951a9fe`) |
| URL | https://salang-group-mlm.laravel.cloud |
| Branche Git Cloud | `main` |
| Build | `composer install --no-dev` + `npm ci` + `npm run build` |
| Deploy | `php artisan migrate --force` (configuré sur l’env) |

## 4. Contrôler le déploiement

```bash
cloud deployment:list env-a227ad2f-d5cb-4387-b31e-d062e951a9fe -n --json | head
```

Statut attendu : `deployment.succeeded` sur le commit de merge.

Si les migrations n’ont **pas** tourné (deploy command vide ou erreur) :

```bash
cloud command:run env-a227ad2f-d5cb-4387-b31e-d062e951a9fe -n --cmd="php artisan migrate --force"
```

Seed **uniquement** si la journée a ajouté des rôles / données de référence idempotentes (ex. nouveau rôle Spatie) :

```bash
cloud command:run env-a227ad2f-d5cb-4387-b31e-d062e951a9fe -n --cmd="php artisan db:seed --class=RoleSeeder --force"
```

**Jamais** en prod : `migrate:fresh`, `migrate:refresh`, `db:wipe`, ni import de `database/dusk.sqlite`.

## 5. Aligner le poste de travail

```bash
git checkout main
git pull origin main
```

## 6. Smoke test production

- Connexion → redirection **`/services`**
- Parcours touché dans la journée (admin, boutique, wallet, etc.)

## Rappels

- **Local correct** = code + migrations testées sur MySQL dev, pas une dump SQLite vers prod.
- **Dusk** = CI SQLite uniquement ; voir [DUSK.md](./DUSK.md).
- Détail prod : [DEPLOYMENT.md](./DEPLOYMENT.md), [PRODUCTION_CHECKLIST.md](./PRODUCTION_CHECKLIST.md).

## Phrase type pour l’assistant Cursor

> « Livre la journée en prod » → enchaîner commit/push, PR + Dusk vert, merge `main`, vérifier Cloud + migrate si besoin, `git pull main`, smoke test.
