import { Button } from '@/src/components/ui/Button';

const links = [
  { href: '#positionnement', label: 'Positionnement' },
  { href: '#live', label: 'Classe live' },
  { href: '#ecosysteme', label: 'Écosystème' },
  { href: '#temoignages', label: 'Témoignages' },
];

export function Navbar() {
  return (
    <header className="sticky top-0 z-40 border-b border-[var(--border)] bg-white/85 backdrop-blur-xl">
      <div className="mx-auto max-w-7xl px-4 md:px-6">
        <nav aria-label="Navigation principale" className="flex items-center justify-between py-4">
          <a className="inline-flex items-center gap-3 text-sm font-bold text-[var(--foreground)]" href="#hero" aria-label="Ed-tech accueil">
            <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-[var(--foreground)] text-white shadow-lg shadow-slate-900/10">
              E
            </span>
            <span className="tracking-[-0.02em]">Ed-tech</span>
          </a>

          <div className="hidden items-center gap-1 lg:flex">
            {links.map((link) => (
              <a
                className="rounded-full px-4 py-2 text-sm font-medium text-[var(--muted)] transition hover:bg-[var(--secondary)] hover:text-[var(--foreground)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--primary)]"
                href={link.href}
                key={link.href}
              >
                {link.label}
              </a>
            ))}
          </div>

          <div className="flex items-center gap-2">
            <Button ariaLabel="Connexion Ed-tech" className="hidden sm:inline-flex" href="/auth/login" variant="ghost">
              Connexion
            </Button>
            <Button ariaLabel="Inscription Ed-tech" href="/auth/register" variant="primary">
              S’inscrire
            </Button>
          </div>
        </nav>
      </div>
    </header>
  );
}
