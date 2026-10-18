# Passe 2B — Architecture · Base de données · Queues

**Date :** 2026-10-16  
**Projet :** Salang MLM (Laravel 13, MLM / caisse / paiements)  
**Statut :** axes **A–H complétés** — **STOP 2B** (reprise 2C après validation utilisateur)  
**Ordre des passes :** `perf_tests_deploy_passe2C.md` **SUSPENDU** ; **2B seul** jusqu’à H + livrables → **STOP** → reprise 2C après validation 2B

---

## Préambule

### Documents exclus / non doublons

| Document | Action |
|----------|--------|
| `securite_passe2A.md` | **CLOSE** — ne pas modifier. Cette passe **ne reprend pas** webhooks, middleware, policies, CSRF, CVE, session cookie (déjà M7 2A). |
| `perf_tests_deploy_passe2C.md` | Entamé par ailleurs — **hors scope 2B** (perf front, Vite, pagination admin). Recoupe possible sur N+1 (`PeriodCommissionCalculator.php:43`) : traité ici côté **architecture MLM**. |

### Échelle confirmée (dépôt, 2026-10-16)

| Élément | Attendu | Mesuré | Preuve |
|---------|---------|--------|--------|
| Contrôleurs | 82 | **82** | `app/Http/Controllers/**/*.php` (glob) |
| Modèles | 32 | **32** | `app/Models/*.php` |
| Migrations | 51 | **51** | `database/migrations/*.php` |
| Services (total) | 34 | **34** | `app/Services/**/*.php` |
| Services MLM | ~20 | **19** | `app/Services/MLM/*.php` (+ `PaymentEligibilityResult.php` DTO 36 lignes) |
| Jobs | 7 | **7** | `app/Jobs/*.php` |
| Observers (fichiers) | 2 *indiqué* | **4** | `app/Observers/*.php` |
| Observers (enregistrés) | — | **4** | `AppServiceProvider.php:175-178` |

**Hors scope axe A :** migrations, modèles `$fillable`, jobs idempotence → axes D–E (à venir).

---

## A. Services MLM — inventaire et analyse

### A.1 Cartographie des dépendances (DI Laravel)

Graphe **principal** (pas de cycle de constructeur détecté) :

```text
CommissionService → AdvancedRankCalculator, RankConditionChecker, CommissionDistributor
AdvancedRankCalculator → RankConditionChecker, TeamPVCalculator
RankUpdateService → AdvancedRankCalculator, TeamPVCalculator
MonthlyCommissionService → AdvancedRankCalculator, CommissionDistributor,
                           PeriodCommissionCalculator, TeamPVCalculator,
                           PaymentEligibilityChecker, CommissionHistoryPaymentSync
                           + app(MlmPeriodCloseService) (:92, service locator)
CommissionDistributor → PendingPvLedgerService
MlmPeriodCloseService → PendingPvLedgerService, TeamPVCalculator → Job RecalculateAfterPVImport (:116-117)
PvManagementService → PendingPvLedgerService
CashierPvPeriodService → PendingPvLedgerService
PeriodCommissionCalculator → (aucun service injecté ; modèles + DB direct)
GenealogyService → (autonome, 477 lignes)
```

**Hub partagés :** `TeamPVCalculator` (381 L), `PendingPvLedgerService` (114 L), `AdvancedRankCalculator` (483 L).

**Point d’attention :** `MonthlyCommissionService.php:92` résout `MlmPeriodCloseService` via `app()` — couplage runtime, tests plus difficiles (pas une boucle DI).

---

### A.2 Tableau — tous les services `app/Services/MLM/` (+ voisins commission/PV)

