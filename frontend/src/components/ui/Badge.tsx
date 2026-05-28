import { cn } from '@/src/lib/cn';

export function Badge({ children, className }: { children: React.ReactNode; className?: string }) {
  return (
    <span
      className={cn(
        'inline-flex items-center gap-2 rounded-full border border-[var(--border)] bg-white/90 px-3 py-1 text-xs font-semibold text-[var(--foreground)] backdrop-blur',
        className,
      )}
    >
      {children}
    </span>
  );
}
