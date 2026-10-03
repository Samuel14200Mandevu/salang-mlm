# Runbook — Sync `commission_history` → paiements (Phase 5)

Décisions : **D1=B** (KYC off temporaire), **D2=A** (34 périodes une par une),
**D3=B** (history → `paid` après crédit), **D4=B** (deferred, pas skip silencieux),
**D5=A** (colonne `source_commission_history_id` UNIQUE).

**Prérequis code** : commit `593799b` ou ultérieur.

---

## 1. État de référence (prod attendue avant Phase 5)

| Métrique | Valeur |
|----------|--------|
| `users` | 2307 |
| `commission_history` | 11854 (pending) |
| `commissions` | 0 |
| `commission_periods` | 0 |
| `wallets` | 0 |
| Brut history | 198 842,79 USD |
| Périodes distinctes | **34** (2023-11 → 2026-08) |

---

## 2. Tâche 1 — Migration `150000` : choix

### Option A — Migrer **avant** dry-run sync « complet » (recommandé Phase 5)

```bash
./scripts/backup-db.sh
php artisan migrate --path=database/migrations/2026_10_03_150000_add_source_commission_history_id_to_commissions_table.php --force
php artisan migrate:status --no-interaction | grep 150000   # Ran
```

- Dry-run sync peut détecter les lignes déjà importées (`skipped`).
- Sync réel requiert cette colonne.

### Option B — Dry-run **sans** migration (Phase 4)

- Depuis le patch Phase 4 : `commissions:sync-from-history --dry-run` compte **11854 créations** sans lire la colonne.
- **Sync réel impossible** tant que la migration n’est pas appliquée.
- Phase 5 commence toujours par **Option A** avant tout sync sans `--dry-run`.

**Ne pas migrer sans backup.** Samuel valide explicitement Option A avant exécution.

---

## 3. `CommissionHistoryPaymentSync` (D3 — pas de commande Artisan)

Service : `App\Services\MLM\CommissionHistoryPaymentSync`.

| Méthode | Rôle |
|---------|------|
| `markPaidForUserPeriod($userId, $period)` | Après crédit wallet dans `generatePayments`, aligne `commission_history.status` → **`paid`**. |

**Logique**

1. Si des `commissions` **paid** existent avec `source_commission_history_id` → met **`paid`** uniquement ces lignes history.
2. Sinon (fallback) → met **`paid`** toutes les lignes history **pending** pour `(user_id, period)`.

Appelé automatiquement depuis `MonthlyCommissionService::generatePayments` après
`Commission::…->update(['status' => 'paid'])` pour l’utilisateur.

**Important** : les users **deferred** (KYC, PV, min, inactif, sans rank) ne passent pas en `paid` dans `commissions` ni dans history.

---

## 4. Variables d’environnement (D1 — rattrapage historique)

```env
COMMISSION_REQUIRE_KYC=false
COMMISSION_TAX_RATE=5
COMMISSION_MIN_PAYMENT=1
COMMISSION_REQUIRE_MONTHLY_PV=true
COMMISSION_REQUIRE_ACTIVE_ACCOUNT=true
COMMISSION_REQUIRE_RANK=true
```

Après rattrapage : remettre `COMMISSION_REQUIRE_KYC=true` + campagne KYC.

`php artisan config:clear` après changement `.env`.

---

## 5. Phase 4 — Dry-runs (lecture / simulation)

```bash
./scripts/sync-payment-preflight.sh
./scripts/sync-payment-dry-run.sh
# Après migration 150000 uniquement pour simulate fiable :
php artisan commissions:simulate-payments --period=2023-11
```

`commissions:simulate-payments` exige des `commission_periods` en **`calculated`** et des lignes `commissions` pending → **après** sync d’au moins une période (ou dry-run global sync pour volumétrie).

---

## 6. Phase 5 — Ordre strict (une période à la fois)

Pour chaque période `YYYY-MM` **dans l’ordre chronologique** :