| Service | Lignes | Rôle (résumé) | Tests unitaires | Dépendances injectées |
|---------|--------|---------------|-----------------|------------------------|
| `RankConditionChecker` | 688 | Vérifie conditions rang (PV, branches, SQL) `:21+` | ✅ `RankConditionCheckerLevel4Test`, `RankConditionCheckerMixedBranchesTest` | Aucune |
| `MonthlyCommissionService` | 672 | Calendrier période MLM, clôture mensuelle, orchestration fin de période `:45+` | ❌ | `AdvancedRankCalculator`, `CommissionDistributor`, `PeriodCommissionCalculator`, `TeamPVCalculator`, `PaymentEligibilityChecker`, `CommissionHistoryPaymentSync` |
| `AdvancedRankCalculator` | 483 | Calcul rang avancé + historique `:27+` | ✅ `AdvancedRankCalculatorTest` | `RankConditionChecker`, `TeamPVCalculator` |
| `GenealogyService` | 477 | Arbre généalogie / descendants `:11+` | ✅ `GenealogyServiceTest` | Aucune |
| `CommissionDistributor` | 444 | PV + commissions **temps réel** (POS, sponsor) `:34-33` docblock | ❌ | `PendingPvLedgerService` |
| `TeamPVCalculator` | 381 | **Source unique** `team_pv` / descendants `:11-28` docblock | ❌ | Aucune |
| `PeriodCommissionCalculator` | 378 | Direct / Indirect / Leadership fin de période `:38+` | ❌ | Aucune |
| `CommissionHistoryToCommissionsSync` | 226 | Sync historique → table `commissions` | ✅ `CommissionHistoryToCommissionsSyncTest` | *(à lire axe B)* |
| `CashierPvPeriodService` | 224 | Files PV caissier principal, périodes `:16-19` | ❌ | `PendingPvLedgerService` |
| `MarkOfflinePaidService` | 216 | Marquage paiements offline | ❌ | *(grep usages controllers)* |
| `GlobalCommissionReportService` | 213 | Rapport global PDF / agrégats commissions | ❌ | *(utilise modèles)* |
| `RankUpdateService` | 169 | Mise à jour rang utilisateur (wrapper calc) `:16-19` | ❌ | `AdvancedRankCalculator`, `TeamPVCalculator` |
| `MlmPeriodCloseService` | 165 | Posting PV pending + allocations clôture `:26+` | ❌ | `PendingPvLedgerService`, `TeamPVCalculator` |
| `HidePeriodsService` | 161 | Masquage périodes commission (bandes B/C) | ❌ | — |
| `PendingPvLedgerService` | 114 | Ledger PV différés (`config/mlm`) | ❌ | — |
| `PaymentEligibilityChecker` | 110 | Éligibilité paiement commissions | ✅ `PaymentEligibilityCheckerTest` | — |
| `UserCommissionDisplayService` | 87 | Présentation wallet / bonus membre | ✅ `UserCommissionDisplayServiceTest` | — |
| `CommissionHistoryPaymentSync` | 44 | Sync paiements historique | ✅ `CommissionHistoryPaymentSyncTest` | — |
| `PaymentEligibilityResult` | 36 | DTO résultat éligibilité | — (DTO) | — |

**Services MLM adjacents (hors dossier `MLM/`) :**

| Service | Lignes | Rôle | Tests | Dépendances |
|---------|--------|------|-------|-------------|
| `PvManagementService` | 464 | Ventes → `user_pv_balances`, allocations, jobs `:20-32` | ❌ | `PendingPvLedgerService` |
| `CommissionService` | 262 | Facade achat package → `CommissionDistributor` `:39-71` | ❌ | `AdvancedRankCalculator`, `RankConditionChecker`, `CommissionDistributor` |

**Couverture tests MLM :** 12 fichiers `tests/Unit/MLM/*.php` ; **~8 services couverts** sur **21** classes métier MLM+commission/PV (~38 %).

---

### A.3 Focus métier (Commission · Rang · PV · Période)

#### Commission

| Composant | Fichier | Responsibility split |
|-----------|---------|----------------------|
| Temps réel | `CommissionDistributor.php:18-33` | PV history, cash POS, bonus sponsor uniquement |
| Fin de période | `PeriodCommissionCalculator.php:14-24` | Direct, Indirect, Leadership depuis `pv_history` |
| Orchestration mensuelle | `MonthlyCommissionService.php:18-43` | Enchaîne calculateurs + sync paiements |
| Legacy façade | `CommissionService.php:36-71` | Package + wallet dans transaction `DB::beginTransaction` `:62` |

