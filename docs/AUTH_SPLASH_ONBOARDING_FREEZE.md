# Gel auth / splash / onboarding

État figé : **commit `1f60e51`** (matin ~8h du 11 novembre 2026).

## Contenu gelé

Voir `.cursor/rules/auth-splash-onboarding-freeze.mdc`.

## Protection deploy

Le workflow GitHub **Freeze auth/splash/onboarding** compare ces chemins à `1f60e51`.

- PR qui les modifie → **CI rouge**, sauf label `allow-auth-splash` (uniquement si l’utilisateur l’a demandé explicitement).
- Push `main` qui les modifie → **CI rouge** (doit rester aligné).

## Restaurer

```bash
git checkout 1f60e51 -- \
  resources/views/layouts/auth.blade.php \
  resources/views/layouts/public.blade.php \
  resources/css/public-mobile.css \
  resources/js/public-splash.js \
  resources/js/public-onboarding.js \
  resources/views/welcome.blade.php \
  resources/views/partials/public \
  resources/views/partials/auth \
  resources/views/auth
```
