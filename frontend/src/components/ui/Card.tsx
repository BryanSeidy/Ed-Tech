import { cn } from '@/src/lib/cn';

export function Card({ children, className }: { children: React.ReactNode; className?: string }) {
  return (
    <article
      className={cn(
        'rounded-2xl border border-[var(--border)] bg-[var(--card)] p-4 md:p-6 shadow-sm transition duration-500 ease-out hover:-translate-y-1 hover:shadow-xl',
        className,
      )}
    >
      {children}
    </article>
  );
}
