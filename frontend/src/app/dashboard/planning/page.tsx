'use client';

import { ProtectedView } from '@/src/components/auth/ProtectedView';
import { AppNav } from '@/src/components/layout/AppNav';

export default function PlanningPage() {
  return (
    <ProtectedView>
      <main className="page-shell">
        <AppNav />
        <section className="card wide-card">
          <h1>Planning intelligent</h1>
          <p>Optimisez vos sessions automatiquement selon vos objectifs et disponibilités.</p>
          <div className="cards-grid">
            <article className="item-card"><h3>Calendrier adaptatif</h3><p>Répartition optimale de vos révisions.</p></article>
            <article className="item-card"><h3>Priorités IA</h3><p>Travaillez en premier les sujets critiques.</p></article>
          </div>
          <button className="button primary" type="button">Optimiser mon planning</button>
        </section>
      </main>
    </ProtectedView>
  );
}
