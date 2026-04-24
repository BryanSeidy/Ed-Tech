# E-learning Platform (MVP)

## Stack
- Frontend: Next.js
- Backend: Laravel
- Database: MySQL / SQLite (tests)

## Run local (MVP checklist)
```bash
# From repository root
./scripts/run_mvp_check.sh
```

Manual steps:
```bash
# Frontend
cd frontend
npm ci
npm run smoke
npm run lint
npm run build

# Backend
cd ../backend
composer install --no-interaction --prefer-dist
cp .env.example .env
php artisan key:generate --force
php artisan migrate:fresh --seed
php artisan test
php artisan serve
```

## MVP scope in production
- ✅ Auth session flow: register/login/me/logout
- ✅ Catalogue cours
- ✅ Détail cours (modules + leçons)
- ✅ Lecture de leçon
- ✅ Soumission quiz
- ✅ Affichage progression
- ⛔ Live / certificats / paiement masqués côté UI tant qu'incomplets

## Basic security controls
- CORS limité à `FRONTEND_URL` avec cookies (`supports_credentials=true`).
- Session API activée via middleware `StartSession`.
- Validation requêtes auth + payload quiz/progression.
- Rate limit auth (`throttle:10,1` sur register/login).

## CI
- Workflow `.github/workflows/ci.yml`
  - Frontend: `smoke + lint + build`
  - Backend: `migrate:fresh --seed + php artisan test`
