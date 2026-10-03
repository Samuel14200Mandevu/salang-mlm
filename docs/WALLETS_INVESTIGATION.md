# Investigation wallets + migrations + dump

Date : 2026-10-03  
**Lecture seule** — aucune modification base / code.

---

## 1. Wallets

### Option identifiée : **A + B (hybride)** — pas C

| Option | Verdict |
|--------|---------|
| **A** — création à la volée (`firstOrCreate` / `create`) | ✅ **Oui**, sur inscription, API, commissions, caisse, admin |
| **B** — `$user->wallet` nullable, affichage **0** si absent | ✅ **Oui**, dashboard / panier / web wallet |
| **C** — solde via `commission_balance` sur `users` | ❌ **Non** — champ initialisé à 0 à l’inscription, **non utilisé** pour l’affichage des soldes |

### Justification (extraits de comportement)

1. **API** — `Api\WalletController@show` : `$user->wallet ?? Wallet::create([... balance => 0 ...])` → **Option A**.
2. **Web membre** — `WalletController@index` : `$wallet = $user->wallet; $balance = $wallet ? $wallet->balance : 0` → **Option B**.
3. **Panier** — `CartController` : `Wallet::where(...)->first()` puis `$wallet ? $wallet->balance : 0` → **Option B**.
4. **Paiement commissions** — `CommissionService` : `Wallet::firstOrCreate` puis `$wallet->balance += $commission->amount` → **Option A** (sur table `commissions`, vide en prod aujourd’hui).
5. **Inscription** — `RegisteredUserController` / `Api\AuthController` : `Wallet::create([...])` pour **nouveaux** comptes seulement.

`commission_balance` : présent sur `User`, rempli à **0** à la création (`RegisteredUserController`, `SocialiteController`, admin) — **pas** la source d’affichage du solde wallet.

### Données prod (MySQL `salang_mlm`)

| Mesure | Valeur |
|--------|--------|
| Lignes `wallets` | **0** |
| `INSERT INTO wallets` dans dump gzip backup | **0** |
| `INSERT INTO wallets` dans `salang_mlm.sql` | **0** |
| `users.commission_balance > 0` | **0** / 2307 |
| `commission_history` (somme `amount`) | **~198 842.79** |
| Users avec `pv_balance > 0` | **1318** |

### Verdict wallets

| | |
|---|---|
| **Perte de données à la restauration ?** | **Non** — le dump n’a jamais contenu de lignes `wallets`. |
| **Bug fonctionnel ?** | **Écart fonctionnel** ⚠️ — les 2307 comptes legacy n’ont pas de ligne `wallets` ; l’UI/API affichent **0** jusqu’à création lazy (souvent avec **balance 0**), sans reprise automatique depuis `commission_history`. |
| **Action future (hors audit)** | Décision métier : backfill wallet depuis historique vs. continuer lazy create ; admin (`Wallet::sum('balance')`) restera à 0 tant qu’aucune ligne n’existe. |

---

## 2. Migrations

Voir le détail : **`docs/MIGRATIONS_RECONCILIATION.md`**.

**Synthèse**

| Catégorie | Nombre (approx.) |
|-----------|------------------|
| Pending « déjà en base » → marquer Ran | ~12 |
| Pending « à exécuter » | **2 tables** (`user_higher_ranks`, `personal_access_tokens`) |
| Pending « socialite / FK » | 3 (après contrôle) |
| Invalides / doublons | `xxxx_xx_xx_*`, FlexPay vide |

---

## 3. Dump vs base restaurée

Estimation des lignes par parsing des blocs `INSERT INTO ... VALUES` :

| Source | `users` | `commission_history` | `wallets` |
|--------|---------|----------------------|-----------|
| **`salang_mlm.sql`** (Documents/salang, ~4,7 Mo) | ~26 blocs / parse partiel* | ~50 blocs / parse partiel* | **0** |
| **Backup gzip** `salang_mlm_PROD_20261003_181029` | **~2307** | **~11854** | **0** |
| **MySQL live** (2026-10-03) | **2307** | **11854** | **0** |

\*Le fichier SQL plat utilise des `INSERT` multi-lignes ; un parseur naïf sous-estime le fichier Documents. **Référence fiable** : backup gzip du jour **= base live** (2307 / 11854 / 0 wallets).

### Interprétation

- **Pas de perte** entre backup récent et MySQL actuel pour `users` et `commission_history`.
- **`wallets` vide dans toutes les sources** → cohérent, pas une régression d’import.
- Conserver **`~/Backups_Salang/`** comme référence avant toute réconciliation migrations.

---

## Recommandations

1. **Valider métier** : le solde « officiel » est-il `commission_history` / exports legacy, ou la table `wallets` Laravel ?
2. Si `wallets` est requis en prod : planifier **backfill** (script dédié, staging, backup) — **pas** un seed aveugle.
3. **Réconciliation migrations** selon `MIGRATIONS_RECONCILIATION.md` (staging d’abord).
4. Créer **`personal_access_tokens`** avant d’activer l’API mobile Sanctum en prod.
5. Créer **`user_higher_ranks`** si les grades supérieurs sont utilisés en prod.

---

*Aucune modification MySQL, migration ou seed effectuée lors de cette investigation.*