**Risque archi :** deux pipelines (temps réel vs période) — documenté dans `CommissionDistributor` docblock `:26-32` ; cohérence à valider par tests d’intégration (absents).

#### Rang

| Composant | Fichier | Notes |
|-----------|---------|-------|
| Conditions | `RankConditionChecker.php:21` | 688 L — candidat découpage |
| Calcul | `AdvancedRankCalculator.php:27` | Consomme checker + `TeamPVCalculator` |
| Entry job/controller | `RankUpdateService.php:23-30` | Cache lock `:40-45` (anti concurrence) |

Pas de classe `RankService` — le rôle est **éclaté** entre ces trois + `TeamPVCalculator`.

#### PV

| Composant | Fichier | Notes |
|-----------|---------|-------|
| Ventes / balances | `PvManagementService.php:32-60` | Transaction `:35` ; n’écrit pas `users.pv_balance` directement (commentaire `:46-48`) |
| Ledger différé | `PendingPvLedgerService.php` | Branching via `config/mlm` (defer PV) |
| Caisse période | `CashierPvPeriodService.php:22-24` | Lecture files + agrégats période |
| Clôture | `MlmPeriodCloseService.php:37-103` | `DB::transaction` ; dispatch recalcul `:116-117` |

#### Période

| Composant | Fichier | Notes |
|-----------|---------|-------|
| Calcul commissions période | `PeriodCommissionCalculator.php:38-41` | `memory_limit` 2048M, `set_time_limit(1800)` — batch lourd |
| Clôture PV | `MlmPeriodCloseService.php:26` | Appelé depuis `MonthlyCommissionService.php:92` |
| Masquage / calendrier | `HidePeriodsService`, tests `CommissionPeriodCalendarTest`, `MlmPeriodTest` | Période MLM 8→7 |

---

### A.4 COMPLÉMENT 1 — Idempotence commission (finding prioritaire)

| Contrôle | Résultat | Preuve |
|----------|----------|--------|
| 1. Flag commande (`commission_distributed` / `commission_paid` / `processed_at`) | **Absent** | `2026_06_22_000004_create_orders_table.php:7-23` — colonnes limitées ; `Order.php:12-33` `$fillable` — aucun flag commission |
| 2. `CommissionDistributor` vérifie le flag **avant** crédit | **Non** | `distributeCommissionsForOrder` `:417-444` enchaîne les items sans garde ; `distributeCommissions` `:187-295` crée `Commission::create` cash POS `:254-271` et PV `:236-238` sans test « déjà traité » |
| 3. `PeriodCommissionCalculator` exclut commandes temps réel | **Non** (agrège tout `pv_history`) | `calculateForPeriod` `:46-55` — `SUM(amount)` par user/période ; pas de jointure/filtre « orders déjà distribués ». Idempotence **partielle** : `:70-74` supprime puis recrée `direct`/`indirect`/`leadership` uniquement |
| 4. `RecalculateAfterPVImport` rejoué → double crédit commission | **Non pour commissions** | Job `:37-149` — recalc `monthly_pv`, `team_pv`, rangs ; **n’appelle pas** `CommissionDistributor`. Risque restant : **double appel** checkout + rejeu manuel `distributeCommissions` → double `pv_history` `:311-321` (pas d’unicité `order_id`) + double `cash_pos` (pas d’index unique `(order_id, type)`) — `2026_06_22_000006_create_commissions_table.php:15-25` |

Garde partielle existante : bonus sponsor filleul — `hasReceivedSponsorBonus` `:134-147` (par parrain/filleul, pas par commande).

**Verdict : BA12 — CRITIQUE** (contrôles 1 et 2 manquants ; 3 ne couvre pas le temps réel ; schéma DB sans verrou métier commande).

---

### A.5 COMPLÉMENT 2 — `GenealogyService` (code mort ?)

Recherche `GenealogyService` (hors `vendor` / `node_modules`) :

