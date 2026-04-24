'use client';

import { ProtectedView } from '@/src/components/auth/ProtectedView';
import { AppNav } from '@/src/components/layout/AppNav';

export default function ExercisesPage() {
  return (
    <ProtectedView>
      <main className="page-shell">
        <AppNav />
        <section className="card wide-card">
          <h1>Exercices</h1>
          <p>Générez des exercices adaptés à vos lacunes et recevez un feedback instantané.</p>
          <div className="cards-grid">
            <article className="item-card"><h3>Diagnostic rapide</h3><p>Identifiez vos points faibles en 3 minutes.</p></article>
            <article className="item-card"><h3>Feedback immédiat</h3><p>Corrigés expliqués étape par étape.</p></article>
          </div>
          <button className="button primary" type="button">Générer un nouvel exercice</button>
        </section>
      </main>
    </ProtectedView>
  );
}
