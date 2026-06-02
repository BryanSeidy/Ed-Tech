'use client';

import { useEffect, useState } from 'react';
import { ProtectedView } from '@/src/components/auth/ProtectedView';
import { InstructorDashboard } from '@/src/components/dashboard/instructor/InstructorDashboard';
import { useAuth } from '@/src/features/auth/useAuth';
import { HttpError } from '@/src/lib/http';
import { instructorDashboardService, type InstructorDashboardData } from '@/src/services/api/instructorDashboardService';

function InstructorDashboardLoader() {
  const { user } = useAuth();
  const [dashboardData, setDashboardData] = useState<InstructorDashboardData | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    let mounted = true;

    void (async () => {
      try {
        setError(null);
        const nextDashboardData = await instructorDashboardService.dashboard();

        if (mounted) {
          setDashboardData(nextDashboardData);
        }
      } catch (err) {
        if (!mounted) {
          return;
        }

        setError(err instanceof HttpError ? err.message : 'Impossible de charger votre dashboard formateur.');
      }
    })();

    return () => {
      mounted = false;
    };
  }, []);

  if (error) {
    return (
      <main className="auth-shell">
        <section className="card">
          <h1>Dashboard formateur</h1>
          <p className="error">{error}</p>
        </section>
      </main>
    );
  }

  if (!dashboardData) {
    return (
      <main className="auth-shell">
        <section className="card">
          <p>Chargement de votre dashboard formateur...</p>
        </section>
      </main>
    );
  }

  return (
    <InstructorDashboard
      instructorName={user?.name ?? 'Formateur'}
      stats={dashboardData.stats}
      courses={dashboardData.courses}
      liveSessions={dashboardData.liveSessions}
    />
  );
}

export default function InstructorDashboardPage() {
  return (
    <ProtectedView allowedRoles={['instructor']}>
      <InstructorDashboardLoader />
    </ProtectedView>
  );
}
