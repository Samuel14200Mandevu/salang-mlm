# Passe 2C — Performance · Front · Tests · Déploiement

**Date :** 2026-10-16  
**Projet :** Salang MLM (Laravel 13, MLM / caisse / paiements)  
**Statut :** **CLOSE** — axes A–E + plan de tests priorisé (2026-10-16)  
**Références closes :** `securite_passe2A.md`, `archi_bdd_passe2B.md` (BA12, BA14, BE1 confirmés)

---

## Préambule

### Documents de référence (2A / 2B)

| Document | Présence repo | Contenu retenu pour éviter les doublons |
|----------|---------------|----------------------------------------|
| `securite_passe2A.md` | ✅ | Sécurité : webhooks (C1/M2), middleware, CSRF, policies, validation inline, session **non chiffrée** (M7), dépendances CVE, routes exposées. **Ne pas re-auditer** ces points sauf impact perf (ex. middleware `active`). |
| `archi_bdd_passe2B.md` | ✅ **CLOSE** | Idempotence commission (BA12), clôture TX (BA14), job paiements (BE1), hub DI (BA11). **Ne pas dupliquer** l’inventaire migrations — renvoi 2B pour schéma. |

### Périmètre 2C (reste après 2A + 2B)

| Axe | Inclus 2C | Exclu (déjà 2A/2B) |
|-----|-----------|---------------------|
| **A. Performance** | N+1, pagination vs `->get()`, cache stats, OPcache / caches Laravel, Vite/bundle, session/queue drivers | Secrets, authZ, webhooks |
| **B. Front** | Blade/Livewire/Alpine, a11y, XSS `{!! !!}`, `@csrf`, assets | — |
| **C. Tests** | Couverture Unit/Feature/Dusk, factories, mocks, tests manquants métier | — |
| **D. Déploiement** | Laravel Cloud, health, maintenance, logs, storage, migrations/rollback | — |
| **E. Config prod vs dev** | `.env.example`, drivers, timezone (complète M7 2A côté ops) | Rotation secrets (2A) |

**Méthode :** constats avec **fichier:ligne** ; distinction **quick win** vs **refonte lourde**.

---

## A. PERFORMANCE — constats (2026-10-16)

### A.1 Synthèse exécutive

| Thème | Verdict rapide | Type |
|-------|----------------|------|
| Listes admin index (User/Order/Wallet/Commission) | **Paginées** (15–20) | ✅ OK |
| Exports / PDF / rapports admin | **`->get()` massif** fréquent | ⚠️ Refonte / chunk |
| Dashboard admin | **~20+ requêtes agrégées** par chargement, **sans** `Cache::remember` | ⚠️ Quick win cache |
| Middleware `active` | **`User::find` + 4× `Cache::forget`** à **chaque** requête authentifiée | ⚠️ Quick win |
| Services MLM | Chargements **ensemble** (`User::…->get()`, arbres) | ⚠️ Mix quick win / lourd |
| Cache métier stats | **`Cache::remember`** surtout `User.php`, SMS tokens ; **pas** dashboard | ⚠️ Quick win |
| Caches Laravel prod | `bootstrap/cache/` : **pas** de `config.php` / `routes-v7.php` versionnés ; doc checklist `config:cache` | ⚠️ Ops quick win |
| Vite | CSS **~649 kB** gzip **~91 kB** ; JS principal **~34 kB** + Chart **~202 kB** chunk | ⚠️ Quick win (split CSS admin) |
| Session / queue (exemple dev) | `.env.example:28-34` → `SESSION_DRIVER=database`, `QUEUE_CONNECTION=database` ; `config/queue.php:16` défaut env **`redis`** | ⚠️ Alignement env |

---

### A.2 Tableau findings — axe A uniquement

