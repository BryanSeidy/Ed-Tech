'use client';

import { ProtectedView } from '@/src/components/auth/ProtectedView';
import { AppNav } from '@/src/components/layout/AppNav';

export default function ExamsPage() {
  return (
    <ProtectedView>
      <main className="page-shell">
        <AppNav />
        <section className="card wide-card">
          <h1>Examens blancs</h1>
          <p>Simulez vos conditions d’examen avec timer, scoring et recommandations.</p>
          <div className="cards-grid">
            <article className="item-card"><h3>Timer intelligent</h3><p>Rythme réel de l’examen final.</p></article>
            <article className="item-card"><h3>Rapport de performance</h3><p>Analyse des erreurs par compétence.</p></article>
          </div>
          <button className="button primary" type="button">Démarrer un examen blanc</button>
        </section>
      </main>
    </ProtectedView>
  );
}
