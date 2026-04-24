'use client';

import { ProtectedView } from '@/src/components/auth/ProtectedView';
import { AppNav } from '@/src/components/layout/AppNav';
import { useAuth } from '@/src/features/auth/useAuth';

export default function ProfilePage() {
  const { user } = useAuth();

  return (
    <ProtectedView>
      <main className="page-shell">
        <AppNav />
        <section className="card wide-card">
          <h1>Profil & statistiques</h1>
          <p>Nom: {user?.name}</p>
          <p>Email: {user?.email}</p>
          <div className="cards-grid">
            <article className="item-card"><h3>Niveau global</h3><p>Intermédiaire avancé</p></article>
            <article className="item-card"><h3>Sessions utiles</h3><p>87% de complétion ce mois-ci</p></article>
            <article className="item-card"><h3>Recommandation</h3><p>Renforcer les quiz de logique appliquée.</p></article>
          </div>
        </section>
      </main>
    </ProtectedView>
  );
}
