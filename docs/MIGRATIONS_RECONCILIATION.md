# Réconciliation des migrations

Date : 2026-10-03  
Statut : **investigation lecture seule** — aucune action exécutée sur MySQL.

## Résumé

| | |
|---|---|
| **Total migrations (fichiers / status)** | 41 |
| **Ran (table `migrations`)** | 24 |
| **Pending (`artisan migrate:status`)** | 17 |

La base prod restaurée contient déjà une grande partie du schéma Laravel, mais la table `migrations` n’enregistre que 24 entrées → Laravel considère 17 migrations comme non jouées.

---

## Migrations pending par catégorie

### À marquer « Ran » (objet déjà présent en base)

| Migration | Objet | Vérification MySQL |
|-----------|--------|-------------------|
| `2026_07_13_082015_create_higher_ranks_table` | table `higher_ranks` | ✅ existe |
| `2026_07_13_082015_create_mlm_settings_table` | table `mlm_settings` | ✅ existe |
| `2026_07_13_082016_create_commission_payments_table` | table `commission_payments` | ✅ existe |
| `2026_07_13_082016_create_commission_periods_table` | table `commission_periods` | ✅ existe (0 lignes) |
| `2026_07_13_082017_create_qualified_branches_table` | table `qualified_branches` | ✅ existe |
| `2026_07_13_082018_create_user_monthly_ranks_table` | table `user_monthly_ranks` | ✅ existe |
| `2026_07_13_082019_create_notifications_table` | table `notifications` | ✅ existe |
| `2026_07_13_101129_add_pv_bv_to_order_items_table` | `order_items.pv_value`, `bv_value` | ✅ présentes |
| `2026_10_03_120000_add_genealogy_and_monthly_pv_to_users_table` | `parrain_id`, `team_pv`, `monthly_pv`, … | ✅ largement présent (schéma hérité du dump) |
| `2026_10_03_130000_create_pv_history_and_mlm_test_schema` | `pv_history` + schéma associé | ✅ `pv_history` (3744 lignes) |
| `2026_07_21_120659_add_flexpay_columns_to_transactions_table` | — | ⚠️ migration **vide** (no-op) |
| `xxxx_xx_xx_add_flexpay_columns_to_transactions_table` | `transactions.order_id`, index | ✅ `order_id` déjà présent |

### À exécuter réellement (manquant)

| Migration | Manque |
|-----------|--------|
| `2026_07_13_082018_create_user_higher_ranks_table` | table **`user_higher_ranks`** absente |
| `2026_10_03_120001_create_personal_access_tokens_table` | table **`personal_access_tokens`** absente (Sanctum API) |

### À exécuter ou marquer après contrôle fin

| Migration | Notes |
|-----------|--------|
| `2026_07_13_082019_add_socialite_columns_to_users_table` | colonnes OAuth (`google_id`, `facebook_id`, …) **non vérifiées / probablement absentes** — exécuter si login social requis |
| `2026_07_13_082321_add_foreign_keys_to_commission_payments` | ajoute FK `commission_period_id` — vérifier contraintes existantes avant `migrate` |
| `2026_07_13_082321_add_foreign_keys_to_user_higher_ranks` | dépend de **`user_higher_ranks`** — après création table |

### Migration invalide / dette technique

| Fichier | Problème |
|---------|----------|
| `xxxx_xx_xx_add_flexpay_columns_to_transactions_table.php` | nom non daté, **doublon** avec `2026_07_21_*` (vide) et schéma déjà partiellement en base |
| `2026_07_21_120659_add_flexpay_columns_to_transactions_table.php` | corps **vide** — inutile en l’état |

---

## Plan d’action proposé (validation humaine requise)

1. **Backup** : `./scripts/backup-db.sh`
2. **Staging** : reproduire l’état prod (dump gzip récent).
3. **Marquer Ran** (INSERT contrôlé dans `migrations`, batch dédié ex. `998`) pour les migrations « table/colonnes déjà là » — **jamais** `migrate:fresh`.
4. **Exécuter** uniquement :
   - `2026_07_13_082018_create_user_higher_ranks_table`
   - `2026_10_03_120001_create_personal_access_tokens_table`
   - `2026_07_13_082019_add_socialite_columns_to_users_table` (si OAuth prod)
   - FK migrations après création `user_higher_ranks`
5. **Nettoyer le repo** : supprimer/renommer `xxxx_xx_xx_*` et la migration FlexPay vide (commit séparé, hors prod).
6. **Vérifier** : `php artisan migrate:status` → tout `Ran`.

---

## Risques

- Lancer `php artisan migrate` sans tri → erreurs **« table already exists »**.
- FK sur tables vides (`commission_payments`) → peut échouer si types/index divergent du dump.
- **Toujours** tester sur copie + backup avant prod.
