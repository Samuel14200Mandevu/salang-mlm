# Pilote Prod — Checklist de lancement

**Projet :** Salang MLM · **Branche :** `sprint-0/audit-critical-fixes` (PR #3)  
**Objectif :** ouverture pilote caisse + consultations + lecture admin commissions, sans clôture mensuelle « argent réel » complète (Sprint 1).

---

## Pré-merge

- [ ] CI PR #3 verte (PHPUnit + Dusk + Vite)
- [ ] Tests locaux : **110 passed**, **1 skipped**
- [ ] Doublons commissions prod : requête SQL `GROUP BY order_id, type HAVING COUNT(*) > 1` → **0** (sinon dédoublonner avant migration unique)
- [ ] Backup prod fait (timestamp: ____________)

---

## Merge

- [ ] PR #3 mergée sur `main`
- [ ] Deploy auto Laravel Cloud (`Samuel14200Mandevu/salang-mlm`, branche `main`) — vérifier logs deploy

---

## Config Cloud (avant ou pendant deploy, avant `migrate`)

- [ ] `MLM_DEMO_MODE=false`
- [ ] `STRIPE_WEBHOOK_SECRET` configuré (si Stripe / Cashier actif en prod)
- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`

Vérifications rapides (Laravel Cloud command ou tinker) :

```bash
php artisan tinker --execute="echo config('mlm.demo_mode_enabled') ? 'DEMO_ON' : 'DEMO_OFF';"
php artisan tinker --execute="echo config('cashier.webhook.secret') ? 'STRIPE_OK' : 'STRIPE_NULL';"
```

---

## Migration

- [ ] `php artisan migrate --force` (inclus deploy ou manuel si doute : env Cloud documenté dans `docs/ROUTINE_FIN_DE_JOURNEE.md`)
- [ ] `php artisan migrate:status` → `2026_10_17_160000` (unique `order_id` + `type`) **Ran**
- [ ] `php artisan migrate:status` → `2026_10_17_170000` (enum `failed` commission_periods) **Ran**
- [ ] `config('mlm.demo_mode_enabled')` → **false**
- [ ] `config('cashier.webhook.secret')` → **non null** (si encaissement Stripe en ligne)

**Ne pas** : `migrate:fresh`, copie SQLite → MySQL prod, seeders non idempotents sans validation.

---

## Smoke test (prod ou staging identique)

| # | Action | Résultat attendu |
|---|--------|------------------|
| 1 | `curl -sf https://salang-group-mlm.laravel.cloud/up` | **200** |
| 2 | Login **admin** → `/admin/pv/commission-history` | **200** (lecture) |
| 3 | Login **admin** → POST recalculate (sans demo) | **403** (bloqué) |
| 4 | Login **caissier** → `/cashier/checkout` | **200** |
| 5 | Checkout test (panier vide) | Erreur métier (redirect + flash ; pas de commande créée) |
| 6 | Checkout test (panier valide, 1 clic) | Commande `completed`, **1** ligne `cash_pos` par commande (types multiples OK) |
| 7 | Double-clic checkout (2 POST rapides, même client/téléphone) | **1** commande ; 2ᵉ → redirect commande existante (BB1-rapide) |
| 8 | Consultation → sélection → checkout | Commande avec `consultation_id` renseigné |

---

## Monitoring 24 h

- [ ] Logs erreurs applicatives : **0** critique récurrent
- [ ] `commission_periods` : pas de `calculating` bloqué > 1 h (BA14a)
- [ ] Jobs queue en échec : **0** non traités
- [ ] Wallet crédits : vérifier manuellement **3** commandes pilote (règle métier : crédit immédiat vs validation admin — à documenter avec finance)

---

## Brief caissiers

- [ ] **« 1 clic checkout »** — ne pas spammer « Valider » (garde 2 min + bouton désactivé au clic)
- [ ] **« Wallet parrain »** — expliquer quand le solde apparaît (selon décision métier post-pilote)
- [ ] **Signalement** — canal défini pour tout comportement anormal (double facture, commission manquante, etc.)

---

## Références

- Audit & bilan : [`AUDIT.md`](../AUDIT.md) § *Sprint 0 + BB1-rapide — Bilan final*
- Routine fin de journée : [`docs/ROUTINE_FIN_DE_JOURNEE.md`](ROUTINE_FIN_DE_JOURNEE.md)
- Deploy : [`docs/DEPLOYMENT.md`](DEPLOYMENT.md)

---

*Dernière mise à jour : 2026-10-17 — Sprint 0 + BB1-rapide, CI PR #3 verte.*
