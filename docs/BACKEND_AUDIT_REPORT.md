# Rapport d'audit backend — 2026-10-03

Audit **lecture seule** (aucune migration, seed ou modification de données).  
Environnement analysé : dépôt `salang-mlm-ecommerce`, `.env` local dev, MySQL `salang_mlm` (2307 utilisateurs).

## Résumé exécutif

| | |
|---|---|
| **Statut global** | **⚠️ AVERTISSEMENTS** (plusieurs points **bloquants** avant mise en prod) |
| **Bloquants** | **6** |
| **Avertissements** | **12** |

La base contient des **données MLM réelles** (users, `commission_history`, `pv_history`, rangs), mais l’état **schéma / migrations / wallets** n’est pas aligné avec un déploiement « greenfield » Laravel. Les **tests PHPUnit (54/54)** passent sur SQLite en mémoire ; cela ne garantit pas la cohérence du schéma prod restauré.

---

## 1. Configuration

| Élément | Valeur observée | Statut |
|---------|-----------------|--------|
| APP_ENV | `local` | ⚠️ Passer à `production` en prod |
| APP_DEBUG | `true` | ⚠️ Passer à `false` en prod |
| APP_URL | `http://127.0.0.1:8000` | ⚠️ URL réelle HTTPS en prod |
| APP_KEY | défini (non vide) | ✅ |
| LOG_CHANNEL | `stack` | ✅ |
| LOG_LEVEL | `debug` | ⚠️ `info` ou `warning` en prod |
| DB_CONNECTION | `mysql` / `salang_mlm` | ✅ |
| QUEUE_CONNECTION | `database` | ⚠️ Worker + table `jobs` requis |
| CACHE_DRIVER | `database` | ⚠️ Tables `cache` / `cache_locks` présentes |
| SESSION_DRIVER | `database` | ⚠️ Table `sessions` requise |
| MAIL_MAILER | `log` | ⚠️ SMTP réel en prod |
| Paiements (.env) | Pas de clés `FLEXPAY_*` / `STRIPE_*` / `COINBASE_*` visibles | ⚠️ Secrets à configurer en prod |

**Config runtime (tinker)** : timezone `Africa/Kinshasa`, queue/cache/session en `database`, log `stack`.

**Problèmes** :
- Environnement clairement **dev**, pas prod.
- Intégrations paiement / webhooks **non configurées** dans `.env` actuel (tinker : FlexPay / Stripe / Coinbase = non).

---

## 2. Base de données

### 2.1 Volumes (MySQL `salang_mlm`)

| Table | Lignes | Statut |
|-------|--------|--------|
| users | 2307 | ✅ |
| migrations | 24 | ⚠️ Désaligné vs code (voir §3) |
| commission_history | 11854 | ✅ |
| pv_history | 3744 | ✅ |
| rank_history | 1662 | ✅ |
| products | 64 | ✅ |
| packages | 5 | ✅ |
| ranks | 10 | ✅ |
| notifications | 2 | ✅ |
| orders | 0 | ⚠️ |
| order_items | 0 | ⚠️ |
| commissions | 0 | ⚠️ |
| commission_periods | 0 | ⚠️ |
| commission_payments | 0 | ⚠️ |
| wallets | 0 | ❌ |
| transactions | 0 | ⚠️ |
| withdrawals | 0 | ⚠️ |
| genealogy | 0 | ⚠️ |

### 2.2 Intégrité

| Contrôle | Résultat | Statut |
|----------|----------|--------|
| Utilisateurs actifs | 2307 actifs | ✅ |
| Distribution rangs | Distributeur 1165, Cumul Directeur 803, … | ✅ (cohérent) |
| Wallets orphelins | 0 | ✅ |
| **Users sans wallet** | **2307** | ❌ |
| Commissions sans user | 0 | ✅ |
| Commissions sans période | 0 | ✅ |
| Foreign keys | 61 | ✅ |

**Commentaire** : table `wallets` **existe** mais **vide** — tous les membres n’ont pas de ligne wallet. Impact probable sur retraits, solde, API wallet, caisse.

Tables `orders` / `commissions` vides alors que `commission_history` est pleine : possible historique importé hors modèle Laravel actuel — à valider métier avant prod.

### 2.3 Index / PK

Non exécuté en détail (requête `information_schema` longue) ; 61 FK présentes — schéma relationnel partiellement contraint.

---

## 3. Migrations

| | |
|---|---|
| **Total fichiers migration (status)** | 41 |
| **Enregistrées en base** | 24 |
| **Pending (artisan)** | **17** |

**Pending (extrait)** :
- `create_commission_periods_table`, `create_commission_payments_table`, `create_notifications_table`, …
- `2026_10_03_*` (genealogy/monthly PV, Sanctum tokens, pv_history schema)
- `xxxx_xx_xx_add_flexpay_columns_to_transactions_table` (nom anormal)

