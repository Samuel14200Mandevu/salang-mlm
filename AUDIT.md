# Audit technique — Salang MLM E-commerce

**Date :** 2026-10-17  
**Versions :** Laravel 13.18.0, PHP 8.3 (cible `composer.json:9`), MySQL  
**Périmètre :** application complète (MLM, caisse, paiements, admin)  
**Méthodologie :** 3 passes indépendantes — sécurité (`securite_passe2A.md`), architecture/BDD/jobs (`archi_bdd_passe2B.md`), performance/front/tests/déploiement (`perf_tests_deploy_passe2C.md`)

---

## 1. Résumé exécutif

### Notes par domaine (/10)

| Domaine | Note | Critères (synthèse) |
|---------|------|---------------------|
| **Robustesse financière** | **2/10** | **BA12**, **BA14**, **BE1** — triple risque fraude / perte commissions (`CommissionDistributor.php:417-444` ; `MonthlyCommissionService.php:92-94` ; `ProcessMonthlyCommissions.php:107-110`). |
| **Sécurité** | **4/10** | 3 findings **CRITIQUE** runtime (C1 webhook Stripe, C3 Guzzle CVE high, C4 Dompdf CVE) ; **2 policies** sur modèles sensibles (`app/Policies/` — 2A M6) ; session **non chiffrée** (`vendor/laravel/framework/config/session.php:50`, `.env.example:30`) ; webhooks app contournables hors prod (`VerifyWebhookSignature.php:22`). |
| **Architecture** | **5/10** | Graphe DI MLM sain (**BA11**, hub `TeamPVCalculator`) ; mais **3 CRITIQUE** argent (BA12 idempotence, BA14 clôture, BE1 job) + **BB1** god controller caisse (~2989 L) ; ~40 % services MLM sans tests unitaires (2B). |
| **Tests** | **3/10** | **32** classes test vs **82** contrôleurs ; **6 scénarios financiers P0** non couverts (2C PC1) ; Dusk = smoke (`tests/Browser/`, 5 fichiers). |
| **Déploiement** | **6/10** | Health `/up` (`bootstrap/app.php:38`) ; doc migrate/rollback (`docs/DEPLOYMENT.md:7-24`) ; gaps `.env.example` (Stripe, webhooks — 2A M3, 2C PE1) ; pas de rollback auto Cloud. |

**Note globale indicative : ~4,5/10** — produit riche et structuré côté MLM, mais **exposition financière et sécurité runtime** non acceptable pour une prod « argent réel » sans Sprint 0.

**Le MLM fonctionne mais l'argent est à risque** — 3 CRITIQUE financiers (**BA12**, **BA14**, **BE1**) doivent être corrigés avant toute mise en prod.

### Cinq phrases — état du projet

1. Salang MLM est une application Laravel mature (caisse, wallet, commissions, grades) avec une **séparation claire** temps réel / fin de période documentée dans le code commission.  
2. Les **fondations MLM** (calcul team PV, conditions de rang, calendrier période 8→7) sont testées partiellement et **sans cycle de dépendances** entre services.  
3. En revanche, les **flux qui touchent l’argent** (commission par commande, clôture mensuelle, webhooks) manquent de **garde-fous transactionnels** et de tests automatisés.  
4. La **surface sécurité** expose des CVE Composer prioritaires et un webhook Stripe Cashier **non signé** si le secret n’est pas configuré.  
5. Le déploiement Laravel Cloud est **documenté** ; le risque principal est **configuration** (secrets, queues) plus que l’absence de health check.

### Trois risques business immédiats (langage simple)

1. **Payer deux fois la même commission** — si une commande est retraitée (bug, double clic, rejeu manuel), rien dans la base ne l’empêche formellement (`CommissionDistributor.php:417-444`, pas d’unicité `order_id` + `type` — `2026_06_22_000006_create_commissions_table.php:15-25`).  
2. **Clôture de mois incohérente** — la fin de mois MLM peut laisser des commissions calculées sans paiement correspondant, sans alerte claire (`ProcessMonthlyCommissions.php:107-110`).  
3. **Fraude ou erreur sur les paiements en ligne** — webhook Stripe ou bibliothèques HTTP/PDF obsolètes peuvent compromettre l’intégrité des encaissements ou des documents (C1, C3, C4).

### Trois points forts

