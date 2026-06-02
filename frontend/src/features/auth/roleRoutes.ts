import type { UserRole } from '@/src/features/auth/types';

export const roleDashboardRoutes = {
  student: '/dashboard/student',
  instructor: '/dashboard/instructor',
  admin: '/dashboard/admin',
} as const satisfies Record<UserRole, string>;

export function getRoleDashboardRoute(role: UserRole): string {
  return roleDashboardRoutes[role];
}

export function getDashboardRoleFromPathname(pathname: string): UserRole | null {
  if (pathname === roleDashboardRoutes.student || pathname.startsWith(`${roleDashboardRoutes.student}/`)) {
    return 'student';
  }

  if (pathname === roleDashboardRoutes.instructor || pathname.startsWith(`${roleDashboardRoutes.instructor}/`)) {
    return 'instructor';
  }

  if (pathname === roleDashboardRoutes.admin || pathname.startsWith(`${roleDashboardRoutes.admin}/`)) {
    return 'admin';
  }

  return null;
}