| # | Sévérité | Catégorie | Fichier:Ligne | Problème | Impact | Correction | Effort |
|---|----------|-----------|---------------|----------|--------|------------|--------|
| PA1 | MAJEUR | Middleware | `EnsureUserActive.php:37-47` | Requête SQL `User::find` + invalidation cache user/rank/descendants **à chaque hit** route `active` | Latence + charge DB/cache sur **toutes** pages membre/caisse/admin (sauf routes whitelist `:18-21`) | Rafraîchir seulement si TTL ou event ; ne pas `Cache::forget` systématiquement | Quick win |
| PA2 | MAJEUR | Dashboard | `Admin/DashboardController.php:24-98` | Bloc `index()` : multiples `count`/`sum`/`whereMonth` en boucle 6 mois (`:49-65`), sans `Cache::remember` malgré `use Cache` `:17` | TTFB admin dashboard élevé ; charge DB proportionnelle au trafic admin | `Cache::remember('admin.dashboard.stats', 60–300s, …)` + invalidation ciblée | Quick win |
| PA3 | MAJEUR | Pagination | `Admin/CommissionController.php:281,374,503` | Actions export / PDF : `$query->…->get()` sans limite (hors `index` paginé `:89`) | Mémoire / timeout sur gros volumes commissions | `chunk()` / cursor / filtres période obligatoires | Moyen |
| PA4 | MAJEUR | Pagination | `Admin/AdminOrderController.php:190` | `export()` : `$orders = $query->…->get()` | CSV commandes = table scan | Stream CSV + `chunkById` | Quick win |
| PA5 | MAJEUR | Pagination | `Admin/WalletController.php:205` | `export()` : `$wallets = $query->…->get()` | Idem portefeuilles | `chunk` | Quick win |
| PA6 | MAJEUR | Pagination | `Admin/AdminPVController.php:704` | `$users = $query->orderBy('pv_balance', 'desc')->get()` | Liste PV complète en mémoire | Paginate ou export async | Moyen |
| PA7 | MAJEUR | Pagination | `Admin/AdminCommissionHistoryController.php:72,124,560` | Commissions / PV history en `->get()` | Pages historique lourdes | Pagination + index période | Moyen |
| PA8 | MAJEUR | MLM service | `PeriodCommissionCalculator.php:43` | `$allUsers = User::where('is_active', true)->get()->keyBy('id')` | Calcul période = O(users) mémoire | Itération chunk + cache descendants (refonte calculateur) | Lourd |
| PA9 | MAJEUR | N+1 / requêtes | `MonthlyCommissionService.php:668` | `foreach (User::where('parrain_id', $user->id)->get() as $filleul)` dans boucle appelante | Multiplication requêtes filleuls | Eager load / une requête `whereIn` par niveau | Moyen |
| PA10 | MAJEUR | Rapports | `Admin/ReportController.php:1254-1257` | Arbre réseau : `User::…->whereIn('id', …)->get()` (descendants) | Explosion mémoire grands réseaux | Limite profondeur / pagination arbre / job async | Lourd |
| PA11 | MAJEUR | POS | `Admin/AdminPOSReportController.php:255` | `calculateTotalPV(Order::…->get())` | Charge toutes commandes POS completed | Agrégat SQL `sum` PV | Quick win |
| PA12 | MINEUR | N+1 | `Admin/AdminOrderController.php:47` | Filtre index : `User::select(…)->orderBy('name')->get()` pour dropdown | Requête users complète à chaque index orders | Cache 5 min ou autocomplete API | Quick win |
| PA13 | MINEUR | Index OK | `Admin/UserController.php:75` | Index users **paginate(15)** + `with` `:37` | — | Conserver ; optimiser **stats** `:77-88` (5 counts/page) | Quick win |
| PA14 | MINEUR | Index OK | `Admin/WalletController.php:46` | Index wallets **paginate(20)** | — | Idem stats `:49-52` | Quick win |
| PA15 | MINEUR | Assets | `vite.config.js:7-13` ; build `public/build/assets/app-*.css` **649 kB** | Entrées multiples CSS/JS ; Tailwind admin + member dans un gros `app.css` | FCP mobile / caisse | Split entries (admin vs member) ; `@vite` ciblé par layout | Moyen |
| PA16 | MINEUR | Assets | `vite.config.js:11` ; `auto-*.js` **202 kB** (Chart.js) | Chart chargé via bundle admin | Télécharger chart seulement pages graphiques | Dynamic import `charts.js` (déjà entrée séparée — vérifier imports) | Quick win |
| PA17 | MINEUR | Images | `resources/views/**` | `loading="lazy"` présent sur **~20** fichiers (pas systématique) | LCP shop/wallet | Étendre aux listes produits / carrousels | Quick win |
| PA18 | MINEUR | Cache Laravel | `bootstrap/cache/` | Seulement `packages.php`, `services.php` — **pas** `config.php` / routes cached en repo | Normal en dev ; prod doit `optimize` | `docs/PRODUCTION_CHECKLIST.md:15` — `config:cache route:cache view:cache` au deploy | Ops |
| PA19 | MINEUR | Session | `.env.example:28-29` ; `vendor/.../config/session.php:50` | `SESSION_DRIVER=database` | Sessions en table `sessions` = écritures DB par requête | Redis sessions en prod si trafic | Ops |
| PA20 | MINEUR | Queue | `.env.example:34` ; `config/queue.php:16` | Example **database** ; défaut config **redis** si env absent | Jobs MLM (`RecalculateAfterPVImport`, etc.) sur driver incohérent | Documenter + aligner Cloud (redis/database) | Ops |

