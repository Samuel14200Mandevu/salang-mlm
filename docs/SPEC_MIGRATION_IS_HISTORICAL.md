# Spec — Migration `is_historical` et bandes A / B / C

**Phase 2 — spec uniquement (aucune exécution en prod sans validation Samuel).**

## Contexte

- `commission_history` = backfill unique du **2026-10-01**, pas un journal temps réel.
- **34** périodes `2023-11` → `2026-08`, toutes `commission_periods.status = calculated`, wallets vides.
- Décision prudente :
  - **Bande A** (`2023-11` → `2024-08`, 10 périodes) : déjà payé hors système → marquer **historique** (`paid` sans wallet).
  - **Bande B** (`2024-09`, 1 période) : **premier** paiement via `generate-payments` (spec paiement séparée / runbook).
  - **Bande C** (`2024-10` → `2026-08`, 23 périodes) : **masquer** UI + rester `pending`, **interdit** de payer en masse tant que le métier n’a pas tranché mois par mois.

---

## Migration

Fichier proposé : `database/migrations/2026_10_03_160000_add_historical_flags_to_commission_periods_table.php`

```php
Schema::table('commission_periods', function (Blueprint $table) {
    $table->boolean('is_historical')->default(false)->after('status');
    $table->timestamp('paid_offline_at')->nullable()->after('is_historical');
    $table->boolean('is_hidden')->default(false)->after('paid_offline_at');
});
```

### `down()`

```php
Schema::table('commission_periods', function (Blueprint $table) {
    $table->dropColumn(['is_historical', 'paid_offline_at', 'is_hidden']);
});
```

### Index (recommandé)

```php
$table->index(['is_hidden', 'period']);
$table->index('is_historical');
```

Pas de migration sur `commissions` ni `commission_history` : le statut ligne **`paid`** / **`pending`** reste la source pour le détail ; les flags période pilotent pipeline et agrégats UI.

---

## Sémantique des colonnes

| Colonne | Type | Défaut | Rôle |
|---------|------|--------|------|
| `is_historical` | bool | `false` | Période réglée **hors système** ; **ne jamais** appeler `generate-payments`. |
| `paid_offline_at` | timestamp nullable | `null` | Horodatage du marquage admin (audit « payé offline »). |
| `is_hidden` | bool | `false` | Exclure de listes dashboard / API membre (bande C). Admin peut voir avec filtre explicite. |

### Invariants métier

| Bande | Périodes | `is_historical` | `is_hidden` | `commission_periods.status` cible | Lignes `commissions` / `history` | Wallet |
|-------|----------|-----------------|-------------|-----------------------------------|-------------------------------------|--------|
| **A** | `2023-11` … `2024-08` | `true` | `false` | **`paid`** | **`paid`**, `paid_at` = `paid_offline_at` (ou batch) | **Aucun** crédit |
| **B** | `2024-09` | `false` | `false` | `calculated` → `paid` **via** paiement | `pending` → `paid` via pipeline | Crédit **normal** |
| **C** | `2024-10` … `2026-08` | `false` | `true` | **`calculated`** (inchangé) | **`pending`** | **Aucun** jusqu’à décision |

`total_paid` bande A : **`0.00`** (argent déjà versé hors app ; option doc : note dans `notes` avec montant offline de référence = `total_commissions`).

---

## Backfill initial (spec SQL — après migration)

Exécution **manuelle**, **backup obligatoire**, transaction recommandée.

### Bande A — flags période

```sql
UPDATE commission_periods
SET
    is_historical = 1,
    is_hidden = 0,
    paid_offline_at = COALESCE(paid_offline_at, NOW()),
    status = 'paid',
    payment_date = COALESCE(payment_date, paid_offline_at, NOW()),
    total_paid = 0.00,
    notes = CONCAT(
        COALESCE(notes, ''),
        ' | Marquage historique offline (bande A). Montant de référence: ',
        total_commissions,
        ' USD. Aucun crédit wallet.'
    )
WHERE period >= '2023-11' AND period <= '2024-08';
```

### Bande C — masquage seulement

```sql
UPDATE commission_periods
SET is_hidden = 1, is_historical = 0
WHERE period >= '2024-10' AND period <= '2026-08';
```

### Bande B — explicite (idempotence)

```sql
UPDATE commission_periods
SET is_historical = 0, is_hidden = 0
WHERE period = '2024-09';
```

---

## Marquage bande A — lignes (sans wallet)

Commande Artisan **à implémenter** (nom proposé) :  
`commissions:mark-offline-paid --from=2023-11 --to=2024-08 --dry-run`

Comportement attendu :

1. Refuser si `commission_payments` ou `transactions` type `commission` existent pour ces périodes (sécurité).
2. Pour chaque `commission_period` avec `is_historical = true` (ou plage validée) :
   - `Commission::where(commission_period_id)->where('status','pending')->update(['status'=>'paid','paid_at'=>$ts])`
   - `commission_history` : `status = paid`, `paid_at` aligné (même `(user_id, period)` que sync).
3. **Ne pas** créer `CommissionPayment`, **ne pas** toucher `wallets` / `transactions`.
4. `--dry-run` : comptages par période + delta USD.

Ordre recommandé prod :

1. Backup  
2. `migrate` colonnes  
3. Backfill flags périodes (SQL ou commande dédiée)  
4. `commissions:mark-offline-paid` (réel)  
5. Vérifications lecture seule (§ Vérification)

---

## Garde-fous pipeline paiement

### `MonthlyCommissionService::generatePayments`

Avant tout traitement :

```text
if ($period->is_historical) → log + return false (exception métier documentée)
if ($period->is_hidden)     → log + return false (bande C non payable via UI batch)
```

Conserver la règle existante : `status === 'calculated'` uniquement.