1. **Architecture DI MLM** — pas de boucle de services ; hub `TeamPVCalculator` partagé proprement (**BA11**, `AdvancedRankCalculator.php:17-21`, `MlmPeriodCloseService.php:18`).  
2. **Middleware admin** — contrôle d’accès admin explicite (`AdminMiddleware.php:15-62`, 2A matrice).  
3. **Listes admin paginées** — index User/Order/Wallet/Commission en `paginate()` (2C PA13–PA14, ex. `UserController.php:75`).

### Recommandation — quoi corriger **avant** toute mise en prod « argent réel »

Exécuter le **Sprint 0** (§4, **7 items**) : idempotence commission (BA12), secrets + signature Stripe (C1), mises à jour **Guzzle** et **Dompdf** (C3, C4), échec explicite du job mensuel (BE1), filet caisse (BB1), nettoyage **GenealogyService** (BA13), puis vérifier `composer audit` et `/up` en prod. Voir **Go/No-Go Production** ci-dessous.

### Go/No-Go Production — Checklist obligatoire

- [ ] Sprint 0 (**7 items**) complété et testé  
- [ ] `composer audit` = 0 advisory **CRITIQUE/MAJEUR** runtime  
- [ ] `STRIPE_WEBHOOK_SECRET` configuré et vérifié (tinker)  
- [ ] Test idempotence commission (P0-1) passe en CI  
- [ ] Test clôture période transactionnelle (P0-2) passe en CI  
- [ ] Test échec job → throw (P0-3) passe en CI  
- [ ] 3 tests Feature `CashierController` (checkout / createOrder / cancel)  
- [ ] `.env.example` sans credentials réalistes  
- [ ] Session chiffrée + cookie secure + SameSite=strict en prod  
- [ ] Health check `/up` répond 200  
- [ ] Rollback documenté et testé sur staging  

---

## 2. Top 10 findings CRITIQUE (fusion 2A + 2B + 2C)

Tri par **impact business** décroissant.

| # | Finding | Fichier:Ligne | Impact business | Effort | Source |
|---|---------|---------------|-----------------|--------|--------|
| 1 | **Double crédit commission / PV par commande (BA12)** | `CommissionDistributor.php:417-444` ; `CommissionDistributor.php:254-271` ; `2026_06_22_000004_create_orders_table.php:7-23` ; `2026_06_22_000006_create_commissions_table.php:15-25` | Surpaiement parrains / PV gonflés → commissions et grades faussés | **M** (migration + garde + test) | 2B |
| 2 | **Clôture mensuelle sans transaction globale (BA14)** | `MonthlyCommissionService.php:92-94` ; `ProcessMonthlyCommissions.php:83-107` | État partiel (PV clôturés, soldes incohérents) après incident | **M–L** | 2B |
| 3 | **Échec paiements clôture silencieux (BE1)** | `ProcessMonthlyCommissions.php:107-110` ; `failed()` `:172-194` (non appelé si `false` sans exception) | Commissions « calculées » sans virement wallet ; équipe croit la clôture OK | **S** | 2B |
| 4 | **Webhook Stripe Cashier non signé (C1)** | `vendor/laravel/cashier/config/cashier.php:50` ; route `stripe/webhook` middleware `[]` (2A) ; secret null → pas de vérif signature | Forge événements Stripe / abonnements Cashier | **S** (config + test) | 2A |
| 5 | **Guzzle 7.13.1 — CVE high (C3)** | `composer audit` ; `guzzlehttp/guzzle` &lt; 7.15.2 (`CVE-2026-69246`) | Compromission des appels HTTP (paiements, APIs) | **S** | 2A |
| 6 | **Dompdf 3.1.5 — CVE (C4)** | `composer audit` ; `dompdf/dompdf` &lt; 3.1.6 | Fuite fichiers / DoS via PDF admin/caisse | **S** | 2A |
| 7 | **God controller caisse (BB1)** | `app/Http/Controllers/Cashier/CashierController.php` (~2989 L, 2B B.1) | Régression caisse = perte ventes / commissions POS ; diff reviews impossibles | **L** | 2B |
| 8 | **Webhooks wallet contournables hors prod (M2)** | `VerifyWebhookSignature.php:15-22` ; `WebhookController.php:464-467` | Crédit wallet forgé en staging / dev si secrets absents | **S–M** | 2A |
| 9 | **Compte inactif peut payer (M1)** | `EnsureUserActive.php:65-75` ; `routes/web.php:157-299` | Encaissements sans activation MLM contractuelle | **M** | 2A |
| 10 | **6 scénarios financiers P0 sans tests (PC1)** | Plan 2C P0-1…P0-6 ; ex. `CommissionCalculationTest.php` sans idempotence | Régressions argent non détectées en CI | **M** | 2C |

