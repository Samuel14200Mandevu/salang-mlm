# Passe 2A — Audit sécurité (Salang MLM)

> Document temporaire — fusion prévue dans `AUDIT.md` après validation des passes d’audit.

**Date :** 2026-10-16  
**Périmètre :** sécurité uniquement  
**Laravel :** 13.18.0 · **PHP cible (Composer) :** ^8.3  

---

## Clarifications (post P1.2–P1.6)

### CLARIF 1 — `EnsureUserActive.php:65-75`

**Comportement exact :** si l’utilisateur est authentifié mais `is_active` est faux (ou `$freshUser` absent après `:37-41`), le middleware **ne bloque pas** la requête. Il partage `account_inactive = true` en vue (`:66-67`), flash un avertissement commission (`:69-70`), puis **`return $next($request)`** (`:74-75`).

**Commentaires explicites dans le code :**

- `:65` — `// COMPTE INACTIF : ON NE BLOQUE PAS L'ACCÈS`
- `:74` — `// PERMETTRE L'ACCÈS À TOUTES LES ROUTES`

**Intention vs bug :** **intentionnel** côté produit (accès au parcours d’activation, dashboard, achat d’activation) — cohérent avec le flash « Activez-le pour recevoir des commissions » (`:70`) et les routes `activate.*` dans le même groupe `auth`+`active` (`routes/web.php:157`, `:329-334`). Ce n’est **pas** un oubli de `abort` : le bloc actif court-circuite seulement le cas `is_active === true` (`:54-62`).

**Où s’applique `active` :**

- Alias : `bootstrap/app.php:47` → `EnsureUserActive`.
- Routes : `routes/web.php:136`, `:146`, `:157` (membre + paiement + wallet + `payment.process` `:299`), `:342` (cashier), `:491` (admin **+** `AdminMiddleware` qui, lui, bloque l’inactif `AdminMiddleware.php:50-62`).

**Verdict :** **MAJEUR** — choix métier documenté, mais **périmètre trop large** : compte inactif conserve l’accès aux zones cashier (si rôle caisse) et paiement membre sans garde `is_active` supplémentaire dans les contrôleurs (ex. `PaymentController.php:132+` sans test `is_active`).

---

### CLARIF 2 — `VerifyWebhookSignature.php:22`

**Portée :** la branche « passe sans signature » est **`app()->environment('production') === false`** (`:15-22`), donc **local, staging, testing** et tout autre env non nommé `production` — pas limité à `APP_ENV=local`.

**Recommandation audit :** exiger un secret dès que `APP_ENV !== 'local'` (ou dès `staging` / `production`), sinon **503** ; conserver le bypass uniquement pour dev local explicite.

**Verdict :** **MAJEUR** (CRITIQUE si staging partage données réelles / secrets de prod absents — crédit wallet via `WebhookController.php:464-467`).

---

### CLARIF 3 — `WebhookController.php:439`

**Analyse :** `$request->validate([...])` sur payload JSON entrant (`:439-447`) est un **pattern légitime** pour webhooks (corps brut, pas de session, pas de FormRequest standard Laravel Cashier). Un FormRequest dédié (`WebhookPaymentRequest`) serait du **refactoring qualité**, pas une exigence sécurité si la **signature** est appliquée avant.

**Verdict :** **OK** (la sévérité repose sur `VerifyWebhookSignature` + secrets, pas sur l’absence de FormRequest).

---

### CLARIF 4 — `cashier/consultations/*` et workflow counts