| Zone | Occurrences |
|------|-------------|
| Définition | `app/Services/MLM/GenealogyService.php:11` |
| Tests | `tests/Unit/MLM/GenealogyServiceTest.php:7-20` |
| Doc / 2B | `archi_bdd_passe2B.md`, `docs/BACKEND_AUDIT_REPORT.md` |
| **Production (`app/`, routes, jobs, observers)** | **0 import / 0 appel** |

`GenealogyObserver.php:13-28` dispatche `RecalculateAfterPVImport` — **n’utilise pas** `GenealogyService`.

**Verdict : BA13 — MAJEUR** — ~477 lignes + tests isolés ; archiver ou brancher sur `NetworkController` / admin si besoin futur.

---

### A.6 COMPLÉMENT 3 — `MonthlyCommissionService` (transaction + découpage)

#### Séquence orchestration (8 méthodes publiques métier)

Ordre **job mensuel** (`ProcessMonthlyCommissions.php:64-107`) :

1. `createMonthlyPeriod` / période existante — `:65`
2. `calculateMonthlyPVBV` — `:83`
3. `calculateMonthlyRanks` — `:91`
4. `calculateMonthlyCommissions` — `:99`
5. `generatePayments` — `:107`

Méthodes publiques **hors job** (lifecycle) : `createCurrentPeriod` `:70-74`, `cleanPeriod` `:349`, `resetMonthlyPV` `:590`, `recalculateMonthlyPV` `:626`, `getProcessingProgress` `:383`.

#### Écritures DB — `createCurrentPeriod`

- 1 appel `CommissionPeriod::findOrCreateForValue` → `updateOrCreate` `:282-292` dans `CommissionPeriod.php` → **1 `updateOrInsert` logique** (1 ligne `commission_periods` créée ou mise à jour).

#### `app(MlmPeriodCloseService)` ligne 92

**Confirmé** — `calculateMonthlyPVBV` `:92` résout le service via conteneur **avant** `DB::beginTransaction()` `:94`. **À injecter** au constructeur (comme les 6 autres dépendances `:29-42`).

#### Transaction englobante sur les 5 étapes job

**Non.** Chaque étape a sa propre transaction (`calculateMonthlyPVBV` `:94-151`, `calculateMonthlyRanks` `:200-230`, `calculateMonthlyCommissions` `:265-321`, `generatePayments` `:445-565`) ; le job `:33-149` **n’appelle pas** `DB::transaction` global.

Effet si étape 4 OK et 5 échoue : commissions en base, statut période `:573-576` repasse `calculated` ; job logue quand même « completed » si step 5 retourne `false` sans exception — `:107-110`.

Effet si `closePeriod` OK (`:92`) puis rollback PV (`:161`) : clôture PV **non annulée** par le rollback de l’étape 2.

**Verdict : BA14 — CRITIQUE** (état partiel inter-étapes + clôture hors transaction PV).

---

### A.7 Requalification BA1–BA8 (règle cohérente argent / taille / tests)

