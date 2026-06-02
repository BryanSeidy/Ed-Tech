import { cn } from '@/src/lib/cn';

export function DashboardCard({ children, className }: Readonly<{ children: React.ReactNode; className?: string }>) {
  return (
    <section className={cn('rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:shadow-none sm:p-6', className)}>
      {children}
    </section>
  );
}

export function SectionHeader({ eyebrow, title, action }: Readonly<{ eyebrow?: string; title: string; action?: React.ReactNode }>) {
  return (
    <div className="mb-5 flex items-start justify-between gap-4">
      <div>
        {eyebrow ? <p className="mb-1 text-xs font-bold uppercase tracking-widest text-primary dark:text-secondary">{eyebrow}</p> : null}
        <h2 className="text-xl font-bold tracking-tight text-slate-950 dark:text-white">{title}</h2>
      </div>
      {action}
    </div>
  );
}

export function StatusBadge({ children, variant }: Readonly<{ children: React.ReactNode; variant: 'primary' | 'success' | 'warning' | 'locked' | 'live' }>) {
  const variants = {
    primary: 'bg-primary/10 text-primary dark:bg-primary/20 dark:text-secondary',
    success: 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-300',
    warning: 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300',
    locked: 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
    live: 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300',
  };

  return <span className={cn('inline-flex items-center rounded-full px-3 py-1 text-xs font-bold', variants[variant])}>{children}</span>;
}

export function ProgressBar({ value, muted = false }: Readonly<{ value: number; muted?: boolean }>) {
  return (
    <div className="h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800" aria-hidden="true">
      <div
        className={cn('h-full rounded-full transition-all', muted ? 'bg-slate-300 dark:bg-slate-700' : 'bg-primary dark:bg-secondary')}
        style={{ width: `${value}%` }}
      />
    </div>
  );
}