| Route / action | Fichier:ligne | Comportement | Verdict |
|----------------|---------------|--------------|---------|
| `index` | `ConsultationController.php:21-58` | Liste **filtrée** `cashier_id = Auth::id()`, stats comptages, marque notifications lues | **MINEUR** (données patient / PII) |
| `create` | `:64-70` | Formulaire, génère codes affichage | **OK** |
| `store` | `:76-119` | Crée `Consultation` (données patient, `status=pending`, totaux **0**, pas de vente ni commission) | **MAJEUR** (intégrité : tout utilisateur `auth`+`active` peut créer des fiches avec `cashier_id = Auth::id()` sans `isCashierStaff`) |
| `show` | `:125-142` | Lecture + stats produits ; `abort(403)` si pas propriétaire `:128-129` | **MINEUR** |
| `checkoutSelection` | `:148-183` | Valide lignes, remplit `session('pos_cart')` et `consultation_checkout`, redirect **encaissement** `cashier.checkout` | **MAJEUR** (préparation flux financier ; **finalisation** bloquée par `CashierController.php:48-50` `isCashierStaff()` sur `createCheckoutOrder` / checkout) |
| `print` | `:189-203` | Impression si `status=completed` + propriétaire | **MINEUR** |
| `cashier.workflow-pending-counts` | `WorkflowPendingCountController.php:25-40` | JSON : **compteurs** notifications non lues (consultations, rapports), **pas** de soldes / montants | **OK** |

---

## Révisions (clôture passe 2A)

### RÉVISION C2 — Secrets webhooks app (`services.webhooks.*`)

**Sources dépôt / runtime audité (2026-10-16) :**

| Variable (`config/services.php:6-11`) | `.env.example` | `.env` local (audit) | `config:show services` |
|--------------------------------------|----------------|----------------------|-------------------------|
| `FLEXPAY_WEBHOOK_SECRET` | absent | ABSENT | `null` |
| `PAYMENT_WEBHOOK_SECRET` | absent | ABSENT | `null` |
| `MOBILE_MONEY_WEBHOOK_SECRET` | absent | ABSENT | `null` |
| `CRYPTO_WEBHOOK_SECRET` | `:53` (vide) | ABSENT | `null` |
| `STRIPE_WEBHOOK_SECRET` (bloc webhooks) | absent | ABSENT | `null` |

- `grep -rn "WEBHOOK_SECRET\|webhooks.*secret" .env.example config/ app/` → clés dans `config/services.php:7-11` ; seul `CRYPTO_WEBHOOK_SECRET` dans `.env.example:53` ; `app/` → `VerifyWebhookSignature.php:13` uniquement.
- `.env.production` : `FLEXPAY_WEBHOOK_SECRET=[A_REMPLIR]` (`:157`) ; **pas** de `PAYMENT_WEBHOOK_SECRET` / `MOBILE_MONEY_WEBHOOK_SECRET` (noms attendus par `config/services.php`).
- **Prod Laravel Cloud** : valeurs **non lisibles** depuis le dépôt ; checklist `docs/PRODUCTION_CHECKLIST.md:31` (« à configurer »).

**Comportement middleware (diffère de C1 Stripe) :**

- `VerifyWebhookSignature.php:16-19` — si secret vide **et** `production` → **503** (pas de crédit wallet).
- `VerifyWebhookSignature.php:22` — si secret vide **et** env **≠** `production` → requête **acceptée sans signature** → `WebhookController.php:464-467` peut créditer le wallet.

**Verdict révision :** **C2 reclassé MAJEUR (ex-M2 enrichi)** — forge wallet prouvée sur **local/staging** (runtime audité : secrets null). En **prod** avec `APP_ENV=production` et secret vide → webhooks **refusés** (503), pas forge (≠ C1). En **prod** avec secrets renseignés (attendu Cloud) → signatures HMAC actives. Risque **MAJEUR** : bypass `:22`, doc `.env.example` / `.env.production` incomplète, staging avec données réelles.

**Action clôture prod :** `php artisan tinker` sur Cloud → `config('services.webhooks.payment.secret')` non null (sans logger la valeur).

---

### RÉVISION C5 — Encaissement après `checkout-selection`

**Chaîne code :**

1. `ConsultationController.php:174-182` — remplit `pos_cart` / `consultation_checkout`, redirect `route('cashier.checkout')`.
2. `cashier.checkout`, `cashier.checkout.order`, `cashier.pos.order` → **`CashierController`** (`route:list` → `@checkout`, `@createCheckoutOrder`, `@createOrder`).
3. `CashierController.php:48-50` — **toute** action du contrôleur : `if (! auth()->user()->isCashierStaff()) { abort(403, ...) }` (`User.php:939-941` : rôles `cashier`, `caissier_principal`, `admin`).