---

### A.3 Pagination admin — preuve ciblée (Order, User, Wallet, Commission)

| Modèle | Index (liste) | Export / rapport sans pagination |
|--------|---------------|-----------------------------------|
| **User** | `UserController.php:75` `paginate(15)` | `UserController.php:906` `$query->get()` (export) |
| **Order** | `AdminOrderController.php:39` `paginate(20)` | `AdminOrderController.php:190` `->get()` |
| **Wallet** | `WalletController.php:46` `paginate(20)` | `WalletController.php:205` `->get()` |
| **Commission** | `CommissionController.php:89` `paginate(20)` | `CommissionController.php:281,374,503` `->get()` |

**Conclusion :** les **écrans index** principaux sont paginés ; le risque perf est sur **exports, PDF, calculs MLM et dashboard**.

---

### A.4 Cache — usages observés (`grep Cache::` dans `app/`)

| Pattern | Exemples | Gap |
|---------|----------|-----|
| `Cache::remember` | `User.php:436,763` ; `SmsService.php:170,244,318` | Pas de cache dashboard / rapports admin |
| `Cache::put/get` | Settings admin, rate limit, maintenance, activation | OK fonctionnel |
| `Cache::forget` agressif | `EnsureUserActive.php:44-47` | Voir PA1 |

---

### A.5 N+1 / eager loading (échantillon)

| Zone | Preuve | Note |
|------|--------|------|
| Admin index | `UserController.php:37` `with([...])` | Bon pattern |
| MLM | `GlobalCommissionReportService.php:177` `User::…->with('parrain')->get()` | OK sur ce chemin |
| MLM | `MonthlyCommissionService.php:668` | Requête filleuls **par** user dans helper — PA9 |
| Services MLM | Peu de `->with()` (`grep` ~2 fichiers) vs nombreux `->get()` | Revue ciblée recommandée (axe A suite ou outil debugbar) |

---

### A.6 Assets (build local 2026-10-16)

```
public/build/assets/app-*.css     ~649 kB (gzip ~91 kB)
public/build/assets/app-*.js      ~34 kB  (gzip ~10 kB)
public/build/assets/auto-*.js     ~202 kB (gzip ~69 kB)  # Chart
public/build/assets/module.esm-*  ~110 kB (gzip ~38 kB)  # Alpine/Livewire deps
```

Entrées Vite : `vite.config.js:7-13` (`app.css`, `app.js`, `cashier.js`, `charts.js`, `admin-registrations-chart.js`).

---

### A.7 Session & queue (config)

| Source | Session | Queue |
|--------|---------|-------|
| `.env.example:28-29,34` | `SESSION_DRIVER=database` | `QUEUE_CONNECTION=database` |
| `config/queue.php:16` | — | `env('QUEUE_CONNECTION', 'redis')` |
| Runtime audité (session précédente 2A) | `encrypt=false`, `same_site=lax` | — |

