'use client';

import Link from 'next/link';
import { usePathname } from 'next/navigation';

const items = [
  { href: '/dashboard', label: 'Dashboard' },
  { href: '/dashboard/courses', label: 'Cours' },
  { href: '/dashboard/exercises', label: 'Exercices' },
  { href: '/dashboard/exams', label: 'Examens' },
  { href: '/dashboard/planning', label: 'Planning' },
];

export function AppNav() {
  const pathname = usePathname();

  return (
    <nav className="app-nav" aria-label="Navigation principale">
      <Link className="brand" href="/">
        Ed-Tech
      </Link>
      <div className="app-nav-links">
        {items.map((item) => (
          <Link
            key={item.href}
            href={item.href}
            className={`app-nav-link ${pathname === item.href ? 'active' : ''}`.trim()}
          >
            {item.label}
          </Link>
        ))}
      </div>
    </nav>
  );
}
