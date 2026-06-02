'use client';

import Link from 'next/link';
import { usePathname, useRouter } from 'next/navigation';
import { useState } from 'react';
import { ThemeSwitch } from '@/src/components/layout/ThemeSwitch';
import { BellIcon, BookIcon, CertificateIcon, DashboardIcon, SettingsIcon, VideoIcon } from '@/src/components/dashboard/DashboardIcons';
import { cn } from '@/src/lib/cn';

const topbarLinks = [
  { href: '/dashboard/student', label: 'Tableau de bord' },
  { href: '/dashboard/courses', label: 'Catalogue' },
  { href: '/dashboard/planning', label: 'Classes Virtuelles' },
  { href: '/dashboard/profile', label: 'Mon Profil' },
];

const sidebarLinks = [
  { href: '/dashboard/student', label: "Vue d'ensemble", icon: DashboardIcon },
  { href: '/dashboard/courses', label: 'Mes Cours Suivis', icon: BookIcon },
  { href: '/dashboard/planning', label: 'Prochains Lives', icon: VideoIcon },
  { href: '/dashboard/profile', label: 'Mes Certificats Reçus', icon: CertificateIcon },
];

type StudentDashboardShellProps = {
  children: React.ReactNode;
  userName: string;
  onLogout: () => void;
};

function BrandMark() {
  return (
    <Link href="/dashboard/student" className="group inline-flex items-center gap-3" aria-label="Retour au tableau de bord Ed-tech">
      <span className="grid size-10 place-items-center rounded-2xl bg-primary text-sm font-bold text-white transition group-hover:-translate-y-0.5 group-hover:bg-primary/90 dark:bg-primary">
        E
      </span>
      <span className="text-lg font-bold tracking-tight text-slate-950 dark:text-white">Ed-tech</span>
    </Link>
  );
}

function Topbar({ userName, onMenuClick, onLogout }: { userName: string; onMenuClick: () => void; onLogout: () => void }) {
  const pathname = usePathname();

  return (
    <header className="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur dark:border-slate-800 dark:bg-slate-950/95">
      <div className="mx-auto flex h-16 w-full items-center gap-4 px-4 sm:px-6 lg:px-8">
        <button
          type="button"
          className="inline-flex cursor-pointer items-center justify-center rounded-xl border border-slate-200 p-2 text-slate-700 transition hover:border-primary hover:text-primary lg:hidden dark:border-slate-700 dark:text-slate-200"
          onClick={onMenuClick}
          aria-label="Ouvrir le menu d'apprentissage"
        >
          <span className="flex flex-col gap-1" aria-hidden="true">
            <span className="block h-0.5 w-5 rounded-full bg-current" />
            <span className="block h-0.5 w-5 rounded-full bg-current" />
            <span className="block h-0.5 w-5 rounded-full bg-current" />
          </span>
        </button>
        <BrandMark />
        <nav className="hidden flex-1 items-center justify-center gap-2 lg:flex" aria-label="Navigation principale">
          {topbarLinks.map((item) => {
            const isActive = pathname === item.href;

            return (
              <Link
                key={item.href}
                href={item.href}
                className={cn(
                  'rounded-full px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-primary dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-secondary',
                  isActive && 'bg-primary/10 text-primary dark:bg-primary/20 dark:text-secondary',
                )}
              >
                {item.label}
              </Link>
            );
          })}
        </nav>
        <div className="ml-auto flex items-center gap-2 sm:gap-3">
          <ThemeSwitch />
          <button
            type="button"
            aria-label="Notifications"
            className="relative inline-flex cursor-pointer items-center justify-center rounded-full border border-slate-200 bg-white p-3 text-slate-700 transition hover:-translate-y-0.5 hover:border-primary hover:text-primary dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
          >
            <BellIcon className="size-5" />
            <span className="absolute right-2 top-2 size-2 rounded-full bg-orange-500 ring-2 ring-white dark:ring-slate-900" />
          </button>
          <div className="hidden items-center gap-3 rounded-full border border-slate-200 bg-white py-1 pl-1 pr-3 dark:border-slate-700 dark:bg-slate-900 sm:flex">
            <span className="grid size-9 place-items-center rounded-full bg-secondary text-sm font-bold text-primary dark:bg-slate-800 dark:text-secondary">
              {userName.charAt(0).toUpperCase()}
            </span>
            <span className="max-w-32 truncate text-sm font-semibold text-slate-800 dark:text-slate-100">{userName}</span>
            <button type="button" onClick={onLogout} className="cursor-pointer text-xs font-semibold text-slate-500 hover:text-primary dark:text-slate-400 dark:hover:text-secondary">
              Quitter
            </button>
          </div>
        </div>
      </div>
    </header>
  );
}

function SidebarContent({ onNavigate }: { onNavigate?: () => void }) {
  const pathname = usePathname();
  const router = useRouter();

  return (
    <div className="flex h-full flex-col bg-white dark:bg-slate-950">
      <div className="border-b border-slate-200 p-4 dark:border-slate-800 lg:hidden">
        <BrandMark />
      </div>
      <nav className="flex flex-1 flex-col gap-2 p-4" aria-label="Navigation étudiant">
        {sidebarLinks.map((item) => {
          const Icon = item.icon;
          const isActive = pathname === item.href;

          return (
            <Link
              key={item.href}
              href={item.href}
              onClick={onNavigate}
              className={cn(
                'group inline-flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-primary dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-secondary',
                isActive && 'bg-primary/10 text-primary dark:bg-primary/20 dark:text-secondary',
              )}
            >
              <Icon className="size-5 shrink-0" />
              <span>{item.label}</span>
            </Link>
          );
        })}
      </nav>
      <div className="border-t border-slate-200 p-4 dark:border-slate-800">
        <button
          type="button"
          onClick={() => {
            onNavigate?.();
            router.push('/dashboard/profile');
          }}
          className="inline-flex w-full cursor-pointer items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-primary dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-secondary"
        >
          <SettingsIcon className="size-5" />
          Paramètres du compte
        </button>
      </div>
    </div>
  );
}

export function StudentDashboardShell({ children, userName, onLogout }: StudentDashboardShellProps) {
  const [isDrawerOpen, setIsDrawerOpen] = useState(false);

  return (
    <div className="min-h-screen bg-slate-50 text-slate-950 dark:bg-slate-950 dark:text-slate-50">
      <Topbar userName={userName} onMenuClick={() => setIsDrawerOpen(true)} onLogout={onLogout} />
      <div className="flex min-h-screen">
        <aside className="sticky top-16 hidden h-screen w-60 shrink-0 border-r border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950 lg:block">
          <SidebarContent />
        </aside>
        <main className="min-w-0 flex-1 px-4 py-4 sm:px-6 sm:py-6 lg:px-8 lg:py-8">
          <div className="mx-auto grid w-full max-w-screen-2xl grid-cols-1 gap-4 md:grid-cols-6 md:gap-5 lg:grid-cols-12 lg:gap-6">
            {children}
          </div>
        </main>
      </div>
      {isDrawerOpen ? (
        <div className="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true" aria-label="Menu d'apprentissage">
          <button
            type="button"
            className="absolute inset-0 cursor-pointer bg-slate-950/50"
            aria-label="Fermer le menu"
            onClick={() => setIsDrawerOpen(false)}
          />
          <aside className="relative h-full w-72 border-r border-slate-200 shadow-2xl dark:border-slate-800">
            <SidebarContent onNavigate={() => setIsDrawerOpen(false)} />
          </aside>
        </div>
      ) : null}
    </div>
  );
}