OPcache : **non configuré dans le repo** (PHP-FPM / Laravel Cloud) — hors périmètre code ; s’appuyer sur `docs/DEPLOYMENT.md:25` et checklist prod.

---

---

## B. FRONT — Blade, Livewire, CSRF, XSS

### B.1 Livewire

| Constats | Preuve |
|----------|--------|
| **Aucun composant applicatif** | `app/Livewire/` vide (0 fichier) |
| Vendor présent | `public/vendor/livewire/` ; listeners dans `admin/layouts/app.blade.php:786-790` |
| Pas de `wire:` dans les vues métier | Grep `wire:` → uniquement JS vendor + listeners layout |

**Verdict :** stack **Blade + Alpine/Vite** ; Livewire = dépendance transitive (layout admin), **pas** de surface Livewire custom à auditer séparément.

### B.2 Blade >500 lignes (maintenance / perf rendu)

| Lignes | Fichier |
|--------|---------|
| 1657 | `resources/views/admin/pv/index.blade.php` |
| 1197 | `resources/views/cashier/checkout.blade.php` |
| 1112 | `resources/views/admin/pv/commission-history.blade.php` |
| 978 | `resources/views/cashier/pos.blade.php` |
| 904 | `resources/views/admin/layouts/app.blade.php` |

**Impact :** temps compilation views, diff reviews, risque XSS/CSRF oublié dans sous-blocs.

### B.3 `@csrf` sur formulaires POST

| Métrique | Valeur |
|----------|--------|
| `method="POST"` dans `resources/views` | ~80 fichiers (1–6 forms/fichier) |
| `@csrf` présent | ~79 fichiers — **alignement global bon** |
| Exemple caisse critique | `cashier/checkout.blade.php:599-600` — POST + `@csrf` |

**Finding PB1 (MINEUR) :** revue manuelle recommandée sur partials AJAX (fetch) hors `@csrf` — hors grep Blade ; API Sanctum `routes/api.php` (2A).

### B.4 `{!! !!}` — XSS

| # | Sévérité | Fichier:Ligne | Problème | Correction |
|---|----------|---------------|----------|------------|
| PB2 | MAJEUR | `network/index.blade.php:107` | `{!! $controller->renderGenealogyTree(...) !!}` — HTML arbre non échappé | Échapper nœuds user-controlled ; CSP |
| PB3 | MAJEUR | `admin/reports/user_network.blade.php:333` | `{!! renderTreeNodes(...) !!}` | Idem |
| PB4 | MAJEUR | `partials/auth/page-header.blade.php:8` | `{!! $lead !!}` | Passer par `e()` sauf HTML admin trusted |
| PB5 | MINEUR | `admin/pv/import.blade.php:426,434` | `{!! nl2br(e(session(...))) !!}` | Pattern **safe** (e + nl2br) |
| PB6 | MINEUR | PwaKit / QrCode | `admin/layouts/app.blade.php:10,623` ; QR views | Trust vendor ; pas de input user |

---

## C. TESTS — couverture par domaine

### C.1 Inventaire (2026-10-16)

| Domaine | Fichiers `*Test.php` | Exemples |
|---------|----------------------|----------|
| **Unit** | 14 | 12 × `Unit/MLM/*`, Support |
| **Feature** | 13 | Auth (7), `Feature/MLM/CommissionCalculationTest.php`, API commission |
| **Browser (Dusk)** | 5 | Login, Wallet, Commission, Purchase |
| **Total** | **32** classes test | ~38 fichiers PHP sous `tests/` |

### C.2 Couverture métier (synthèse)

| Domaine | Couvert | Lacunes (2B / 2A) |
|---------|---------|-------------------|
| Rang MLM | ✅ `AdvancedRankCalculatorTest`, `RankConditionChecker*` | — |
| Calendrier période | ✅ `MlmPeriodTest`, `CommissionPeriodCalendarTest` | — |
| Commission temps réel | ⚠️ `CommissionCalculationTest` (PV history) | **Pas** idempotence BA12 |
| Clôture mensuelle | ❌ | **BA14** — 0 test `ProcessMonthlyCommissions` |
| Webhook Stripe | ❌ | **C1** 2A — 0 test signature |
| Retrait / wallet | ❌ Feature dédiée | **M6** policies ; double-spend |
| Caisse | ⚠️ Dusk Purchase | Pas Feature `CashierController` |
| Admin PV import | ❌ | Volume code `AdminPVImportController` |

