import Link from 'next/link';
import { DashboardCard } from '@/src/components/dashboard/DashboardPrimitives';
import { PlayIcon } from '@/src/components/dashboard/DashboardIcons';
import type { LearningStat } from '@/src/data/mockStudentData';

function RadialProgress({ value }: Readonly<{ value: number }>) {
  return (
    <div className="relative grid size-40 place-items-center rounded-full bg-primary/5 text-primary dark:bg-primary/10 dark:text-secondary">
      <svg className="size-36 -rotate-90" viewBox="0 0 120 120" role="img" aria-label={`${value}% de progression globale`}>
        <circle cx="60" cy="60" r="48" fill="none" stroke="currentColor" strokeOpacity="0.12" strokeWidth="12" />
        <circle
          cx="60"
          cy="60"
          r="48"
          fill="none"
          stroke="currentColor"
          strokeWidth="12"
          strokeLinecap="round"
          pathLength="100"
          strokeDasharray={`${value} 100`}
        />
      </svg>
      <div className="absolute text-center">
        <p className="text-4xl font-bold tracking-tight text-slate-950 dark:text-white">{value}%</p>
        <p className="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Progression</p>
      </div>
    </div>
  );
}

export function WelcomeResumeCard({ userName, stats }: Readonly<{ userName: string; stats: LearningStat[] }>) {
  return (
    <DashboardCard className="col-span-1 overflow-hidden md:col-span-6 lg:col-span-12">
      <div className="grid gap-8 lg:grid-cols-2 lg:items-center">
        <div className="max-w-3xl">
          <p className="mb-3 text-sm font-bold uppercase tracking-widest text-primary dark:text-secondary">Parcours étudiant</p>
          <h1 className="text-3xl font-bold tracking-tight text-slate-950 dark:text-white sm:text-4xl">
            Bonjour {userName}, reprenez votre trajectoire d&apos;apprentissage.
          </h1>
          <p className="mt-4 max-w-2xl text-base leading-7 text-slate-600 dark:text-slate-300">
            Votre espace réunit les lives, quiz, certificats et cours actifs pour transformer chaque session en progression mesurable.
          </p>
          <div className="mt-6 flex flex-wrap gap-3">
            <Link href="/dashboard/courses" className="inline-flex cursor-pointer items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white transition hover:-translate-y-0.5 hover:bg-primary/90 dark:bg-primary dark:hover:bg-secondary dark:hover:text-slate-950">
              <PlayIcon className="size-4" />
              Reprendre le dernier cours
            </Link>
            <Link href="/dashboard/planning" className="inline-flex cursor-pointer items-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:-translate-y-0.5 hover:border-primary hover:text-primary dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-secondary dark:hover:text-secondary">
              Voir le planning
            </Link>
          </div>
        </div>
        <div className="grid gap-5 sm:grid-cols-2 sm:items-center lg:justify-items-end">
          <RadialProgress value={68} />
          <div className="grid gap-3">
            {stats.map((stat) => (
              <div key={stat.label} className="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950">
                <p className="text-2xl font-bold text-slate-950 dark:text-white">{stat.value}</p>
                <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">{stat.label}</p>
              </div>
            ))}
          </div>
        </div>
      </div>
    </DashboardCard>
  );
}
