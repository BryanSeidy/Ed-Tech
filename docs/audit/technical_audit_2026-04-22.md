# Audit technique & produit — Ed-Tech (2026-04-22)

## Verdict rapide
Le projet est **au stade prototype avancé**, avec une base API + auth existante, mais des incohérences structurelles majeures (modèles/migrations/seeders/front) empêchent une livraison production sans phase de stabilisation courte mais intensive.

## Points critiques observés
1. **Incohérence du modèle de données backend** (`teacher_id` vs `instructor_id`, `order` vs `position`, `pdf_file` absent en migration) entre modèles, seeders, migrations et services.
2. **Frontend incomplet** : de nombreux composants/services/hooks sont des fichiers vides (0 octet).
3. **Parcours auth incomplet/cassé** : redirections vers `/login` au lieu de `/auth/login`.
4. **Documentation insuffisante** : README backend/frontend par défaut, pas de runbook de prod.
5. **Pipeline qualité insuffisant** : tests présents mais non exécutables localement sans installation backend (`vendor/` absent), absence de couverture front réelle.

## Priorités de remédiation
- **P0 (jour 1)** : unifier le schéma métier (instructor), corriger relations Eloquent, seeders, redirections front.
- **P1 (jour 1-2)** : fermer le scope MVP (cours -> leçon -> quiz -> progression), supprimer/masquer les écrans non prêts.
- **P2 (jour 2-3)** : tests E2E happy paths + erreurs, hardening auth/session/cookies/CORS.
- **P3 (jour 3-4)** : CI/CD minimal, observabilité, checklists prod, déploiement staging puis prod.