**Risque** : restauration SQL a créé des tables **sans** enregistrer toutes les migrations → `php artisan migrate` en prod peut **échouer** (table exists) ou **appliquer des changements** non testés.

**Dernières migrations enregistrées en base** (extrait) : jusqu’à `2026_07_12_215516_add_mlm_columns_to_ranks_table`.

---

## 4. Routes

| Métrique | Valeur |
|----------|--------|
| Lignes `route:list` (approx.) | ~494 |
| Routes `api/*` | ~101 |
| Routes admin/cashier (grep) | ~290 |

**Webhooks** (POST, sans session utilisateur — normal) :
- `webhook/crypto`, `webhook/flexpay`, `webhook/mobile-money`, `webhook/payment`, `stripe/webhook`, `api/coinbase/webhook`

**CSRF** : exceptions webhook dans `bootstrap/app.php` ✅

**À faire manuellement** : revue ciblée des routes admin/cashier sans middleware `auth` / `admin` (liste trop longue pour grep automatique fiable). Recommandation : `php artisan route:list --path=admin` + revue middleware en staging.

---

## 5. Services critiques

| Service | Emplacement | Statut |
|---------|-------------|--------|
| AdvancedRankCalculator | `app/Services/MLM/AdvancedRankCalculator.php` | ✅ |
| TeamPVCalculator | `app/Services/MLM/TeamPVCalculator.php` | ✅ |
| RankUpdateService | `app/Services/MLM/RankUpdateService.php` | ✅ |
| PeriodCommissionCalculator | `app/Services/MLM/PeriodCommissionCalculator.php` | ✅ |
| RecalculateAfterPVImport | `app/Jobs/RecalculateAfterPVImport.php` | ✅ |
| RankService / RankCalculationService | Supprimés (audit docs/RANK_SERVICES_AUDIT.md) | ✅ |

**Commandes MLM** : `commissions:calculate-period`, `mlm:reset-monthly-pv`, `commissions:generate-payments`, `mlm:fix-team-pv`, etc. (~38 commandes custom).

**Note** : `commissions:remind` = stub (retour immédiat), pas de boucle — OK.

---

## 6. Scheduler

| Tâche | Fréquence | Notes |
|-------|-----------|--------|
| `mlm:reset-monthly-pv` | 8 du mois 00:00 | `withoutOverlapping(30)` ✅ |
| `commissions:calculate-period --full` | 8 du mois 00:05 | `withoutOverlapping(120)` ✅ |
| `commissions:generate-payments` | 15 du mois 00:00 | `withoutOverlapping(120)` ✅ |
| `mlm:fix-team-pv --dry-run` | hourly | `withoutOverlapping` ✅ |
| `mlm-ensure-current-period` | daily 00:30 | closure `Schedule::call` ✅ |

**Doublons** : aucun détecté dans `schedule:list`.

**Prod** : cron `* * * * * php artisan schedule:run` + **queue worker** (`QUEUE_CONNECTION=database`).

---

## 7. Observers & événements

| Observer | Modèle |
|----------|--------|
| UserObserver | User |
| OrderObserver | Order |
| GenealogyObserver | Genealogy |

Enregistrement dans `AppServiceProvider` ✅.

---

## 8. Sécurité

| Contrôle | Statut |
|----------|--------|
| Guards Dusk / migrate:fresh prod | ✅ (DuskTestCase, BlockMigrateFreshProvider) |
| Middleware aliases (`admin`, `api.auth`, `webhook.verify`, …) | ✅ `bootstrap/app.php` |
| Rate limiting | ✅ `RouteServiceProvider` (api, login, webhook, admin, …) |
| CSRF webhooks exempt | ✅ |
| `.env` dans `.gitignore` | ✅ (`.env*`) |
| Secrets webhooks en .env | ⚠️ non renseignés (dev) |

---

## 9. Tests

| Catégorie | Fichiers |
|-----------|----------|
| Feature | 11 |
| Unit | 5 |
| Browser (Dusk) | 7 |
| **PHPUnit exécuté** | **54/54** ✅ |

**Dusk** : non exécuté dans cet audit ; config Snap Chromium documentée (`docs/DUSK.md`, `--user-data-dir`).

Couverture MLM : tests présents mais sur SQLite `:memory:` — ne valide pas l’état MySQL restauré.

---

## 10. Dépôt & fichiers

| | |
|---|---|
| **Working tree** | Nombreuses modifications non commitées (backend, routes, tests) |
| **Fichiers sensibles** | `.env`, `.env.local`, `.env.production` présents localement, ignorés par git ✅ |
| **Storage link** | `public/storage` → présent (avatars, kyc, …) ✅ |
| **Parasytes racine `public/`** | Non audités exhaustivement ; pas de PHP hors `index.php` signalé |

---

## 11. Dépendances

| Outil | Version |
|-------|---------|
| Laravel | 13.18.0 |
| PHP | 8.5.x (CLI audit) |
| Node | v24.16.0 |
| Composer | 2.10.1 |

