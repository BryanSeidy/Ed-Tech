import Image from 'next/image';
import { Button } from '@/src/components/ui/Button';

export function Hero() {
  return (
    <section className="py-16 md:py-24">
      <div className="max-w-7xl mx-auto px-4 md:px-6 grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
        <div className="space-y-6 animate-in-up">
          <p className="inline-flex rounded-full border border-[var(--border)] bg-[var(--secondary)] px-3 py-1 text-xs font-semibold text-[var(--muted)]">
            Plateforme juridique interactive
          </p>
          <h1 className="text-4xl md:text-5xl font-bold text-[var(--foreground)] leading-tight">
            Autonomiser les esprits juridiques grâce à une éducation interactive en ligne
          </h1>
          <p className="text-[var(--muted)] text-base md:text-lg">
            Élevez votre parcours juridique en apprenant en ligne pour les étudiants en droit. Débloquez le monde du
            droit Apprenez, appliquez, réussissez
          </p>
          <div className="flex flex-wrap gap-3 animate-in-up delay-2">
            <Button ariaLabel="Commencer" href="/auth/register" variant="primary">
              Commencer
            </Button>
            <Button ariaLabel="Parcourir tous les cours" href="/dashboard/courses" variant="secondary">
              Parcourir tous les cours
            </Button>
          </div>
        </div>

        <div className="relative animate-in-up delay-3">
          <div className="absolute -left-4 -top-4 h-16 w-16 rounded-full bg-[var(--secondary)]" />
          <div className="absolute -right-3 bottom-8 h-8 w-8 rounded-full bg-[var(--accent)]/70" />
          <div className="relative overflow-hidden rounded-3xl border border-[var(--border)] bg-[var(--card)] p-2">
            <Image
              alt="Deux étudiantes diplômées"
              className="h-auto w-full rounded-2xl object-cover"
              height={680}
              priority
              src="/hero-graduates.svg"
              width={720}
            />
          </div>
        </div>
      </div>
    </section>
  );
}
