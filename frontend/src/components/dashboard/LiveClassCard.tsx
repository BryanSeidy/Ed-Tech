import { DashboardCard, SectionHeader, StatusBadge } from '@/src/components/dashboard/DashboardPrimitives';
import { LockIcon, VideoIcon } from '@/src/components/dashboard/DashboardIcons';
import type { LiveClass, LiveClassState } from '@/src/data/mockStudentData';
import { cn } from '@/src/lib/cn';

const stateMeta: Record<LiveClassState, { label: string; badge: 'primary' | 'warning' | 'locked' | 'live'; button: string; disabled: boolean; pulse?: boolean }> = {
  locked: { label: 'Verrouillé', badge: 'locked', button: 'Pré-requis requis', disabled: true },
  upcoming: { label: 'À venir', badge: 'primary', button: 'Ouverture bientôt', disabled: true },
  startingSoon: { label: 'Démarre bientôt', badge: 'warning', button: 'Préparer la session', disabled: false },
  live: { label: 'En direct', badge: 'live', button: 'Rejoindre maintenant', disabled: false, pulse: true },
};

export function LiveClassCard({ liveClass }: Readonly<{ liveClass: LiveClass }>) {
  const meta = stateMeta[liveClass.state];

  return (
    <DashboardCard className="col-span-1 md:col-span-3 lg:col-span-5">
      <SectionHeader
        eyebrow="Classe virtuelle"
        title={liveClass.title}
        action={<StatusBadge variant={meta.badge}>{meta.pulse ? <span className="mr-2 size-2 rounded-full bg-current animate-pulse" /> : null}{meta.label}</StatusBadge>}
      />
      <div className="rounded-2xl border border-primary/20 bg-primary/5 p-4 dark:border-primary/30 dark:bg-primary/10">
        <div className="flex items-start gap-3">
          <span className="grid size-12 shrink-0 place-items-center rounded-2xl bg-primary text-white dark:bg-secondary dark:text-slate-950">
            <VideoIcon className="size-6" />
          </span>
          <div>
            <h3 className="font-bold text-slate-950 dark:text-white">{liveClass.course}</h3>
            <p className="mt-1 text-sm text-slate-600 dark:text-slate-300">{liveClass.instructor}</p>
            <p className="text-xs font-semibold text-slate-500 dark:text-slate-400">{liveClass.instructorRole}</p>
          </div>
        </div>
        <div className="mt-5 grid gap-3 sm:grid-cols-2">
          <div className="rounded-xl bg-white p-3 dark:bg-slate-900">
            <p className="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Date</p>
            <p className="mt-1 font-semibold text-slate-950 dark:text-white">{liveClass.dateLabel}</p>
          </div>
          <div className="rounded-xl bg-white p-3 dark:bg-slate-900">
            <p className="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Heure</p>
            <p className="mt-1 font-semibold text-slate-950 dark:text-white">{liveClass.timeLabel}</p>
          </div>
        </div>
      </div>
      <div className="mt-5 grid grid-cols-3 gap-3" aria-label="Compte à rebours avant le live">
        {Object.entries(liveClass.countdown).map(([label, value]) => (
          <div key={label} className="rounded-2xl border border-slate-200 bg-slate-50 p-3 text-center dark:border-slate-800 dark:bg-slate-950">
            <p className="text-2xl font-bold tabular-nums text-slate-950 dark:text-white">{value}</p>
            <p className="mt-1 text-xs font-semibold capitalize text-slate-500 dark:text-slate-400">{label}</p>
          </div>
        ))}
      </div>
      <button
        type="button"
        disabled={meta.disabled}
        className={cn(
          'mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-bold transition',
          meta.disabled
            ? 'cursor-not-allowed bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500'
            : 'cursor-pointer bg-primary text-white hover:-translate-y-0.5 hover:bg-primary/90 dark:bg-secondary dark:text-slate-950 dark:hover:bg-secondary/90',
        )}
      >
        {meta.disabled ? <LockIcon className="size-4" /> : <VideoIcon className="size-4" />}
        {meta.button}
      </button>
    </DashboardCard>
  );
}
