# Changelog (corrections audit MLM)

## 2026-10-03 — Perf bundle frontend

- Chart.js extrait dans `resources/js/charts.js` (entrée Vite séparée, import dynamique sur `admin/reports`).
- Suppression de l’import global Flowbite (aucun usage `data-*` Flowbite dans les vues).
- Bundle initial `app.js` ~97 KB ; chunk `charts.js` ~202 KB à la demande.

## 2026-10-03 — Frontend P1 + design anti-vibe-coding

### Phase 1 (critique)
- Un seul `@vite` par layout ; suppression du double chargement JS.
- Alpine, Chart.js et client `resources/js/api.js` (Sanctum) dans le bundle Vite.
- `showToast` sécurisé (escape HTML) ; caisse réutilise le toast global.
- Viewport sans `maximum-scale=1` ; manifest PWA harmonisé (`#5ab638`).
- Axios réel dans `bootstrap.js` ; HMR via `VITE_HMR_HOST`.
- Lazy loading sur les `<img>` (hors logos LCP).

### Phase 2 (design)
- Palette Tailwind Salang (vert) ; suppression indigo SaaS dans les vues.
- Police unique Plus Jakarta Sans via `app.css`.
- Landing : grille asymétrique « Pourquoi Salang » ; footer légal membre.

### Phase 3 (composants)
- Composants Blade `resources/views/components/ui/*` (button, card, alert, page-header, stat).

## 2026-10-03

### Critique
- **BUG-007** : `CommissionService::calculatePackageCommission()` ne double plus le PV ; période via `CommissionPeriod` + `MlmPeriod`.
- **BUG-008** : helper `App\Support\MlmPeriod` ; remplacement de `date('Y-m')` dans `app/`.

### Majeur
- **BUG-012** : trait `DispatchesMlmRecalculation` remplace les triples dispatch `UpdateTeamPV` / `UpdateRanks` / `CalculatePVBV` dans les contrôleurs d’achat/activation.
- **BUG-013** : injection de `CommissionService` dans `CryptoPaymentService`.
- **BUG-014** : migrations `parrain_id`, PV mensuels, `personal_access_tokens` ; tests avec sqlite + `RefreshDatabase` / seed permissions.
- **BUG-015** : suppression de `RankService` et `RankCalculationService` (non utilisés).

### Mineur
- **BUG-019** : suppression du fichier stray `et-monthly-pv')`.
