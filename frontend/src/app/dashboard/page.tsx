'use client';

import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { ProtectedView } from '@/src/components/auth/ProtectedView';
import { useAuth } from '@/src/features/auth/useAuth';

export default function DashboardPage() {
  const { user, logout } = useAuth();
  const router = useRouter();

  async function handleLogout() {
    await logout();
    router.push('/auth/login');
  }

  return (
    <ProtectedView>
      <main className="page-shell">
        <section className="card wide-card">
          <div className="topbar">
            <h1>Tableau de bord</h1>
            <button className="button ghost" onClick={handleLogout} type="button">
              Se déconnecter
            </button>
          </div>

          <p className="success">Bienvenue {user?.name}, votre session est active.</p>
          <p className="helper">Email: {user?.email}</p>

          <div className="inline-actions mt-2">
            <Link className="button primary" href="/dashboard/courses">
              Voir le catalogue des cours
            </Link>
          </div>

          <hr className="separator" />
          <p className="helper">
            Les modules <strong>live</strong>, <strong>certificats</strong> et <strong>paiement</strong> sont
            masqués dans ce MVP pour prioriser le parcours d’apprentissage.
          </p>
        </section>
      </main>
    </ProtectedView>
  );
}
