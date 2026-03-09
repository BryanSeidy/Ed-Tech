'use client';

import { useRouter } from 'next/navigation';
import { useEffect } from 'react';
import { useAuth } from '@/src/features/auth/useAuth';

export function ProtectedView({ children }: Readonly<{ children: React.ReactNode }>) {
  const { user, isLoading } = useAuth();
  const router = useRouter();

  useEffect(() => {
    if (!isLoading && !user) {
      router.replace('/login');
    }
  }, [isLoading, user, router]);

  if (isLoading || !user) {
    return (
      <main className="auth-shell">
        <section className="card">
          <p>Vérification de votre session...</p>
        </section>
      </main>
    );
  }

  return <>{children}</>;
}