| # | Sévérité | Catégorie | Fichier | Problème |
|---|----------|-----------|---------|----------|
| BA1 | **MAJEUR** | Taille | `RankConditionChecker.php` (688 L) | >500 L + **16 tests** + critique argent (conditions rang) |
| BA2 | **CRITIQUE** | Taille + tests | `MonthlyCommissionService.php` (672 L) | >500 L + **aucun test** + orchestration paiements |
| BA3 | **MAJEUR** | Taille + tests | `PvManagementService.php` (464 L) | 300–500 L + **aucun test** + argent (PV ventes) |
| BA4 | **CRITIQUE** | Tests | `PeriodCommissionCalculator.php` (378 L) | 300–500 L + **aucun test** + bonus Direct/Indirect/Leadership |
| BA5 | **CRITIQUE** | Tests | `CommissionDistributor.php` (444 L) | 300–500 L + **aucun test** + temps réel argent/PV |
| BA6 | MAJEUR | Tests | `MlmPeriodCloseService.php` | Aucun test — clôture PV |
| BA7 | MAJEUR | Tests | `PvManagementService.php` | (couvert par BA3) |
| BA8 | **MINEUR** | Taille | `AdvancedRankCalculator.php` (483 L), `TeamPVCalculator.php` (381 L) | >300 L mais **tests** (Advanced) ou hub infra (TeamPV) — non argent direct |
| BA9 | MINEUR | Couplage | `MonthlyCommissionService.php:92` | Service locator |
| BA10 | MINEUR | Perf archi | `PeriodCommissionCalculator.php:40-43` | Charge tous users actifs |
| **BA11** | **POINT FORT** | DI | `app/Services/MLM/` | Voir ci-dessous |
| **BA12** | **CRITIQUE** | Idempotence | Orders + `CommissionDistributor` + schéma | Double crédit / double PV possible |
| **BA13** | **MAJEUR** | Code mort | `GenealogyService.php` | 0 appelant prod |
| **BA14** | **CRITIQUE** | Transactions | `MonthlyCommissionService` + `ProcessMonthlyCommissions` | Pas de transaction globale clôture |

#### BA11 — POINT FORT (à conserver dans l’audit final)

> **Aucun cycle DI détecté dans `app/Services/MLM/`.** Hub **`TeamPVCalculator`** avec appelants injectés ou résolus : `AdvancedRankCalculator.php:17-21`, `MlmPeriodCloseService.php:18`, `MonthlyCommissionService.php:23-33`, `RankUpdateService.php:26-30`, `User.php:468`, `RecalculateAfterPVImport.php:37-39`, `UpdateTeamPV.php:34`, jobs/commands (`RecalculateAllRanks.php:49`, `FixTeamPVInconsistencies.php:19`). **Aucun** constructeur MLM ne ré-injecte une chaîne retour vers `TeamPVCalculator` en boucle. Preuve : graphe A.1 + grep `TeamPVCalculator` dans `app/`.

---

### A.8 Findings axe A (table consolidée)

| # | Sévérité | Catégorie | Fichier:Ligne | Problème | Impact | Correction |
|---|----------|-----------|---------------|----------|--------|------------|
| BA1 | MAJEUR | Taille | `RankConditionChecker.php` | God class 688 L | Maintenance | Découper par niveau (`config/ranks.php`) |
| BA2 | CRITIQUE | Orchestration | `MonthlyCommissionService.php` | Monolithe clôture sans tests | Régression paiements | Split + tests intégration job |
| BA3 | MAJEUR | PV | `PvManagementService.php` | 464 L sans tests | Soldes PV incohérents | `PvAllocationService` + tests |
| BA4 | CRITIQUE | Tests | `PeriodCommissionCalculator.php:38+` | 0 test | Bonus période | Golden tests arbre |
| BA5 | CRITIQUE | Tests | `CommissionDistributor.php:187+` | 0 test + pas idempotent | Double commission | Tests + flag commande |
| BA6 | MAJEUR | Tests | `MlmPeriodCloseService.php:37+` | 0 test | PV pending | Test clôture |
| BA8 | MINEUR | Taille | `AdvancedRankCalculator`, `TeamPVCalculator` | Lisibilité | — | Refactor progressif |
| BA9 | MINEUR | DI | `MonthlyCommissionService.php:92` | `app()` | Tests | Injection |
| BA10 | MINEUR | Perf | `PeriodCommissionCalculator.php:43` | `User::where(...)->get()` | OOM | Chunk SQL |
| BA11 | OK | DI | Hub `TeamPVCalculator` | — | — | Documenter (fait) |
| BA12 | CRITIQUE | Idempotence | `orders` + `CommissionDistributor.php:417+` | Pas de flag commande | **Double crédit** | Colonne + garde + unique `(order_id,type)` |
| BA13 | MAJEUR | Dead code | `GenealogyService.php` | 0 appelant prod | Dette 477 L | Archiver / supprimer |
| BA14 | CRITIQUE | Transactions | `ProcessMonthlyCommissions.php:81-110` | 5 étapes sans TX globale | État partiel payé | Saga / TX + idempotence steps |