**Composer audit** : **28 advisories**, **7 packages** (dont phpseclib, autres — voir `composer audit`).

**npm audit** : échec (registry mirror `npmmirror` — endpoint non implémenté) ; relancer avec registry npm officiel avant prod.

---

## 12. Stockage & logs

| | |
|---|---|
| Espace disque `/home` | ~73 Go libres (61 % utilisé) ✅ |
| `storage/logs/` | **~5,3 Go** ⚠️ rotation / nettoyage urgent |
| Erreurs récentes | Connexions MySQL refusées (17:37) quand MySQL arrêté — attendu en dev |

---

## 13. Services externes (config actuelle)

| Service | Configuré |
|---------|-----------|
| FlexPay | ❌ |
| Stripe | ❌ |
| Coinbase | ❌ |
| Mail | `log` / `hello@example.com` ⚠️ |

Tables infra : `jobs`, `cache`, `cache_locks`, `sessions` ✅ ; **`personal_access_tokens`** absente de la liste — probablement liée à migration pending Sanctum ⚠️.

---

## 🚨 BLOQUANTS (avant production)

1. **Réconcilier migrations vs schéma restauré** (17 pending, risque d’échec ou DDL non contrôlé au deploy).
2. **Table `wallets` vide** — 2307 users sans wallet (fonctionnalités financières).
3. **`APP_DEBUG=true` + `APP_ENV=local`** — inacceptables en prod.
4. **Secrets paiement / webhooks** absents du `.env` dev (obligatoires si paiements actifs).
5. **Mail en mode `log`** — pas d’envoi réel.
6. **28 vulnérabilités Composer** — plan de mise à jour / exceptions documentées.

---

## ⚠️ AVERTISSEMENTS

1. `orders`, `commissions`, `commission_periods`, `transactions`, `withdrawals`, `genealogy` à **0** — valider par rapport au métier (données dans `commission_history` / `pv_history`).
2. Migration **`xxxx_xx_xx_add_flexpay_*`** — nom à corriger avant CI/prod.
3. **Queue + cache + session** en `database` — workers et monitoring requis.
4. **Logs 5,3 Go** — rotation (`daily`, logrotate, ou purge).
5. **Branch avec changements non commités** — figer une release/tag avant deploy.
6. **API Sanctum** : table tokens possiblement manquante jusqu’à migration.
7. **`LOG_LEVEL=debug`** en prod → volume et fuite d’info.
8. **npm audit** non concluant (miroir).
9. **Dusk non validé** en CI locale récente.
10. **Scheduler MLM** (8 et 15 du mois) — tester en staging avec `--dry-run` / backup.
11. **`commissions:remind`** — stub, pas de rappels réels.
12. Restaurer **`.env` prod** distinct (ne pas copier `.env` dev).

---

## ✅ POINTS VALIDÉS

1. **2307 users**, **11854 commission_history**, rangs cohérents.
2. **LOG_CHANNEL=stack** (pas `null`).
3. **Guards** anti-`migrate:fresh` sur `salang_mlm` + Dusk SQLite.
4. **Backups** scriptés (`scripts/backup-db.sh`, `docs/BACKUP.md`).
5. **Tests backend 54/54**.
6. **AdvancedRankCalculator** source unique grades (doublons supprimés).
7. **Scheduler** MLM documenté, `withoutOverlapping` sur commandes critiques.
8. **Rate limiting** et **CSRF webhook** configurés.
9. **Observers** User / Order / Genealogy actifs.
10. **Documentation** Dusk, déploiement, images, audit rangs.

---

## Plan d’action priorisé (avant déploiement)

### P0 — Bloquant
1. `./scripts/backup-db.sh` immédiatement avant toute opération DDL.
2. En staging : comparer `SHOW TABLES` vs `migrate:status` ; **marquer ou appliquer** les 17 migrations de façon contrôlée (pas de `migrate:fresh`).
3. Décider stratégie **wallets** (seed/import depuis legacy ou génération contrôlée) — **2307 lignes**.
4. Préparer `.env.production` : `APP_DEBUG=false`, `APP_ENV=production`, URL HTTPS, mail SMTP, secrets webhooks.
5. `composer audit` → mettre à jour packages ou documenter risques acceptés.

### P1 — Fortement recommandé
6. Worker queue + cron `schedule:run`.
7. Rotation logs / purge `storage/logs` (5,3 Go).
8. Migration Sanctum / `personal_access_tokens` si API mobile utilisée.
9. Renommer/supprimer migration `xxxx_xx_xx_*`.
10. Tag git release + déployer artefact commité.

### P2 — Post-lancement
11. Valider Dusk + smoke tests caisse/admin en staging.
12. Implémenter `commissions:remind` si métier requis.
13. PageSpeed / monitoring (Sentry, health `/up`).

---

*Rapport généré par audit automatisé + requêtes MySQL en lecture seule. Aucune modification de code ou de base effectuée during cet audit.*
