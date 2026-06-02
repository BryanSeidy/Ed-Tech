import { DashboardCard, SectionHeader, StatusBadge } from '@/src/components/dashboard/DashboardPrimitives';
import { LockIcon } from '@/src/components/dashboard/DashboardIcons';
import type { QuizState, StudentQuiz } from '@/src/data/mockStudentData';

const quizStateMeta: Record<QuizState, { label: string; badge: 'success' | 'warning' | 'locked'; action: string }> = {
  passed: { label: 'Validé', badge: 'success', action: 'Revoir' },
  retry: { label: 'À repasser', badge: 'warning', action: 'Réessayer' },
  locked: { label: 'À venir', badge: 'locked', action: 'Verrouillé' },
};

export function QuizEvaluationCard({ quizzes }: Readonly<{ quizzes: StudentQuiz[] }>) {
  return (
    <DashboardCard className="col-span-1 md:col-span-3 lg:col-span-5">
      <SectionHeader eyebrow="Évaluations" title="Quiz & validations" />
      <div className="space-y-3">
        {quizzes.map((quiz) => {
          const meta = quizStateMeta[quiz.state];
          const isLocked = quiz.state === 'locked';

          return (
            <article key={quiz.id} className="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950">
              <div className="flex items-start justify-between gap-3">
                <div>
                  <h3 className="font-bold text-slate-950 dark:text-white">{quiz.title}</h3>
                  <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">{quiz.course}</p>
                </div>
                <StatusBadge variant={meta.badge}>{meta.label}</StatusBadge>
              </div>
              <div className="mt-4 flex items-center justify-between gap-3">
                <div>
                  <p className="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Score</p>
                  <p className="mt-1 text-xl font-bold text-slate-950 dark:text-white">{quiz.score === null ? '—' : `${quiz.score}%`}</p>
                </div>
                <button
                  type="button"
                  disabled={isLocked}
                  className={isLocked ? 'inline-flex cursor-not-allowed items-center gap-2 rounded-xl bg-slate-100 px-4 py-2 text-sm font-bold text-slate-400 dark:bg-slate-800 dark:text-slate-500' : 'inline-flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:-translate-y-0.5 hover:border-primary hover:text-primary dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-secondary dark:hover:text-secondary'}
                >
                  {isLocked ? <LockIcon className="size-4" /> : null}
                  {meta.action}
                </button>
              </div>
            </article>
          );
        })}
      </div>
    </DashboardCard>
  );
}