**Axe A — validé** (compléments 1–3 + requalification).

---

## B. Contrôleurs (82)

**Mesure :** 40 contrôleurs **>200 lignes** ; **8** FormRequests (`app/Http/Requests/`) pour 82 contrôleurs (~10 %).

### B.1 Top 20 par `wc -l`

| Lignes | Fichier |
|--------|---------|
| 2989 | `Cashier/CashierController.php` |
| 1472 | `Admin/ReportController.php` |
| 1106 | `Admin/UserController.php` |
| 1053 | `Admin/AdminPVImportController.php` |
| 889 | `CommissionController.php` |
| 869 | `Admin/AdminPVController.php` |
| 705 | `CommissionTriggerController.php` |
| 688 | `Admin/AdminCommissionHistoryController.php` |
| 665 | `DashboardController.php` |
| 652 | `Admin/CommissionController.php` |
| 641 | `Admin/ActivationController.php` |
| 616 | `WebhookController.php` |
| 614 | `ActivationController.php` |
| 544 | `NetworkController.php` |
| 523 | `Cashier/CashierReportController.php` |
| 465 | `Auth/RegisteredUserController.php` |
| 451 | `Cashier/ExpenseController.php` |
| 443 | `UserPackageController.php` |
| 424 | `CartController.php` |
| 405 | `Admin/RankController.php` |

### B.2 Findings

| # | Sévérité | Fichier:Ligne | Problème | Impact | Correction |
|---|----------|---------------|----------|--------|------------|
| BB1 | CRITIQUE | `CashierController.php` (~2989 L) | God controller caisse + MLM inline | Régression caisse/commission | Extraire actions en services (déjà partiel `:2097` `CommissionDistributor`) |
| BB2 | MAJEUR | `AdminPVImportController.php` (~1053 L) | Import PV + recalc inline | Erreurs import massif | Command + service dédié |
| BB3 | MAJEUR | `Admin/UserController.php` (~1106 L) | CRUD + rang/PV inline | Surface admin | FormRequests + policies User |
| BB4 | MAJEUR | `CommissionController.php`, `CommissionTriggerController.php` | Logique commission hors services MLM | Doublon pipeline | Centraliser sur services axe A |
| BB5 | MINEUR | Global | 8 FormRequests / 82 controllers | Validation inconsistante | FormRequests domaine MLM/wallet |

---

## C. Modèles (32)

| Modèle | Lignes | Risque |
|--------|--------|--------|
| `User.php` | 942 | `$fillable` inclut `pv_balance`, `team_pv`, `rank_level`, `commission_balance` `:26-42` — mass assignment MLM |
| `Commission.php` | 568 | Logique métier / scopes denses |
| `CommissionPeriod.php` | 509 | Calendrier MLM + factory période |
| `Order.php` | 249 | Pas de flag idempotence commission (BA12) |

| # | Sévérité | Fichier:Ligne | Problème | Correction |
|---|----------|---------------|----------|------------|
| BC1 | MAJEUR | `User.php:26-42` | Champs financiers / rang en `$fillable` | `$guarded` ou DTO admin ; Policy |
| BC2 | MAJEUR | `Order.php:51-54` | Commentaire observer seul — pas d’état « commissions traitées » | Colonne + cast (BA12) |
| BC3 | MINEUR | `User.php` `booted()` | Side effects rang → jobs | Documenter ; éviter double dispatch avec observers |

**Policies :** 2 fichiers (`PublicationPolicy`, `QuestionConversationPolicy`) — **aucune** policy `User`, `Commission`, `Wallet`, `Withdrawal`.

---

## D. Migrations (51)

