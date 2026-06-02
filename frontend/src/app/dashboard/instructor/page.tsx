'use client';

import { ProtectedView } from '@/src/components/auth/ProtectedView';
import { AppNav } from '@/src/components/layout/AppNav';
import { useAuth } from '@/src/features/auth/useAuth';

export default function InstructorDashboardPage() {
  const { user } = useAuth();

  return (
    <ProtectedView allowedRoles={['instructor']}>
      <main className="page-shell">
        <AppNav />
        <section className="card wide-card">
          <div className="topbar">
            <div>
              <h1>Dashboard instructeur</h1>
              <p>Bienvenue {user?.name}, votre espace formateur centralise vos cours et classes virtuelles.</p>
            </div>
          </div>
          <div className="list-grid">
            <article className="item-card">
              <h3>Cours à animer</h3>
              <p className="helper">Préparez les contenus pédagogiques, les quiz et les ressources de vos cohortes.</p>
            </article>
            <article className="item-card">
              <h3>Classes en direct</h3>
              <p className="helper">Planifiez les sessions Jitsi et accompagnez les apprenants au bon moment.</p>
            </article>
          </div>
        </section>
      </main>
    </ProtectedView>
  );
}
