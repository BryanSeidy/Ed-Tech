import { DashboardCard, ProgressBar, SectionHeader, StatusBadge } from '@/src/components/dashboard/DashboardPrimitives';
import { LockIcon } from '@/src/components/dashboard/DashboardIcons';
import type { CourseState, StudentCourse } from '@/src/data/mockStudentData';
import { cn } from '@/src/lib/cn';

const courseStateMeta: Record<CourseState, { label: string; badge: 'primary' | 'success' | 'locked' }> = {
  active: { label: 'Actif', badge: 'primary' },
  completed: { label: 'Terminé', badge: 'success' },
  locked: { label: 'Verrouillé', badge: 'locked' },
};

function CourseCard({ course }: Readonly<{ course: StudentCourse }>) {
  const meta = courseStateMeta[course.state];
  const isLocked = course.state === 'locked';

  return (
    <article className={cn('rounded-2xl border border-slate-200 bg-white p-4 transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-800 dark:bg-slate-950', isLocked && 'opacity-70 hover:translate-y-0 hover:shadow-none')}>
      <div className="flex items-start justify-between gap-3">
        <div className="flex items-center gap-3">
          <span className={cn('grid size-12 place-items-center rounded-2xl text-sm font-bold', isLocked ? 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' : 'bg-secondary text-primary dark:bg-primary/20 dark:text-secondary')}>
            {isLocked ? <LockIcon className="size-5" /> : course.icon}
          </span>
          <div>
            <h3 className="font-bold text-slate-950 dark:text-white">{course.title}</h3>
            <p className="mt-1 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">{course.category}</p>
          </div>
        </div>
        <StatusBadge variant={meta.badge}>{meta.label}</StatusBadge>
      </div>
      <div className="mt-5 space-y-3">
        <div className="flex items-center justify-between text-sm">
          <span className="font-semibold text-slate-600 dark:text-slate-300">Progression</span>
          <span className="font-bold text-slate-950 dark:text-white">{course.progress}%</span>
        </div>
        <ProgressBar value={course.progress} muted={isLocked} />
        <div className="grid grid-cols-2 gap-3 pt-2">
          <div className="rounded-xl bg-slate-50 p-3 dark:bg-slate-900">
            <p className="text-sm font-bold text-slate-950 dark:text-white">{course.completedLessons}/{course.totalLessons}</p>
            <p className="text-xs text-slate-500 dark:text-slate-400">Leçons terminées</p>
          </div>
          <div className="rounded-xl bg-slate-50 p-3 dark:bg-slate-900">
            <p className="text-sm font-bold text-slate-950 dark:text-white">{course.remainingQuizzes}</p>
            <p className="text-xs text-slate-500 dark:text-slate-400">Quiz restants</p>
          </div>
        </div>
      </div>
    </article>
  );
}

export function CourseProgressBars({ courses }: Readonly<{ courses: StudentCourse[] }>) {
  return (
    <DashboardCard className="col-span-1 md:col-span-6 lg:col-span-7">
      <SectionHeader eyebrow="Cours suivis" title="Progression des parcours" />
      <div className="grid gap-4 sm:grid-cols-2">
        {courses.map((course) => (
          <CourseCard key={course.id} course={course} />
        ))}
      </div>
    </DashboardCard>
  );
}