```bash
export PERIOD=2023-11   # exemple

./scripts/backup-db.sh

# 1) Sync période (première fois : migration 150000 déjà Ran)
php artisan commissions:sync-from-history --period="$PERIOD" --ensure-periods

# 2) Vérifications SQL (lecture)
# SELECT COUNT(*) FROM commissions WHERE period='$PERIOD';
# SUM(amount) history vs commissions pour la période

# 3) Simulation paiement
php artisan commissions:simulate-payments --period="$PERIOD"

# 4) Paiement réel (si simulate OK)
php artisan commissions:generate-payments "$PERIOD"

# 5) Contrôles post-paiement
# commission_periods.status = paid
# wallets / transactions pour users crédités
# commission_history.status = paid pour users payés
# users count still 2307
```

**Piège** : ne jamais lancer `generate-payments` sur une période **sans** commissions pending (marque `paid` à 0).

**Interdit** sur périodes syncées : `commissions:calculate-period --full`, `commissions:process` (double crédit).

---

## 7. Liste des 34 périodes (history)

| Période | Lignes | Brut USD |
|---------|-------:|---------:|
| 2023-11 | 103 | 2 987,94 |
| 2023-12 | 163 | 4 566,53 |
| 2024-01 | 513 | 10 311,66 |
| 2024-02 | 2 | 80,00 |
| 2024-03 | 433 | 9 425,40 |
| 2024-04 | 285 | 5 273,58 |
| 2024-05 | 377 | 7 789,57 |
| 2024-06 | 494 | 8 908,16 |
| 2024-07 | 459 | 7 929,92 |
| 2024-08 | 605 | 11 435,65 |
| 2024-09 | 387 | 5 290,19 |
| 2024-10 | 465 | 7 154,34 |
| 2024-11 | 471 | 7 245,14 |
| 2024-12 | 361 | 7 288,80 |
| 2025-01 | 418 | 11 249,05 |
| 2025-02 | 90 | 1 558,37 |
| 2025-03 | 332 | 7 663,87 |
| 2025-04 | 144 | 1 828,57 |
| 2025-05 | 480 | 8 414,17 |
| 2025-06 | 278 | 9 057,66 |
| 2025-07 | 274 | 4 925,62 |
| 2025-08 | 85 | 1 030,75 |
| 2025-09 | 56 | 781,27 |
| 2025-10 | 161 | 2 326,49 |
| 2025-11 | 168 | 2 704,56 |
| 2025-12 | 174 | 2 886,79 |
| 2026-01 | 450 | 6 554,68 |
| 2026-02 | 435 | 6 245,83 |
| 2026-03 | 530 | 5 677,61 |
| 2026-04 | 565 | 6 767,61 |
| 2026-05 | 517 | 6 705,18 |
| 2026-06 | 527 | 5 791,04 |
| 2026-07 | 485 | 5 042,96 |
| 2026-08 | 567 | 5 943,83 |
| **Total** | **11854** | **198 842,79** |

---

## 8. Rollback (urgence)

1. **Restaurer backup gzip** (voir `docs/BACKUP.md`) — seule voie sûre si sync/payment partiel.
2. **Ne pas** `migrate:rollback` seul si des données métier ont été écrites.
3. Rollback ciblé (expert, après backup) :
   - Supprimer `commissions` où `source_commission_history_id IS NOT NULL`
   - Supprimer `commission_payments` / `transactions` type commission créés après date T
   - Remettre `commission_periods` supprimées ou statuts
   - Remettre `commission_history.status` → `pending` si history marquée paid par erreur
   - Ajuster `wallets` manuellement **uniquement** avec trace comptable

Documenter l’heure et la période en cours avant toute action.

---

## 9. Checklist go / no-go Phase 5

- [ ] Backup récent validé (users=2307, history=11854)
- [ ] Migration 150000 **Ran**
- [ ] `COMMISSION_REQUIRE_KYC=false` confirmé (D1)
- [ ] Dry-run sync global OK (11854 créations simulées)
- [ ] Plan période par période imprimé (table §7)
- [ ] Fenêtre maintenance communiquée
- [ ] Rollback testé sur staging (si disponible)
