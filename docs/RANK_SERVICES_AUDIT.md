# Audit des services de grades

Date : 2026-10-03  
Auteur : Cursor (assisté)  
Statut : **AUDIT — aucune modification de code**

## Objectif

Documenter l’état des services de calcul de rang après suppression des doublons historiques, et préparer une éventuelle session de migration / non-régression.

## Inventaire des fichiers Rank (app/)

| Fichier | Rôle |
|---------|------|
| `app/Services/MLM/AdvancedRankCalculator.php` | Calcul de rang avancé (source de vérité) |
| `app/Services/MLM/RankUpdateService.php` | Orchestration mise à jour rangs (utilise `AdvancedRankCalculator`) |
| `app/Services/MLM/RankConditionChecker.php` | Conditions d’éligibilité par rang |
| `app/Services/MLM/TeamPVCalculator.php` | PV d’équipe |
| `app/Jobs/UpdateRanks.php` | Job legacy → délègue à `AdvancedRankCalculator` |
| Commandes : `RecalculateAllRanks`, `ForceUpdateRanks`, `FixAllRanks`, etc. | CLI maintenance |

### RankService

- **Fichier** : `app/Services/RankService.php` — **supprimé** (voir CHANGELOG BUG-015).
- **Appels restants** : aucun dans `app/` ou `routes/`.

### RankCalculationService

- **Fichier** : `app/Services/RankCalculationService.php` — **supprimé** (CHANGELOG BUG-015).
- **Appels restants** : aucun dans `app/` ou `routes/`.

### AdvancedRankCalculator

- **Fichier** : `app/Services/MLM/AdvancedRankCalculator.php`
- **Méthodes publiques principales** :
  - `calculateAdvancedRank`, `updateTeamPV`
  - `recalculateUserRank`, `recalculateUserRankLight`, `recalculateUserRankWithAncestors`
  - `isEligibleForRank`, `calculatePrizes`, `getProgress`
  - `calculateQualifiedBranches`, `checkHigherRankEligibility`, `getCurrentHigherRank`
  - `countLevel9Branches`, `countDiamondBranches`, `clearCache`
- **Appelé par** (extrait) : `User` (modèle), `RankUpdateService`, `CommissionService`, `MonthlyCommissionService`, contrôleurs Admin/User/Rank/Activation/Cart, jobs `RecalculateAfterPVImport`, commandes `ForceUpdateRanks`, `FixAllRanks`, etc.

## Distribution des rangs (utilisateurs actifs, prod 2026-10-03)

| Rang | Utilisateurs |
|------|-------------:|
| Distributeur | 1165 |
| Cumul Directeur | 803 |
| Directeur | 196 |
| Qualification | 82 |
| Manager Senior | 46 |
| Directeur Envolée | 11 |
| Saphire Manager | 4 |

Total actifs snapshot : **2307** (voir `docs/snapshots/rang_snapshot_20261003.tsv`).

## Risques identifiés

- **Documentation obsolète** : `docs/STRUCTURE.txt` mentionne encore `RankService.php`.
- **Double chemin métier** : plusieurs commandes (`FixAllRanks`, `ForceUpdateRanks`, `RecalculateAllRanks`) peuvent recalculer les rangs ; vérifier qu’elles passent toutes par `AdvancedRankCalculator` / `RankUpdateService` (audit code OK à ce stade, pas de second moteur de calcul).
- **Logs verbeux** : `AdvancedRankCalculator` logue chaque vérification de rang (volume en prod si recalcul massif).

## Recommandations (session dédiée)

1. Mettre à jour `docs/STRUCTURE.txt` et toute doc interne référençant `RankService`.
2. Ajouter tests de non-régression comparant snapshot TSV vs recalcul sur échantillon d’IDs.
3. Documenter une seule commande « officielle » de recalcul prod (`ranks:update` / `RecalculateAllRanks`).
4. Snapshot avant/après obligatoire sur les 2307 users avant tout changement algorithme.

## Décision

**Migration / refactor différés** — les anciens services dupliqués sont déjà retirés ; le risque restant est l’orchestration (CLI/jobs) et la doc, pas un second calculateur actif.
