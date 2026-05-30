# Ed-Tech LMS (MVP + Conversion Landing)

## Stack

- Frontend: Next.js
- Backend: Laravel
- Database: MySQL / SQLite (tests)

## Run local (checklist)

```bash
# from repository root
./scripts/run_mvp_check.sh
```

### Backend dependency mirror (restricted network)

If GitHub access is blocked, configure a mirror before install:

```bash
cd backend
GITHUB_MIRROR_URL=https://ghproxy.com/https://github.com ./scripts/composer_install_with_mirror.sh
```

## Product pages architecture

- Landing conversion: `/`
- Auth: `/auth/login`, `/auth/register`
- Dashboard: `/dashboard`
- Cours: `/dashboard/courses`, `/dashboard/courses/[courseId]`
- Leçon: `/dashboard/courses/[courseId]/lessons/[lessonId]`
- Quiz: `/dashboard/courses/[courseId]/quiz/[quizId]`
- Exercices: `/dashboard/exercises`
- Examens: `/dashboard/exams`
- Planning: `/dashboard/planning`
- Profil: `/dashboard/profile`

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
  - Backend: install (mirror-ready) + `migrate:fresh --seed + php artisan test`
