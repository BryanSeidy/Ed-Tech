import Link from 'next/link';
import { cn } from '@/src/lib/cn';

type ButtonProps = {
  children: React.ReactNode;
  href?: string;
  type?: 'button' | 'submit';
  ariaLabel?: string;
  variant?: 'primary' | 'secondary' | 'ghost';
  className?: string;
};

const base =
  'inline-flex items-center justify-center rounded-xl px-5 py-3 text-sm font-semibold transition duration-500 ease-out focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--primary)] focus-visible:ring-offset-2';

const variants = {
  primary:
    'bg-[var(--primary)] text-white hover:scale-105 hover:shadow-lg hover:shadow-[color:var(--primary)]/30',
  secondary:
    'border border-[var(--border)] bg-[var(--background)] text-[var(--foreground)] hover:bg-[var(--secondary)]',
  ghost: 'text-[var(--foreground)] hover:text-[var(--primary)]',
};

export function Button({
  children,
  href,
  type = 'button',
  ariaLabel,
  variant = 'primary',
  className,
}: ButtonProps) {
  const classes = cn(base, variants[variant], className);

  if (href) {
    return (
      <Link aria-label={ariaLabel} className={classes} href={href}>
        {children}
      </Link>
    );
  }

  return (
    <button aria-label={ariaLabel} className={classes} type={type}>
      {children}
    </button>
  );
}
