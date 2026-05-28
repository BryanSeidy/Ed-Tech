import { Button } from '@/src/components/ui/Button';
import { IconChevronDown } from '@/src/components/landing/icons';

function MenuItem({ label, active = false, withDropdown = false }: { label: string; active?: boolean; withDropdown?: boolean }) {
  return (
    <a
      aria-label={label}
      className={`inline-flex items-center gap-1 rounded-md px-2 py-1 text-sm transition duration-500 ease-out focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--primary)] ${
        active ? 'text-[var(--primary)]' : 'text-[var(--foreground)] hover:text-[var(--primary)]'
      }`}
      href="#"
    >
      {label}
      {withDropdown ? <IconChevronDown /> : null}
    </a>
  );
}

export function Navbar() {
  return (
    <header className="sticky top-0 z-40 border-b border-[var(--border)] bg-[var(--background)]/95 backdrop-blur">
      <div className="max-w-7xl mx-auto px-4 md:px-6">
        <nav aria-label="Primary" className="flex items-center justify-between py-4">
          <a className="inline-flex items-center gap-2 text-sm font-bold text-[var(--foreground)]" href="#" aria-label="ED-TECH Home">
            <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--primary)] text-white">E</span>
            ED-TECH
          </a>

          <div className="hidden items-center gap-4 md:flex">
            <MenuItem active label="Home" />
            <MenuItem label="Courses" withDropdown />
            <MenuItem label="Pages" withDropdown />
            <MenuItem label="Blog" />
            <MenuItem label="Contact" />
          </div>

          <div className="flex items-center gap-2">
            <Button ariaLabel="Register" href="/auth/register" variant="ghost">
              Register
            </Button>
            <Button ariaLabel="Sign In" href="/auth/login" variant="primary">
              Sign In
            </Button>
          </div>
        </nav>
      </div>
    </header>
  );
}
