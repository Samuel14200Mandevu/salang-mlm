# API REST — Salang MLM

Base URL : `/api`

## Enveloppe de réponse

```json
{
  "success": true,
  "data": {},
  "message": null,
  "errors": null
}
```

Certaines routes conservent des champs legacy au niveau racine (ex. `stats` sur `/commissions/stats`, `balance` sur `/wallet`).

## Authentification (Sanctum)

| Méthode | Route | Auth | Description |
|---------|-------|------|-------------|
| POST | `/auth/register` | Non | Crée un compte **inactif** (pas de token) |
| POST | `/auth/login` | Non | Email + mot de passe → token Bearer si `is_active` |
| POST | `/auth/logout` | Bearer | Révoque le token courant |
| GET | `/auth/me` | Bearer | Utilisateur connecté (`UserResource`) |

Alias legacy : `POST /login`, `POST /register`, `POST /logout`.

Header : `Authorization: Bearer {token}`

## Utilisateur

| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/user` | Profil |
| PUT | `/user` | Mise à jour (champs autorisés) |
| GET | `/user/stats` | Statistiques membre (si enregistré) |

## Dashboard

| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/dashboard/stats` | KPIs (PV, équipe, wallet, rang) |
| GET | `/dashboard/chart` | 501 — non implémenté |

## Réseau

| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/network/stats` | Statistiques réseau |
| GET | `/network/tree` | Arbre généalogique |
| GET | `/network/downlines` | Descendants paginés |
| GET | `/network/search` | 501 — non implémenté |

## Wallet

| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/wallet` | Solde + `WalletResource` (+ `balance`, `pending_balance` legacy) |
| GET | `/wallet/transactions` | Historique paginé |
| POST | `/wallet/withdraw` | Délégation retrait |

## Commissions

Routes **statiques avant** `{id}` :

| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/commissions/export` | Export JSON ou `?format=csv` |
| GET | `/commissions/stats` | Agrégats (`stats` racine + `data.stats`) |
| GET | `/commissions` | Liste paginée (`data[]` + `pagination`) |
| GET | `/commissions/{id}` | Détail |

## Webhooks (web, pas `/api`)

Préfixe : `POST /webhook/{provider}` — CSRF désactivé, middleware `webhook.verify:{provider}`.

| Provider | Route | Secret env |
|----------|-------|------------|
| flexpay | `/webhook/flexpay` | `FLEXPAY_WEBHOOK_SECRET` |
| crypto | `/webhook/crypto` | `CRYPTO_WEBHOOK_SECRET` |
| mobile_money | `/webhook/mobile-money` | `MOBILE_MONEY_WEBHOOK_SECRET` |
| payment | `/webhook/payment` | `PAYMENT_WEBHOOK_SECRET` |

Signature HMAC SHA256 du corps brut, en-tête `X-Webhook-Signature` (ou `X-FlexPay-Signature` / `Stripe-Signature`). Sans secret : vérification ignorée hors production ; **503** en production.

Idempotence FlexPay : clé cache `webhook:flexpay:{hash(reference|orderNumber)}` (7 jours).

## Codes d'erreur API

- **401** — Non authentifié / signature webhook invalide
- **403** — Compte inactif
- **422** — Validation
- **501** — Endpoint stub

En **production**, les erreurs 500 renvoient le message générique : « Une erreur est survenue » (détails réservés au dev).