*Effort : S = quelques heures, M = 1–3 j, L = &gt; 1 sem.*

---

## 3. Findings par domaine

### 3.1 Sécurité (résumé 2A)

**CRITIQUES** — détail §2 : **C1**, **C3**, **C4**.

**MAJEURS (tableau court)**

| ID | Fichier:Ligne | Résumé |
|----|---------------|--------|
| M1 | `EnsureUserActive.php:65-75` | Compte inactif : accès non bloqué au groupe `auth`+`active` |
| M2 | `VerifyWebhookSignature.php:15-22` | Bypass signature si env ≠ production et secret vide |
| M3 | `.env.example:1-80` | Secrets Stripe/webhooks absents ; `APP_DEBUG=true` `:4` |
| M4 | `WithdrawalRequest.php:8` ; `WithdrawalController.php:65` | FormRequest retrait non utilisé |
| M5 | `PaymentController.php:134` ; `WalletController.php:146` | Validation inline paiement/wallet |
| M6 | `app/Policies/` (2 fichiers) | Pas de policy User/Wallet/Withdrawal/Order/Commission |
| M7 | `session.php:50,172,202` ; `.env.example:30-31` | Session non chiffrée ; cookie secure null |
| M11 | `ConsultationController.php:148-183` ; `CashierController.php:48-50` | Consultations sans `cashier.staff` ; encaissement bloqué 403 |

**MINEURS notables :** N1 API login sans throttle ; N2 routes payment success/cancel publiques ; N4 `.env.production` local avec secrets (non versionné — OK git).

**Matrice de conformité (2A)**

| Pratique | Statut |
|----------|--------|
| Webhooks signés Stripe Cashier | ❌ C1 |
| Webhooks signés app (Flexpay/crypto/payment) | ⚠️ M2 |
| Middleware admin | ✅ |
| Middleware cashier staff | ⚠️ M11 |
| Policies modèles sensibles | ❌ 0/7 |
| FormRequests | ❌ ~8/82 |
| Session chiffrée / cookie strict | ❌ M7 |
| `composer audit` clean | ❌ C3, C4 (+ M8, M9) |

---

### 3.2 Architecture / BDD / Jobs (résumé 2B)

**CRITIQUES** — §2 : **BA12**, **BA14**, **BE1**, **BB1** (+ **BA2/BA4/BA5** qualité/tests liés à l’argent).

**MAJEURS**

| ID | Fichier:Ligne | Résumé |
|----|---------------|--------|
| BA2 | `MonthlyCommissionService.php` (672 L) | Orchestrateur clôture sans tests |
| BA4 | `PeriodCommissionCalculator.php:38-43` | Bonus fin de période, 0 test ; charge tous users `:43` |
| BA5 | `CommissionDistributor.php:187-295` | Temps réel argent/PV, 0 test |
| BA6 | `MlmPeriodCloseService.php:37+` | Clôture PV pending, 0 test |
| BA7 | `PvManagementService.php:32+` | Allocations PV ventes, 0 test |
| BA13 | `GenealogyService.php:11` | ~477 L, 0 appelant prod |
| BB2 | `AdminPVImportController.php` (~1053 L) | Import PV monolithique |
| BB3 | `Admin/UserController.php` (~1106 L) | Admin users + MLM inline |
| BE2 | `ProcessMonthlyCommissions.php:68-74` | Double dispatch job possible |
| BC1 | `User.php:26-42` | Champs MLM/argent en `$fillable` |

**MINEURS :** BA1 `RankConditionChecker.php` (688 L, testé) ; BA8 taille `AdvancedRankCalculator` / `TeamPVCalculator` ; BA9 `MonthlyCommissionService.php:92` `app()` ; BA10 `PeriodCommissionCalculator.php:43` OOM.

**Point fort — BA11 :** aucun cycle DI dans `app/Services/MLM/` ; hub `TeamPVCalculator` (`AdvancedRankCalculator.php:17-21`, `RecalculateAfterPVImport.php:37-39`, etc.).

---

### 3.3 Performance / Front / Tests / Deploy (résumé 2C)

**CRITIQUES**