- **Tables créées :** ~35 migrations `Schema::create` (dont Laravel cache/jobs/users).
- **FK explicites :** présentes sur cœur MLM (`orders`, `commissions`, `order_items`, `wallets`, `transactions`, `genealogy`, `rank_history`) — grep `foreign(` ≈ 12 migrations.
- **Unicité métier commission :** `commission_payments` `(user_id, commission_period_id)` `:28` ; **pas** d’unicité `(order_id, type)` sur `commissions` (`2026_06_22_000006_create_commissions_table.php:15-25`).
- **PV :** `pv_history` index `(user_id, period)` — pas d’unicité par `order_id` (`2026_10_03_130000_create_pv_history_and_mlm_test_schema.php:22` ; `order_id` ajouté `2026_10_16_120000_add_mlm_deferred_pv_and_allocation_period.php:14`).

| # | Sévérité | Problème | Correction |
|---|----------|----------|------------|
| BD1 | CRITIQUE | Absence contrainte idempotence commande / commission (BA12) | Migration `orders.commissions_processed_at` + unique partiel commissions |
| BD2 | MAJEUR | Beaucoup de `Schema::table` conditionnels (`hasColumn`) | OK prod ; documenter ordre deploy |
| BD3 | MINEUR | `commission_period_id` nullable sur commissions anciennes | Backfill + NOT NULL futur |

---

## E. Jobs (7)

| Job | Rôle | Idempotence / notes |
|-----|------|---------------------|
| `ProcessMonthlyCommissions` | Clôture mensuelle 4 étapes | `tries=3` ; pas de lock unique période ; step 5 échec silencieux `:107-110` |
| `RecalculateAfterPVImport` | PV mensuel + team_pv + rangs | `tries=1` ; pas commission ; rejeu = re-écrit users |
| `ProcessWithdrawals` | Retraits | Transaction interne |
| `CalculatePVBV` | Legacy PV/BV | Vérifier scheduler |
| `UpdateTeamPV` | Redirect `TeamPVCalculator` | `:34` |
| `UpdateCumulativePV` | Cumul PV | À auditer scheduler |
| `UpdateRanks` | **Déprécié** `:14-17` | Ne traite qu’un user |

| # | Sévérité | Fichier:Ligne | Problème |
|---|----------|---------------|----------|
| BE1 | CRITIQUE | `ProcessMonthlyCommissions.php:107-110` | Paiements `failed` sans `throw` — job « completed » |
| BE2 | MAJEUR | `ProcessMonthlyCommissions.php:68-74` | Garde `calculating`/`paying` seulement — double dispatch possible |
| BE3 | MINEUR | `UpdateRanks.php` | Job mort — supprimer appelants |

---

## F. Observers (4 enregistrés)

| Observer | Modèle | Événement | Effet | Boucle ? |
|----------|--------|-----------|-------|----------|
| `UserObserver` | `User` | *(vide)* | Délégué à `User::booted()` | — |
| `OrderObserver` | `Order` | `created`/`updated` completed | `RecalculateAfterPVImport` `:42-43` | Risque si commande met à jour PV ailleurs même tick |
| `GenealogyObserver` | `Genealogy` | `created` | `RecalculateAfterPVImport` `:27-28` | Idem |
| `PublicationMediaObserver` | `PublicationMedia` | médias | hors MLM | — |

Enregistrement : `AppServiceProvider.php:175-178`.

| # | Sévérité | Problème |
|---|----------|----------|
| BF1 | MAJEUR | `OrderObserver` + `CommissionDistributor` + `User::booted()` — triple chemin recalc rang (pas commission) — charge queue |
| BF2 | MINEUR | `UserObserver` vide — bruit / confusion |

---

## G. Transactions (services + controllers critiques)

| Zone | Pattern | Fichier |
|------|---------|---------|
| MLM clôture | TX par étape, **pas** globale | `MonthlyCommissionService.php` (7× `beginTransaction`) |
| Clôture PV | `DB::transaction` | `MlmPeriodCloseService.php` |
| Achat package | TX | `CommissionService.php:62` |
| PV vente | TX | `PvManagementService.php:35` |
| Caisse | 6× TX | `CashierController.php` (grep) |
| Webhook wallet | TX | `WebhookController.php` |

