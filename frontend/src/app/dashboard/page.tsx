'use client';

import { redirect } from 'next/navigation';

import { useRouter } from 'next/navigation';
import { useEffect } from 'react';
import { ProtectedView } from '@/src/components/auth/ProtectedView';
import { getRoleDashboardRoute } from '@/src/features/auth/roleRoutes';
import { useAuth } from '@/src/features/auth/useAuth';

function RoleDashboardRedirect() {
  const { user } = useAuth();
  const router = useRouter();

  useEffect(() => {
    if (user) {
      router.replace(getRoleDashboardRoute(user.role));
    }
  }, [router, user]);

  return (
    <main className="auth-shell">
      <section className="card">
        <p>Redirection vers votre espace personnalisé...</p>
      </section>
    </main>
  );
}

export default function DashboardIndexPage() {
  return (
    <ProtectedView>
      <RoleDashboardRedirect />
    </ProtectedView>
  );
}