| ID | Résumé | Fichier:Ligne |
|----|--------|---------------|
| PC1 | Plan 6 tests P0 non implémentés | `perf_tests_deploy_passe2C.md` § Plan P0 |
| PE1 | `STRIPE_WEBHOOK_SECRET` absent de `.env.example` | `.env.example` ; lien C1 |

**MAJEURS**

| ID | Résumé | Fichier:Ligne |
|----|--------|---------------|
| PA1 | Middleware `active` : SQL + cache forget chaque requête | `EnsureUserActive.php:37-47` |
| PA2 | Dashboard admin sans cache stats | `Admin/DashboardController.php:24-98` |
| PA3–PA7 | Exports/PDF `->get()` massifs | ex. `CommissionController.php:281` |
| PA8 | Calculateur période charge tous users | `PeriodCommissionCalculator.php:43` |
| PB2–PB4 | XSS `{!! !!}` arbre réseau / lead | `network/index.blade.php:107` ; `admin/reports/user_network.blade.php:333` |
| PD2–PD4 | Migrations prod / rollback / ordre | `docs/DEPLOYMENT.md:16-24` ; `docs/MIGRATIONS_RECONCILIATION.md` |
| PE2 | Queue example `database` vs config défaut `redis` | `.env.example:34` ; `config/queue.php:16` |

**MINEURS :** PB1 CSRF globalement OK (`cashier/checkout.blade.php:599-600`) ; PA15–PA20 assets/session ; PD5–PD7 ops ; PC3 tests code mort Genealogy.

**Matrice conformité 2C**

| Axe | Verdict |
|-----|---------|
| Perf listes admin | ✅ paginées |
| Perf exports / MLM batch | ⚠️ |
| Front CSRF | ✅ |
| Front XSS `{!! !!}` | ⚠️ |
| Tests métier argent | ❌ |
| Health deploy | ✅ `/up` |
| Config prod documentée | ⚠️ |

---

## 4. Plan d'action priorisé

### Sprint 0 — « À faire avant la prochaine mise en prod » (&lt; 1 jour)

| # | Action | PR / commandes | Finding |
|---|--------|----------------|---------|
| 1 | **Idempotence commission** : migration `orders.commissions_processed_at` (nullable → set after success) + index unique partiel `commissions(order_id, type)` où `order_id` not null + garde en tête de `distributeCommissionsForOrder` | Branche `fix/ba12-commission-idempotence` ; `php artisan make:migration ...` ; tests Feature P0-1 | BA12 |
| 2 | **Stripe webhook** : définir `STRIPE_WEBHOOK_SECRET` sur Laravel Cloud ; documenter dans `.env.example` (fichier hors Sprint 0 si politique « no edit » — **obligatoire en Cloud**) ; `php artisan tinker --execute="echo config('cashier.webhook.secret') ? 'ok' : 'null';"` | Config env Cloud + PR doc/test Feature POST `/stripe/webhook` sans signature → 401/403 | C1 |
| 3 | **Dépendances CVE** : `composer update guzzlehttp/guzzle --with-all-dependencies` (≥ 7.15.2) ; `composer update dompdf/dompdf` (≥ 3.1.6) ; `composer audit` doit passer | PR `chore/security-composer-audit` | C3, C4 |
| 4 | **Job clôture** : si `generatePayments` retourne `false`, `throw new RuntimeException(...)` dans `ProcessMonthlyCommissions.php` après `:107` | PR `fix/be1-monthly-job-failure` | BE1 |
| 5 | **Smoke post-deploy** : `curl -sf https://salang-group-mlm.laravel.cloud/up` ; login → `/services` ; `php artisan migrate:status` (Cloud : `migrate --force` déjà deploy) | Checklist manuelle + CI | PD1 |
| 6 | **`CashierController` (~2989 L, BB1)** — **3 tests Feature non-régression** avant toute refonte ou fix BA12 touchant la caisse : (1) checkout panier valide → commande créée ; (2) `createCheckoutOrder` → statut correct ; (3) `createOrder` → commission déclenchée. Cible : `app/Http/Controllers/Cashier/CashierController.php` | PR `test/cashier-regression-smoke` ; `php artisan test --filter=Cashier` ; **~2 h** | BB1 |
| 7 | **`GenealogyService` (477 L, 0 appelant, BA13)** — **Option A (recommandée)** : `git rm app/Services/MLM/GenealogyService.php tests/Unit/MLM/GenealogyServiceTest.php` ; **Option B** : `@deprecated` sur la classe + issue « suppression sprint X » | PR `chore/remove-genealogy-service-dead-code` ou doc-only ; **~5 min** | BA13 |