### C.3 Findings tests

| # | Sévérité | Problème | Preuve |
|---|----------|----------|--------|
| PC1 | CRITIQUE | 6 scénarios financiers **sans** test automatisé | Voir plan § Livrables |
| PC2 | MAJEUR | Dusk 5 tests — smoke only | `.github/workflows/dusk.yml:56` curl `/up` |
| PC3 | MINEUR | `GenealogyServiceTest` entretient code mort (BA13) | `tests/Unit/MLM/GenealogyServiceTest.php` |

---

## D. DÉPLOIEMENT — migrations, rollback, health

| # | Sévérité | Sujet | Fichier:Ligne / doc | Constats | Action |
|---|----------|-------|---------------------|----------|--------|
| PD1 | OK | Health check | `bootstrap/app.php:38` `health: '/up'` | Utilisé CI Dusk `:56` | Monitor prod `/up` |
| PD2 | MAJEUR | Migrations prod | `docs/DEPLOYMENT.md:7-8,24` | **`migrate --force` only** ; interdit `fresh`/`refresh` | Cloud deploy command |
| PD3 | MAJEUR | Rollback | `docs/DEPLOYMENT.md:16-17` | `migrate:rollback --step=1` + backup | Runbook avant deploy risqué |
| PD4 | MAJEUR | Ordre migrations | `docs/MIGRATIONS_RECONCILIATION.md` | Migrations conditionnelles `hasColumn` ; FK tardives `2026_07_13_082321_*` | `migrate:status` prod vs local |
| PD5 | MINEUR | Maintenance | `APP_MAINTENANCE_DRIVER=file` `.env.example:11` | File driver OK Cloud | — |
| PD6 | MINEUR | Logs Cloud | `bootstrap/app.php:18` `LOG_STREAM_PATH=/tmp/...` | Adapté Laravel Cloud | — |
| PD7 | MINEUR | Build front | Routine 2B/2C | `npm run build` avant merge `main` | Vite assets |

**Rollback :** pas de rollback auto Cloud documenté dans le repo — procédure **manuelle** (revert commit + `rollback --step=1` si migration réversible).

---

## E. CONFIG prod vs dev

| Variable / thème | `.env.example` | Risque | Réf. |
|------------------|----------------|--------|------|
| DB creds en clair | `:23-25` `DB_USERNAME` / `DB_PASSWORD` | **MAJEUR** (2A M3) | Rotation + secrets Cloud |
| `SESSION_DRIVER` | `:28` `database` | Perf (PA19) | Redis prod si charge |
| `QUEUE_CONNECTION` | `:34` `database` | Incohérence `config/queue.php:16` défaut `redis` | Aligner Cloud workers |
| `SESSION_ENCRYPT` | `:30` `false` | 2A M7 | `true` si données session sensibles |
| Caches deploy | — | Pas `config:cache` en repo | `docs/PRODUCTION_CHECKLIST.md` PA18 |
| Stripe webhook | absent `.env.example` | C1 CRITIQUE | `STRIPE_WEBHOOK_SECRET` + `cashier.php:50` |
| `.env.production` | gitignore (2A C6) | OK non versionné | Ne pas committer |

| # | Sévérité | Finding |
|---|----------|---------|
| PE1 | CRITIQUE | Documenter + imposer `STRIPE_WEBHOOK_SECRET` prod (C1) |
| PE2 | MAJEUR | Harmoniser queue driver example vs Cloud |
| PE3 | MINEUR | Retirer creds réels de `.env.example` |

---

## Livrables finaux 2C

### Tableau global findings (axes A–E) — Top perf/sécu/ops