### `commissions:simulate-payments`

- Exclure périodes `is_historical` ou `is_hidden` par défaut.
- Option `--include-hidden` réservée admin (debug).

### Sync `commissions:sync-from-history`

- Inchangé pour l’historique déjà syncé.
- Nouvelles périodes futures : appliquer `is_hidden` par défaut si `period >` période MLM « ouverte au paiement » (règle configurable, Phase ultérieure).

---

## Affichage — trois bandes (dashboard + API)

### Principes UX

| Zone | Bande A | Bande B | Bande C |
|------|---------|---------|---------|
| Liste périodes membre | Visible, badge **« Payé (historique) »** | Visible, **« À payer / En cours »** selon statut | **Absente** (`is_hidden`) |
| Solde wallet | Inchangé (0 tant que pas de paiement B) | Crédité après `generate-payments` | N/A |
| Total « commissions payées » (lifetime) | Inclure montants A en **paid offline** (lecture `commissions.status=paid` sans transaction wallet) | Inclure après paiement système | **Exclure** des totaux « dus » et listes |
| Admin | Toutes périodes + filtres `is_historical`, `is_hidden` | Idem | Idem, bande C visible admin |

### Filtres requêtes (spec)

**Membre — liste commissions / périodes**

```text
CommissionPeriod::query()
    ->where('is_hidden', false)
    ...
```

**Membre — agrégats « en attente » (wallet / pending)**

```text
Commission::pending()
    ->whereHas('commissionPeriod', fn ($q) => $q->where('is_hidden', false)->where('is_historical', false))
```

**Membre — agrégats « payé » (inclut historique A)**

```text
Commission::paid()
    ->whereHas('commissionPeriod', fn ($q) => $q->where('is_hidden', false))
```

Option API : champ `payment_source` sur ressource période ou commission :

- `system` — `paid` + transaction wallet / `commission_payments`
- `offline_historical` — `paid` + période `is_historical`
- `pending` — bande B avant paiement

### Fichiers code à toucher (implémentation future)

| Fichier | Changement |
|---------|------------|
| `app/Models/CommissionPeriod.php` | `$fillable`, `$casts`, scopes `visibleToMember()`, `historical()`, `hidden()` |
| `app/Services/MLM/MonthlyCommissionService.php` | Garde-fous `generatePayments` |
| `app/Http/Controllers/Api/CommissionController.php` | Filtre `is_hidden` |
| `app/Http/Controllers/Api/DashboardController.php` | Totaux 3 bandes |
| `app/Http/Resources/CommissionResource.php` | `payment_source` (optionnel) |
| Vues admin commissions | Filtres + action « Marquer offline » (bande A) |

---

## Modèle `CommissionPeriod` (extrait spec)

```php
protected $fillable = [
    // ... existant ...
    'is_historical',
    'paid_offline_at',
    'is_hidden',
];

protected $casts = [
    // ... existant ...
    'is_historical' => 'boolean',
    'is_hidden'     => 'boolean',
    'paid_offline_at' => 'datetime',
];

public function scopeVisibleToMember($query)
{
    return $query->where('is_hidden', false);
}

public function isPayableViaSystem(): bool
{
    return ! $this->is_historical
        && ! $this->is_hidden
        && $this->status === 'calculated';
}
```

---

## Vérification (lecture seule, post-marquage A)

```sql
-- A : périodes paid, historiques, total_paid wallet = 0
SELECT period, status, is_historical, is_hidden, total_commissions, total_paid
FROM commission_periods
WHERE period BETWEEN '2023-11' AND '2024-08';

-- A : plus de pending sur commissions
SELECT period, status, COUNT(*), SUM(amount)
FROM commissions
WHERE period BETWEEN '2023-11' AND '2024-08'
GROUP BY period, status;

-- B : toujours calculated, flags false
SELECT * FROM commission_periods WHERE period = '2024-09';

-- C : hidden, pending
SELECT period, is_hidden, status,
       (SELECT COUNT(*) FROM commissions c WHERE c.commission_period_id = cp.id AND c.status = 'pending') AS n_pending
FROM commission_periods cp
WHERE period >= '2024-10';

-- Aucun wallet créé par erreur
SELECT COUNT(*) FROM wallets;
SELECT COUNT(*) FROM transactions WHERE type = 'commission';
```

Volumes de référence (prod post-sync, pré-marquage) :

| Bande | Périodes | Lignes | Brut USD |
|-------|----------|-------:|---------:|
| A | 10 | 3 434 | 68 708,41 |
| B | 1 | 387 | 5 290,19 |
| C | 23 | 8 033 | 124 844,19 |

---

## Critères d’acceptation Phase 2

- [ ] Migration appliquée sans perte de données ; rollback testé sur staging.
- [ ] Bande A : 10 périodes `paid` + `is_historical=1`, commissions/history `paid`, **0** transaction commission.
- [ ] Bande B : `2024-09` payable via simulate + `generate-payments` (Phase suivante).
- [ ] Bande C : `is_hidden=1`, tout reste `pending`, absent des écrans membre.
- [ ] `generate-payments` refuse A et C avec message explicite.
- [ ] Dashboard distingue payé historique vs payé système vs pending (B).

---

## Liens

- Runbook sync / paiement : `docs/OPS_RUNBOOK_SYNC_PAYMENT.md`
- Investigation wallets : `docs/WALLETS_INVESTIGATION.md`

---

## Hors scope (cette spec)

- Paiement réel bande B (Phase 5 ciblée `2024-09` uniquement).
- Reclassification bande C période par période (démasquer + payer).
- Nouveau statut enum `commission_history` (`paid_offline`) — **non requis** si `is_historical` sur période + `paid` ligne suffit.
