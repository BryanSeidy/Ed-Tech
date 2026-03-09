'use client';

import { useRouter } from 'next/navigation';
import { ProtectedView } from '@/src/components/auth/ProtectedView';
import { useAuth } from '@/src/features/auth/useAuth';

export default function DashboardPage() {
  const { user, logout } = useAuth();
  const router = useRouter();

  async function handleLogout() {
    await logout();
    router.push('/login');
  }

  return (
    <ProtectedView>
      <main className="auth-shell">
        <section className="card">
          <div className="topbar">
            <h1>Tableau de bord</h1>
            <button className="button ghost" onClick={handleLogout} type="button">
              Se déconnecter
            </button>
          </div>
          <p className="success">Authentification réussie ✅</p>
          <p>Bienvenue {user?.name}, votre session est active.</p>
          <p className="helper">Email: {user?.email}</p>
        </section>
      </main>
    </ProtectedView>
  );
}