**Test mental (membre sans rôle caisse) :**

- `POST cashier/consultations/{id}/checkout-selection` → OK si propriétaire de la fiche (`:150-151`) → redirect checkout.
- `GET cashier/checkout` ou `POST cashier/checkout/order` ou `POST cashier/pos/order` → **403** middleware `:48-50`.

**Verdict révision :** **C5 reclassé MAJEUR (M11)** — pollution workflow / PII / session panier ; **pas** d’encaissement ni commande POS sans `isCashierStaff()`.

---

### RÉVISION C6 — `.env.production` versionné ?

| Check | Résultat |
|-------|----------|
| `git ls-files \| grep -E "^\.env"` | **Uniquement** `.env.example`, `.env.dusk.example`, `.env.testing.example` — **pas** `.env.production` |
| `git ls-files \| grep -E "\.env"` | idem (+ aucun autre `.env*` suivi) |
| `.gitignore` | `:5` `.env.production` ; `:29` `.env*` ; `:30-31` exceptions dusk/testing example |
| `git log -- .env.production` | **Aucun** commit (fichier ignoré / jamais suivi) |
| Contenu local (fichier présent sur disque, 244 lignes) | Mix placeholders (`[A_REMPLIR]`, `your_*`) **et** clés sensibles avec valeurs non vides (ex. `APP_KEY`, `DB_PASSWORD`, `MAIL_PASSWORD`, `GOOGLE_CLIENT_SECRET` — longueurs 20–64) |

**Verdict C6 :** **NON** — pas de fuite via **historique git** de `.env.production` (correctement ignoré).

**Finding associé :** **N4 MINEUR** — fichier `.env.production` local ne doit pas être commité ; préférer `.env.production.example` + doc (`README` / checklist). Risque opérationnel si un dev force `git add -f`.

**Note distincte (committé) :** `.env.example:24-25` contient un `DB_USERNAME` / `DB_PASSWORD` **non placeholder** dans git — voir **M3** (documentation / secrets d’exemple) ; rotation si ce mot de passe a été réutilisé hors dev local.

---

## Passe 2A — **CLOSE** (2026-10-16)

---

## Tableau findings complet