### Sprint 1 — « Quick wins » (1–2 jours)

- **M2** : durcir `VerifyWebhookSignature.php:22` (503 si secret vide sauf `local` explicite).  
- **BE1 suite** : notification admin (mail/Slack/log critical) quand job mensuel échoue.  
- **M7** prod : `SESSION_ENCRYPT=true`, `SESSION_SECURE_COOKIE=true`, `SESSION_SAME_SITE=strict`.  
- **PA2** : `Cache::remember` dashboard 60–300 s (`DashboardController.php:24-98`).  
- **P0-3** : test webhook Stripe signature (2C).  
- **M4** : brancher `WithdrawalRequest` sur `WithdrawalController.php:60`.

### Sprint 2 — « Refonte contrôlée » (1–2 semaines)

- **BA14** : saga / transaction orchestrée clôture (ou compensations) ; injecter `MlmPeriodCloseService` (`MonthlyCommissionService.php:92`).  
- **BB1** : découper `CashierController.php` (checkout, adhésion, POS, rapports).  
- **M6** : policies Wallet, Withdrawal, Order, Commission + `$this->authorize()`.  
- **Tests P0-2, P0-4, P0-5, P0-6** (2C).  
- **PB2–PB4** : échappement arbre réseau + CSP.  
- **PA3–PA5** : exports en `chunkById`.

### Sprint 3 — « Backlog qualité » (&gt; 1 mois)

- BA1 découpage `RankConditionChecker` ; BA13 (si Option B en Sprint 0 #7).  
- BB2/BB3 extraction services admin PV / users.  
- M8/M9 upgrades Livewire, CommonMark.  
- M1 politique compte inactif (middleware strict ou allowlist).  
- Couverture tests &gt; 50 % chemins MLM ; E2E caisse Feature.

---

## 5. Annexe — Preuves et fichiers

### Rapports sources (détail complet)

- [securite_passe2A.md](securite_passe2A.md) — sécurité, CVE, webhooks, middleware  
- [archi_bdd_passe2B.md](archi_bdd_passe2B.md) — services MLM, BA12/BA14/BE1, contrôleurs, jobs  
- [perf_tests_deploy_passe2C.md](perf_tests_deploy_passe2C.md) — perf, front, tests P0, deploy, config  

### Commandes de vérification

```bash
# Dépendances
composer audit
composer show guzzlehttp/guzzle dompdf/dompdf | head -20

# Routes sensibles (webhooks)
php artisan route:list --path=stripe
php artisan route:list --path=webhook

# Config runtime (local — ne pas logger les secrets en prod)
php artisan config:show session.encrypt cashier.webhook

# Migrations
php artisan migrate:status

# Tests
php artisan test --parallel
php artisan dusk   # si env dusk configuré

# Health
curl -sf http://127.0.0.1:8000/up
```

### Checklist post-correction (cocher par sprint)

**Sprint 0 (7 items)**

- [ ] Double appel commission même commande → une seule ligne `cash_pos` (test P0-1 vert)  
- [ ] `composer audit` sans CVE **high** sur Guzzle/Dompdf  
- [ ] `config('cashier.webhook.secret')` non null en prod  
- [ ] Job mensuel : `generatePayments` false → job **failed** (queue), pas log « completed » seul  
- [ ] `/up` OK prod  
- [ ] 3 tests Feature caisse (checkout / `createCheckoutOrder` / `createOrder` commission) verts  
- [ ] `GenealogyService` supprimé (Option A) ou `@deprecated` + issue (Option B)  

**Sprint 1**

- [ ] Webhook app : requête sans signature refusée hors `local`  
- [ ] Session prod : encrypt + secure cookie  
- [ ] Dashboard admin : 2e chargement &lt; 1re (cache)  

**Sprint 2**

- [ ] Clôture mensuelle : test intégration état période cohérent (P0-2)  
- [ ] Policy withdrawal : user B ne voit pas retrait user A  
- [ ] Export commission : pas de timeout sur jeu de données réaliste  

**Sprint 3**

- [ ] `CashierController` &lt; 800 L ou modules séparés  
- [ ] `GenealogyService` supprimé ou branché prod  

---

*Fin du rapport fusion — Passe 3. Les passes 2A/2B/2C restent la référence détaillée ; ce document est la vue priorisée pour décision et planning.*