| # | Sévérité | Problème |
|---|----------|----------|
| BG1 | CRITIQUE | BA14 — clôture mensuelle multi-TX sans compensation |
| BG2 | MAJEUR | `calculateMonthlyPVBV` appelle `closePeriod` **hors** TX `:92-94` |
| BG3 | MINEUR | Controllers fat utilisent TX locales — OK partiel |

---

## H. Tests par domaine

| Domaine | Fichiers | Couverture |
|---------|----------|------------|
| MLM unit | 12 × `tests/Unit/MLM/*` | Rang, calendrier période, sync historique, éligibilité paiement — **pas** `CommissionDistributor`, `PeriodCommissionCalculator`, `MonthlyCommissionService` |
| MLM feature | `Feature/MLM/CommissionCalculationTest.php` | PV history package — **ne teste pas** idempotence double appel |
| API commission | `Feature/API/ApiCommissionTest.php`, `ApiCommissionBandsTest.php` | Bands / API |
| Browser | `Browser/CommissionTest.php`, `PurchaseTest.php` | Smoke Dusk |
| Auth / Admin | Feature Auth, `AdminUserTest` | Hors MLM core |

| # | Sévérité | Problème | Plan |
|---|----------|----------|------|
| BH1 | CRITIQUE | Pas de test idempotence commande (BA12) | Feature : double `distributeCommissions` → 1 commission cash_pos |
| BH2 | CRITIQUE | Pas de test clôture mensuelle bout-en-bout | Test job `ProcessMonthlyCommissions` avec SQLite + fake queue |
| BH3 | MAJEUR | 38 fichiers tests / 82 controllers | Prioriser caisse + webhook + clôture |
| BH4 | MAJEUR | GenealogyServiceTest maintient code mort (BA13) | Supprimer ou brancher prod |

---

## Livrables finaux 2B

### Top 5 — CRITIQUE / MAJEUR (priorité correction)

| Rang | ID | Sévérité | Synthèse |
|------|-----|----------|----------|
| 1 | **BA12** | CRITIQUE | Idempotence commission / PV par commande absente |
| 2 | **BA14** | CRITIQUE | Clôture mensuelle sans transaction globale + `closePeriod` hors TX |
| 3 | **BB1** | CRITIQUE | `CashierController` ~3000 L — risque opérationnel caisse |
| 4 | **BE1** | CRITIQUE | Job mensuel marque succès si paiements échouent |
| 5 | **BA2/BA4/BA5** | CRITIQUE | Services argent >300 L sans tests unitaires |

### Matrice conformité (extrait)

| Domaine | DI / structure | Idempotence argent | Tests | Verdict |
|---------|----------------|-------------------|-------|---------|
| Services MLM | ✅ BA11 | ❌ BA12 | ⚠️ ~38 % services | **À corriger** |
| Contrôleurs | ⚠️ fat | ❌ inline | ⚠️ | **Refactor** |
| Modèles | ⚠️ fillable | ❌ Order | ⚠️ policies | **Durcir** |
| BDD | ✅ FK core | ❌ unicité commission | — | **Migration** |
| Jobs | ⚠️ | ⚠️ BE1/BE2 | — | **Durcir** |
| Observers | ✅ 4 | N/A | — | **OK** |
| Transactions | ❌ BA14 | — | — | **Saga/TX** |
| Tests | — | ❌ BH1 | ⚠️ | **Plan ci-dessous** |

### Plan tests recommandé (post-2B)

1. **BA12** — Feature idempotence `CommissionDistributor` + contrainte DB.
2. **BA14/BE1** — Test intégration `ProcessMonthlyCommissions` (échec step 5 → statut cohérent).
3. **PeriodCommissionCalculator** — Golden file 3 membres, 2 générations.
4. **MlmPeriodCloseService** — PV pending → posted + pas de double `pv_history`.
5. **Cashier** — Feature smoke adhésion POS (1 parcours) sans Dusk.

---

**STOP passe 2B** — axes A→H terminés. **Ne pas reprendre 2C** (`perf_tests_deploy_passe2C.md`) sans validation explicite.

*Document : `archi_bdd_passe2B.md` — 2026-10-16.*