| # | Sévérité | Catégorie | Fichier:Ligne | Problème | Impact | Correction |
|---|----------|-----------|---------------|----------|--------|------------|
| C1 | CRITIQUE | Webhook Stripe | `vendor/laravel/cashier/config/cashier.php:50` ; `WebhookController.php:27-31` ; `route:list` `stripe/webhook` | `STRIPE_WEBHOOK_SECRET` absent → middleware Cashier **non** enregistré ; POST accepté sans `Stripe-Signature` | Forge d’événements Stripe ; intégrité abonnements / facturation Cashier | Définir `STRIPE_WEBHOOK_SECRET` ; `vendor:publish --tag=cashier-config` ; tests 401/403 sans signature ; tinker `config('cashier.webhook.secret')` non null |
| C3 | CRITIQUE | Dépendance runtime | `composer audit` ; `guzzlehttp/guzzle` **7.13.1** installé | CVE **high** `CVE-2026-69246` (affected `<7.15.2` / `8.x<8.0.1`) + 5 autres advisories Guzzle | Compromission chaîne HTTP (API paiement, webhooks, tiers) | `composer update guzzlehttp/guzzle` ≥ 7.15.2 (ou 8.0.1+) ; CI `composer audit` bloquant |
| C4 | CRITIQUE | Dépendance runtime | `composer audit` ; `dompdf/dompdf` **v3.1.5** (`<3.1.6`) | CVE file read / DoS (ex. `CVE-2026-56722`, `CVE-2026-59943`) | Fuite fichiers / DoS via PDF générés (factures, rapports caisse/admin) | Mettre à jour `dompdf/dompdf` ≥ 3.1.6 ; durcir HTML/SVG sources PDF |
| M1 | MAJEUR | Compte inactif | `EnsureUserActive.php:65-75` ; `routes/web.php:157-299` | Compte inactif : accès **total** au groupe `auth`+`active` (commentaire « PERMETTRE L'ACCÈS ») | Paiements / wallet / cashier (si rôle) sans activation MLM | Middleware `active.strict` ou allowlist routes activation ; gardes `is_active` sur paiement/retrait |
| M2 | MAJEUR | Webhook app (ex-C2) | `VerifyWebhookSignature.php:15-22` ; `WebhookController.php:439-467` ; `config/services.php:6-11` | Non-prod + secret null → pas de signature ; prod + secret null → 503 ; secrets non documentés / local null | Forge wallet local/staging ; prod OK si secrets Cloud ; indispo webhooks si prod sans secret | Secrets dès env ≠ local ; compléter `.env.example` ; tinker prod ; durcir `:22` |
| M11 | MAJEUR | Contrôle d’accès caisse (ex-C5) | `routes/web.php:377-382` ; `ConsultationController.php:16-17`, `:148-183` ; `CashierController.php:48-50` | Consultations sans `cashier.staff` ; `checkoutSelection` amorce panier | Fiches / PII / session POS ; encaissement **bloqué** 403 sur `CashierController` | Middleware `cashier.staff` sur `ConsultationController` |
| M3 | MAJEUR | Documentation | `.env.example:1-80` | Pas de `STRIPE_WEBHOOK_SECRET` / clés Stripe ; `APP_DEBUG=true` `:4` | Déploiements sans webhook Stripe ; debug par défaut | Documenter variables ; `APP_DEBUG=false` en exemple prod |
| M4 | MAJEUR | Validation | `WithdrawalRequest.php:8` ; `WithdrawalController.php:65` | FormRequest retrait **non branché** | Règles retrait non centralisées / contournables | `WithdrawalRequest` dans `store`/`update` |
| M5 | MAJEUR | Validation | `PaymentController.php:134` ; `WalletController.php:146` ; `Admin/WalletController.php:103` | Validation inline uniquement | Risque incohérence limites montants / méthodes | FormRequests dédiés paiement / wallet |
| M6 | MAJEUR | Policies | `app/Policies/` (2 fichiers) ; P1.6 | Aucune policy User/Wallet/Withdrawal/Order/Commission/Rank/Kyc | IDOR / autorisation fine non testable via Gate | Policies + `$this->authorize()` |
| M7 | MAJEUR | Session | `vendor/laravel/framework/config/session.php:50,172,202` ; `.env.example:31` ; `config:show session` | `encrypt=false`, `secure=null`, `same_site=lax` | Vol session / fixation si HTTP ou XSS | `SESSION_ENCRYPT=true` ; `SESSION_SECURE_COOKIE=true` prod ; `SESSION_SAME_SITE=strict` |
| M8 | MAJEUR | Dépendance | `livewire/livewire` **v4.3.3** ; `CVE-2026-81887` | Advisory medium (≤4.3.3) | Surface Livewire admin/member | Upgrade Livewire > 4.3.3 quand dispo |
| M9 | MAJEUR | Dépendance | `league/commonmark` **2.8.2** ; multiples CVE high audit | Markdown XSS / bypass | Contenu user markdown (si rendu) | Upgrade ≥ 2.9.1 / 2.10.2 selon advisory |
| N1 | MINEUR | Rate limit API | `Api/AuthController.php` (pas de `RateLimiter`) ; vs `LoginRequest.php:87` | Login web 5 essais ; API login sans throttle route | Bruteforce API | `throttle:login` sur `routes/api.php:31-32` |
| N2 | MINEUR | Routes publiques | `routes/web.php:93-94` | `payment/success` / `cancel` sans auth | Open redirect / UX phishing | Token état paiement |
| N3 | MINEUR | Consultations PII | `ConsultationController.php:21-58` | Index/stats patient pour tout `auth` ayant accès route | Fuite PII si garde caisse absente | Corrigé avec middleware `cashier.staff` (M11) |
| N4 | MINEUR | Secrets / VCS | `.gitignore:5,29` ; `git ls-files` | `.env.production` ignoré (OK) ; fichier local peut contenir secrets réels | Commit accidentel `-f` | `.env.production.example` uniquement ; pas de C6 git |

*(Références P1.x conservées plus bas.)*

---

## Top 5 CRITIQUE (justification — post-révision)

**1. C1 — Webhook Stripe Cashier non signé**  
`config('cashier.webhook.secret')` null désactive le middleware Cashier (`WebhookController.php:27-31`).  
`POST /stripe/webhook` accepte des payloads sans `Stripe-Signature` (même en `production`).  
Impact direct intégrité Stripe / Cashier.

**2. C3 — Guzzle 7.13.1 (CVE high runtime)**  
`CVE-2026-69246` (high) ; affected `<7.15.2`.  
Client HTTP de paiements et intégrations tierces.  
Upgrade Composer prioritaire.

**3. C4 — Dompdf v3.1.5 (< 3.1.6)**  
CVE lecture fichier / DoS sur génération PDF.  
Factures et rapports caisse/admin exposés.  
Upgrade ≥ 3.1.6.

**4. *(Réserve — dépendance)* — `league/commonmark` 2.8.2**  
Multiples advisories **high** au `composer audit` (runtime si Markdown utilisateur rendu).  
Classé **MAJEUR M9** dans le tableau ; à promouvoir si surface Markdown confirmée en prod.

**5. *(Réserve métier)* — Compte inactif + paiements**  
`EnsureUserActive.php:74-75` + `routes/web.php:299` : pas de blocage `is_active` sur `payment.process`.  
Classé **MAJEUR M1** ; CRITIQUE si politique « aucun paiement sans activation » est contractuelle.

*(Ex-C2 webhook app → **M2 MAJEUR** ; ex-C5 consultations → **M11 MAJEUR** — voir révisions.)*

---

## Matrice de conformité

| Pratique | Statut | Preuve |
|----------|--------|--------|
| Webhooks signés (Stripe Cashier) | ❌ | C1 — `cashier.php:50`, `WebhookController.php:27-31`, secret null |
| Webhooks signés (Flexpay/mobile/crypto/payment) | ⚠️ | M2 — prod+secret → HMAC ; prod sans secret → 503 `:16-19` ; non-prod bypass `:22` ; secrets null en audit local |
| Middleware admin | ✅ | `AdminMiddleware.php:15-62` |
| Middleware cashier staff | ⚠️ 7 routes | P1.3 — `ConsultationController` + `WorkflowPendingCountController` |
| Policies sur modèles sensibles | ❌ 0/7 | P1.6 — seulement Publication / Question |
| FormRequests vs contrôleurs | ❌ 8/82 | P1.5 — 8 FormRequests, 82 fichiers contrôleurs |
| Validation inline paiement/retrait | ❌ | `PaymentController.php:134`, `WithdrawalController.php:65` |
| Session chiffrée | ❌ | `session.php:50`, `.env.example:31`, `config:show` encrypt false |
| Cookie secure + SameSite strict | ❌ | `session.php:172,202` — secure null, same_site lax |
| APP_DEBUG=false défaut | ❌ | `.env.example:4` `APP_DEBUG=true` |
| Rate limit login web | ✅ | `LoginRequest.php:87` (5 tentatives), `AuthenticatedSessionController.php:20` |
| Rate limit login API | ❌ | `Api/AuthController.php` sans `RateLimiter` |
| HTTPS forcé | ✅ (prod) | `AppServiceProvider.php:160-162` `URL::forceScheme('https')` si `production` |
| composer audit clean | ❌ 30 advisories / 9 packages | `composer audit` 2026-10-16 |

---

## Advisories Composer (30 advisories · 9 packages)

| Package | Installé | Advisories | Sévérité max | Runtime / dev | Version corrigée (audit) | Classe audit |
|---------|----------|------------|--------------|---------------|---------------------------|--------------|
| dompdf/dompdf | v3.1.5 | 6 | medium (+2 low) | **Runtime** (PDF) | ≥ 3.1.6 | **MAJEUR** (C4 → CRITIQUE expos PDF) |
| guzzlehttp/guzzle | 7.13.1 | 6 | **high** | **Runtime** | ≥ 7.15.2 (ou 8.0.1+) | **CRITIQUE** (C3) |
| league/commonmark | 2.8.2 | 12 | **high** | Transitive (Markdown) | ≥ 2.9.1 / 2.10.x selon CVE | **MAJEUR** |
| laravel/framework | v13.18.0 | 1 | low | **Runtime** | ≥ 13.30.0 | MINEUR |
| league/flysystem | (lock) | 1 | low | **Runtime** | > 3.35.2 | MINEUR |
| livewire/livewire | v4.3.3 | 1 | medium | **Runtime** | > 4.3.3 | **MAJEUR** |
| phpseclib/phpseclib | (transitive) | 1 | medium | Transitive | ≥ 3.0.57 / 4.0.1 | MINEUR |
| symfony/http-foundation | (transitive) | 1 | n/a (ReDoS) | **Runtime** | ≥ 7.4.19 / 8.1.7 selon branche | MINEUR |
| symfony/yaml | (transitive) | 1 | n/a (DoS) | Dev/CLI surtout | ≥ 7.4.18 / 8.1.6 | MINEUR |

**Détail par CVE (extrait `composer audit --format=json`) :**

- **dompdf/dompdf** — CVE-2026-59943, 59942, 59941, 56722, 55555, 55554 — affected `<3.1.6`
- **guzzlehttp/guzzle** — CVE-2026-69246 (high), 69245, 67354, 67355, 67353, 67339 — affected `<7.15.2` / `8.0.1`
- **laravel/framework** — CVE-2026-102279 (low) — `<13.30.0`
- **league/commonmark** — CVE-2026-71488, 71478 + advisories sans CVE — `<=2.10.1` / `<2.9.1`
- **league/flysystem** — CVE-2026-102601 — `<=3.35.2`
- **livewire/livewire** — CVE-2026-81887 — `<=4.3.3`
- **phpseclib/phpseclib** — CVE-2026-84308 — `<3.0.57` / `<4.0.1`
- **symfony/http-foundation** — CVE-2026-108082 (ReDoS Cache-Control)
- **symfony/yaml** — CVE-2026-109413 (DoS lexer)

---

## Routes exposées (résumé)

### Cashier (7) — sans `EnsureCashierStaff` / `EnsureCaissierPrincipal` dans `route:list`

Voir CLARIF 4 et P1.3 (`cashier/consultations/*` × 6, `cashier/workflow-pending-counts`).

### API (14) — sans `Authenticate` dans `route:list`

Auth publique (8), catalogue produits (5), `POST api/coinbase/webhook` (middleware signature Coinbase). Détail P1.4.

### Stripe (1)

| Method | URI | Name | Middleware route:list |
|--------|-----|------|------------------------|
| POST | `stripe/webhook` | `cashier.webhook` | `[]` (+ garde Cashier **conditionnelle** C1) |

### Web member (sensibles filtre jq — sans auth/admin/sanctum/kyc)

| Method | URI | Name |
|--------|-----|------|
| GET | `payment/cancel` | `payment.cancel` |
| GET | `payment/success` | `payment.success` |
| GET | `stripe/payment/{id}` | `cashier.payment` |
| POST | `webhook/payment` | `webhook.payment` (signature app, pas auth) |

---

## Annexes P1.2–P1.6

*(Sections P1.2–P1.6 inchangées dans le fond — voir historique git ou version précédente ; middleware, jq API, FormRequests 8/45, Policies 2/82.)*

**P1.5 précision :** 82 fichiers `app/Http/Controllers/**/*.php` ; 45 avec validation inline / `->all()`.

**P1.6 :** Modèles sensibles sans policy ni `authorize` : User, Wallet, Withdrawal, Order, Commission, Rank, Kyc.

---

## C1 — Fiche détaillée (référence)

*(Conservée — preuves `config:show`, `vendor/laravel/cashier/...`, correction STRIPE_WEBHOOK_SECRET, tests Feature, tinker.)*

**Note MAJEUR doc :** `.env.example` incomplet sur Stripe même si prod configurée manuellement.
