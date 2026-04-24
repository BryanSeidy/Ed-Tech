import { readFileSync } from 'node:fs';

const checks = [
  ['src/app/page.tsx', 'Apprenez plus vite. Réussissez avec précision.'],
  ['src/app/dashboard/courses/page.tsx', 'Catalogue des cours'],
  ['src/app/dashboard/courses/[courseId]/page.tsx', 'Progression:'],
  ['src/app/dashboard/courses/[courseId]/lessons/[lessonId]/page.tsx', 'Marquer comme terminée'],
  ['src/app/dashboard/courses/[courseId]/quiz/[quizId]/page.tsx', 'Soumettre le quiz'],
  ['src/app/dashboard/exercises/page.tsx', 'Générer un nouvel exercice'],
  ['src/app/dashboard/exams/page.tsx', 'Démarrer un examen blanc'],
  ['src/app/dashboard/planning/page.tsx', 'Optimiser mon planning'],
  ['src/app/dashboard/profile/page.tsx', 'Profil & statistiques'],
];

for (const [file, expected] of checks) {
  const content = readFileSync(new URL(`../${file}`, import.meta.url), 'utf8');
  if (!content.includes(expected)) {
    throw new Error(`Smoke check failed for ${file}: missing "${expected}"`);
  }
}

console.log('Frontend smoke checks passed.');