| # | Sévérité | Axe | Résumé |
|---|----------|-----|--------|
| PA1 | MAJEUR | Perf | Middleware `EnsureUserActive` — SQL + cache forget |
| PA8 | MAJEUR | Perf | `PeriodCommissionCalculator.php:43` — all users en RAM |
| PB2–PB4 | MAJEUR | Front | `{!! !!}` arbre réseau / lead |
| PC1 | CRITIQUE | Tests | 6 scénarios financiers manquants |
| PD2–PD4 | MAJEUR | Deploy | Migrations / rollback / ordre |
| PE1 | CRITIQUE | Config | Stripe webhook secret (lien C1) |

**MLM financier (2B, confirmé par extraits code) :** BA12, BA14, BE1 — **tests prioritaires ci-dessous**, pas re-audit ici.

### Matrice conformité 2C

| Axe | Verdict | Quick wins |
|-----|---------|------------|
| A Performance | ⚠️ | Cache dashboard PA2 ; exports chunk PA3–PA5 |
| B Front | ⚠️ | Réduire mega-blades ; durcir `{!! !!}` PB2–PB4 |
| C Tests | ❌ | Plan 6 tests P0 |
| D Deploy | ⚠️ | `/up` + migrate force + doc rollback |
| E Config | ⚠️ | Stripe + queue + `.env.example` |

---

## Plan de tests priorisé (6 scénarios P0)

Ordre d’implémentation recommandé — chaque test doit **citer la finding 2A/2B** et échouer sur le comportement actuel jusqu’à fix produit.

| P | Scénario | Finding | Type | Spec (comportement attendu) | Ancrage code |
|---|----------|---------|------|-------------------------------|--------------|
| **P0-1** | **Idempotence commission (BA12)** | BA12 | Feature | Appeler 2× `CommissionDistributor::distributeCommissions` (même order/item) → **1 seule** ligne `commissions` type `cash_pos` par `order_id` ; **1** ligne `pv_history` par order/item | `CommissionDistributor.php:417-444`, `:254-271`, `:311-321` ; pas de unique `commissions` `(order_id,type)` — `2026_06_22_000006_create_commissions_table.php:15-25` |
| **P0-2** | **Clôture période transactionnelle (BA14)** | BA14 | Feature/Integration | Simuler échec `generatePayments` après commissions OK → statut période **cohérent** (pas « paid » partiel) ; documenter rollback attendu | `MonthlyCommissionService.php:92-94` ; job sans TX globale `ProcessMonthlyCommissions.php:40-110` |
| **P0-3** | **Webhook Stripe signature (C1)** | C1 | Feature | POST `/stripe/webhook` sans header `Stripe-Signature` → **401/403** quand secret configuré ; avec secret null → comportement documenté Cashier | `vendor/laravel/cashier/.../WebhookController.php:27-31` ; 2A C1 |
| **P0-4** | **IDOR withdrawal (M6)** | M6 | Feature | User A ne peut pas `show`/`cancel` retrait user B ; admin `approve` exige rôle admin (middleware) | `WithdrawalController.php:163-168` (OK membre) ; `Admin/WithdrawalController.php:96-98` `findOrFail` sans policy — 2A P1.6 |
| **P0-5** | **Double-spending wallet** | — | Feature | 2 requêtes concurrentes retrait même solde → une seule succès ; solde ≥ 0 | `WithdrawalController.php:106-141` TX locale |
| **P0-6** | **Échec job → alerte admin (BE1)** | BE1 | Unit/Feature | Si `generatePayments` retourne `false`, job **doit** throw ou marquer échec — **ne doit pas** loguer « completed » seul | `ProcessMonthlyCommissions.php:107-110` ; `failed()` `:172-194` **non** appelé si pas d’exception |

### P1 (après P0)

- Export admin chunk (PA3–PA5) — test perf smoke timeout.
- Dusk caisse checkout `@csrf` (`checkout.blade.php:599-600`).
- `PaymentEligibilityChecker` + clôture PV (`MlmPeriodCloseService`) — 2B BA6.

---

**STOP passe 2C** — en attente validation **passe 3 (fusion)**.  
*Ne pas modifier `archi_bdd_passe2B.md` ni `securite_passe2A.md`.*
