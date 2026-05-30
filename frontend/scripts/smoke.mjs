import { readFileSync } from 'node:fs';

const checks = [
  ['src/app/page.tsx', '<Navbar />'],
  ['src/app/dashboard/courses/page.tsx', 'Catalogue des cours'],
  ['src/app/dashboard/courses/[courseId]/CourseDetailClient.tsx', 'Progression:'],
  ['src/app/dashboard/courses/[courseId]/lessons/[lessonId]/LessonClient.tsx', 'Marquer comme terminée'],
  ['src/app/dashboard/courses/[courseId]/quiz/[quizId]/QuizClient.tsx', 'Soumettre le quiz'],
  ['src/app/dashboard/exercises/page.tsx', 'Générer un nouvel exercice'],
  ['src/app/dashboard/exams/page.tsx', 'Démarrer un examen blanc'],
  ['src/app/dashboard/planning/page.tsx', 'Planning des classes virtuelles'],
  ['src/app/dashboard/profile/page.tsx', 'Profil & statistiques'],
];

for (const [file, expected] of checks) {
  const content = readFileSync(new URL(`../${file}`, import.meta.url), 'utf8');
  if (!content.includes(expected)) {
    throw new Error(`Smoke check failed for ${file}: missing "${expected}"`);
  }
}

console.log('Frontend smoke checks passed.');
