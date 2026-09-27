# Système MLM - Architecture

> Version 2.0 — Septembre 2026

## Règle métier unique

- `team_pv` = `pv_balance` + SUM(descendants.pv_balance)
- `team_bv` = `bv_balance` + SUM(descendants.bv_balance)
- `total_team` = nombre de descendants actifs

⚠️ `team_pv` INCLUT le `pv_balance` personnel.

## Source unique de vérité

- `App\Services\MLM\TeamPVCalculator` → calcul team_pv/bv/total_team
- `App\Services\MLM\AdvancedRankCalculator` → calcul des grades
- `App\Services\MLM\RankConditionChecker` → vérification des conditions
- `App\Jobs\RecalculateAfterPVImport` → job unifié post-modification

## Flux temps réel

Modification d'un user (via Eloquent)
    ↓
User::booted() → dispatch RecalculateAfterPVImport
    ↓
RecalculateAfterPVImport → TeamPVCalculator + AdvancedRankCalculator
    ↓
team_pv + rank mis à jour pour l'user + ancêtres

## Filet de sécurité

Schedule: mlm:fix-team-pv --dry-run (hourly)
- Détecte les incohérences
- Log dans storage/logs/mlm-health.log
- Ne modifie rien

## Commandes Artisan

- `php artisan mlm:fix-team-pv --dry-run` → Vérifier les incohérences
- `php artisan mlm:fix-team-pv` → Corriger manuellement
- `php artisan mlm:fix-team-pv --fix-cycles` → Casser les cycles auto
- `php artisan mlm:status` → Statut du système MLM

## Jobs obsolètes (redirigent vers le nouveau système)

- `UpdateTeamPV` → TeamPVCalculator
- `UpdateRanks` → AdvancedRankCalculator
- `CalculatePVBV` → RecalculateAfterPVImport
- `UpdateCumulativePV` → RecalculateAfterPVImport

⚠️ Ne PAS dispatcher ces jobs directement. Utiliser `RecalculateAfterPVImport`.

## Bonnes pratiques

### À FAIRE
- Modifier les users via Eloquent (web, API, tinker, controller)
- Lancer `php artisan mlm:fix-team-pv` 1× par mois (vérification)
- Surveiller `storage/logs/mlm-health.log`
- Garder les 2 workers actifs

### À ÉVITER
- UPDATE SQL direct sur pv_balance/team_pv/rank_* sans relancer fix-team-pv
- Dispatcher UpdateTeamPV/UpdateRanks/CalculatePVBV directement

## Workers à maintenir actifs

```bash
php artisan queue:work --queue=default --tries=3 --timeout=3600
php artisan queue:work --queue=rank-recalculation --tries=1 --timeout=3600