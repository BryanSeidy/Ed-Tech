'use client';

import { usePathname, useRouter } from 'next/navigation';
import { useEffect } from 'react';
import { getDashboardRoleFromPathname, getRoleDashboardRoute } from '@/src/features/auth/roleRoutes';
import type { UserRole } from '@/src/features/auth/types';
import { useAuth } from '@/src/features/auth/useAuth';

type ProtectedViewProps = Readonly<{
  children: React.ReactNode;
  allowedRoles?: UserRole[];
}>;

export function ProtectedView({ children, allowedRoles }: ProtectedViewProps) {
  const { user, isLoading } = useAuth();
  const pathname = usePathname();
  const router = useRouter();

  useEffect(() => {
    if (isLoading) {
      return;
    }

    if (!user) {
      router.replace('/auth/login');
      return;
    }

    const expectedDashboardRoute = getRoleDashboardRoute(user.role);
    const requestedDashboardRole = getDashboardRoleFromPathname(pathname);

    if (allowedRoles && !allowedRoles.includes(user.role)) {
      router.replace(expectedDashboardRoute);
      return;
    }

    if (requestedDashboardRole && requestedDashboardRole !== user.role) {
      router.replace(expectedDashboardRoute);
    }
  }, [allowedRoles, isLoading, pathname, router, user]);

  if (isLoading || !user) {
    return (
      <main className="auth-shell">
        <section className="card">
          <p>Vérification de votre session...</p>
        </section>
      </main>
    );
  }

  const expectedDashboardRoute = getRoleDashboardRoute(user.role);
  const requestedDashboardRole = getDashboardRoleFromPathname(pathname);
  const isForbiddenByRole = allowedRoles ? !allowedRoles.includes(user.role) : false;
  const isWrongRoleDashboard = Boolean(requestedDashboardRole && requestedDashboardRole !== user.role);

  if (isForbiddenByRole || isWrongRoleDashboard) {
    return (
      <main className="auth-shell">
        <section className="card">
          <p>Redirection vers votre espace: {expectedDashboardRoute}</p>
        </section>
      </main>
    );
  }

  return <>{children}</>;
}
