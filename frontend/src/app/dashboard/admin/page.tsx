'use client';

import { ProtectedView } from '@/src/components/auth/ProtectedView';
import { AppNav } from '@/src/components/layout/AppNav';
import { useAuth } from '@/src/features/auth/useAuth';

export default function AdminDashboardPage() {
  const { user } = useAuth();

  return (
    <ProtectedView allowedRoles={['admin']}>
      <main className="page-shell">
        <AppNav />
        <section className="card wide-card">
          <div className="topbar">
            <div>
              <h1>Dashboard administrateur</h1>
              <p>Bienvenue {user?.name}, vous êtes connecté à l&apos;espace de pilotage global.</p>
            </div>
          </div>
          <div className="list-grid">
            <article className="item-card">
              <h3>Gestion académique</h3>
              <p className="helper">Supervisez les cours, les formateurs, les apprenants et les certificats.</p>
            </article>
            <article className="item-card">
              <h3>Qualité & conformité</h3>
              <p className="helper">Suivez les validations, les évaluations et la cohérence des parcours certifiants.</p>
            </article>
          </div>
        </section>
      </main>
    </ProtectedView>
  );
}
