import Link from 'next/link';
import type { InstructorCourse, InstructorLiveSession, InstructorStats } from '@/src/services/api/instructorDashboardService';

type InstructorDashboardProps = {
  instructorName: string;
  stats: InstructorStats;
  courses: InstructorCourse[];
  liveSessions: InstructorLiveSession[];
};

function formatDateTime(value: string): string {
  return new Intl.DateTimeFormat('fr-FR', {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(new Date(value));
}

function DashboardCard({ children, className = '' }: Readonly<{ children: React.ReactNode; className?: string }>) {
  return (
    <section className={`rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 ${className}`}>
      {children}
    </section>
  );
}

function EmptyState({ title, description, href, actionLabel }: Readonly<{ title: string; description: string; href: string; actionLabel: string }>) {
  return (
    <div className="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center dark:border-slate-700 dark:bg-slate-950">
      <div className="mx-auto grid size-12 place-items-center rounded-2xl bg-primary/10 text-primary dark:bg-primary/20 dark:text-secondary">+</div>
      <h3 className="mt-4 text-lg font-bold text-slate-950 dark:text-white">{title}</h3>
      <p className="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-500 dark:text-slate-400">{description}</p>
      <Link href={href} className="mt-5 inline-flex items-center justify-center rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white transition hover:-translate-y-0.5 hover:bg-primary/90 dark:bg-secondary dark:text-slate-950">
        {actionLabel}
      </Link>
    </div>
  );
}

function KpiModule({ stats }: Readonly<{ stats: InstructorStats }>) {
  const items = [
    { label: 'Étudiants actifs', value: stats.total_students.toString(), helper: 'inscrits à vos cours' },
    { label: 'Cours publiés', value: stats.published_courses.toString(), helper: 'visibles au catalogue' },
    { label: 'Complétion moyenne', value: `${stats.average_completion_rate}%`, helper: 'sur les leçons suivies' },
    { label: 'Réussite quiz', value: `${stats.quiz_success_rate}%`, helper: `${stats.upcoming_live_sessions} live(s) à venir` },
  ];

  return (
    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
      {items.map((item) => (
        <DashboardCard key={item.label}>
          <p className="text-sm font-semibold text-slate-500 dark:text-slate-400">{item.label}</p>
          <p className="mt-3 text-3xl font-bold tracking-tight text-slate-950 dark:text-white">{item.value}</p>
          <p className="mt-2 text-sm text-slate-500 dark:text-slate-400">{item.helper}</p>
        </DashboardCard>
      ))}
    </div>
  );
}

function CoursesModule({ courses }: Readonly<{ courses: InstructorCourse[] }>) {
  return (
    <DashboardCard className="xl:col-span-7">
      <div className="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div>
          <p className="text-xs font-bold uppercase tracking-widest text-primary dark:text-secondary">Gestion des cours</p>
          <h2 className="mt-1 text-2xl font-bold tracking-tight text-slate-950 dark:text-white">Vos parcours pédagogiques</h2>
        </div>
        <Link href="/dashboard/courses" className="rounded-xl border border-slate-200 px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-primary hover:text-primary dark:border-slate-700 dark:text-slate-200 dark:hover:border-secondary dark:hover:text-secondary">
          Nouveau contenu
        </Link>
      </div>
      {courses.length === 0 ? (
        <EmptyState title="Créez votre premier cours pour commencer à enseigner" description="Structurez vos modules, ajoutez vos leçons et publiez un parcours certifiant pour accueillir vos premiers apprenants." href="/dashboard/courses" actionLabel="Créer un cours" />
      ) : (
        <div className="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800">
          <div className="hidden grid-cols-4 gap-4 bg-slate-50 px-4 py-3 text-xs font-bold uppercase tracking-wider text-slate-500 dark:bg-slate-950 dark:text-slate-400 md:grid">
            <span>Cours</span>
            <span>Inscriptions</span>
            <span>Statut</span>
            <span>Action</span>
          </div>
          <div className="divide-y divide-slate-200 dark:divide-slate-800">
            {courses.map((course) => (
              <article key={course.id} className="grid gap-4 px-4 py-4 md:grid-cols-4 md:items-center">
                <div>
                  <h3 className="font-bold text-slate-950 dark:text-white">{course.title}</h3>
                  <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">{course.modules_count} module(s) · {course.lessons_count} leçon(s) · {course.upcoming_live_sessions_count} live(s)</p>
                </div>
                <span className="w-fit rounded-full bg-primary/10 px-3 py-1 text-sm font-bold text-primary dark:bg-primary/20 dark:text-secondary">
                  {course.students_count} inscrit(s)
                </span>
                <span className={course.is_published ? 'w-fit rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-700 dark:bg-green-500/20 dark:text-green-300' : 'w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300'}>
                  {course.is_published ? 'Publié' : 'Brouillon'}
                </span>
                <Link href={`/dashboard/courses/${course.id}`} className="inline-flex justify-center rounded-xl bg-slate-950 px-4 py-2 text-sm font-bold text-white transition hover:-translate-y-0.5 hover:bg-primary dark:bg-white dark:text-slate-950 dark:hover:bg-secondary">
                  Gérer le contenu
                </Link>
              </article>
            ))}
          </div>
        </div>
      )}
    </DashboardCard>
  );
}

function LivePlanningModule({ liveSessions }: Readonly<{ liveSessions: InstructorLiveSession[] }>) {
  return (
    <DashboardCard className="xl:col-span-5">
      <div className="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div>
          <p className="text-xs font-bold uppercase tracking-widest text-primary dark:text-secondary">Planification live</p>
          <h2 className="mt-1 text-2xl font-bold tracking-tight text-slate-950 dark:text-white">Classes Jitsi à venir</h2>
        </div>
        <Link href="/dashboard/planning" className="rounded-xl bg-primary px-4 py-2 text-sm font-bold text-white transition hover:-translate-y-0.5 hover:bg-primary/90 dark:bg-secondary dark:text-slate-950">
          Planifier
        </Link>
      </div>
      {liveSessions.length === 0 ? (
        <EmptyState title="Aucun live planifié" description="Planifiez une session Jitsi pour créer un rendez-vous pédagogique clair avec votre cohorte." href="/dashboard/planning" actionLabel="Créer une session" />
      ) : (
        <div className="space-y-3">
          {liveSessions.map((session) => (
            <article key={session.id} className="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950">
              <div className="flex items-start justify-between gap-3">
                <div>
                  <h3 className="font-bold text-slate-950 dark:text-white">{session.title}</h3>
                  <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">{session.course_title ?? 'Cours sans titre'}</p>
                </div>
                <span className="rounded-full bg-secondary/10 px-3 py-1 text-xs font-bold text-secondary dark:bg-secondary/20">Jitsi</span>
              </div>
              <p className="mt-4 text-sm font-semibold text-slate-700 dark:text-slate-200">{formatDateTime(session.start_at)}</p>
              <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">Durée : {session.duration_minutes} minutes</p>
              <Link href="/dashboard/planning" className={session.can_start ? 'mt-4 inline-flex w-full justify-center rounded-xl bg-primary px-4 py-3 text-sm font-bold text-white transition hover:-translate-y-0.5 hover:bg-primary/90 dark:bg-secondary dark:text-slate-950' : 'mt-4 inline-flex w-full justify-center rounded-xl bg-slate-100 px-4 py-3 text-sm font-bold text-slate-400 dark:bg-slate-800 dark:text-slate-500'}>
                {session.can_start ? 'Démarrer le Live Jitsi' : 'Ouverture prochaine'}
              </Link>
            </article>
          ))}
        </div>
      )}
    </DashboardCard>
  );
}

export function InstructorDashboard({ instructorName, stats, courses, liveSessions }: InstructorDashboardProps) {
  return (
    <main className="min-h-screen bg-slate-50 px-4 py-6 text-slate-950 dark:bg-slate-950 dark:text-white sm:px-6 lg:px-8">
      <div className="mx-auto grid w-full max-w-screen-2xl gap-6">
        <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
          <p className="text-sm font-bold uppercase tracking-widest text-primary dark:text-secondary">Dashboard formateur</p>
          <div className="mt-3 flex flex-wrap items-end justify-between gap-4">
            <div>
              <h1 className="text-3xl font-bold tracking-tight text-slate-950 dark:text-white sm:text-4xl">Bonjour {instructorName}</h1>
              <p className="mt-3 max-w-3xl text-base leading-7 text-slate-600 dark:text-slate-300">Pilotez vos cours, suivez l&apos;engagement de vos apprenants et démarrez vos classes virtuelles depuis un espace connecté aux données réelles.</p>
            </div>
            <Link href="/dashboard/planning" className="rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white transition hover:-translate-y-0.5 hover:bg-primary/90 dark:bg-secondary dark:text-slate-950">
              Créer un live
            </Link>
          </div>
        </section>
        <KpiModule stats={stats} />
        <div className="grid gap-6 xl:grid-cols-12">
          <CoursesModule courses={courses} />
          <LivePlanningModule liveSessions={liveSessions} />
        </div>
      </div>
    </main>
  );
}
