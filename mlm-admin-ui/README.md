# Salang Group — Admin UI (Next.js)

Console d’administration **dark mode** pour la plateforme MLM Salang (Health Care International). Projet **démo front** séparé du back-office Laravel ; données mockées dans `src/lib/mock-data.ts`.

## Stack

- Next.js 14 (App Router) · React · TypeScript  
- Tailwind CSS · composants style shadcn (`src/components/ui/`)  
- Lucide React · Recharts · react-simple-maps  

## Thème

| Token | Valeur |
|--------|--------|
| Canvas | `#0B0F19` |
| Cartes | `#151B2B` |
| Bordures | `#1F2937` |
| Primaire | `#7C3AED` |
| Succès | `#10B981` |
| Marque Salang | `#5AB638` |

Layout **3 colonnes** : sidebar nav (250px) · contenu · widgets (300px).

## Lancer en local

```bash
cd salang-mlm-ecommerce/mlm-admin-ui
npm install
npm run dev
```

Ouvrir [http://localhost:3000/dashboard](http://localhost:3000/dashboard).

## Routes

| Route | Contenu |
|--------|---------|
| `/dashboard` | KPI + Revenue + Donut + carte monde + top ranks |
| `/users` | DataGrid (filtres, pagination, actions) |
| `/binary` | Arbre binaire SVG |
| `/wallets` | Portefeuilles (mock) |
| `/settings` | Paramètres (placeholder) |

## Intégration Laravel (à venir)

Brancher les pages sur l’API Laravel (auth Sanctum/session admin, endpoints users/commissions/binary). Ce repo sert de **maquette UI** validée avant connexion au monolithe `salang-mlm-ecommerce`.
