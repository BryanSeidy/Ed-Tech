import { cookies } from 'next/headers';
import { AdminDashboard } from '@/src/components/dashboard/admin/AdminDashboard';
import type { AdminAnalytics, AdminCourse, AdminDashboardData, AdminUser, PaginatedResponse } from '@/src/services/api/adminDashboardService';

export const dynamic = 'force-dynamic';

const API_BASE_URL = process.env.NEXT_PUBLIC_API_URL ?? 'http://localhost:8000/api';

type ApiEnvelope<T> = {
  data: T;
};

async function apiGet<T>(path: string): Promise<T> {
  const cookieStore = await cookies();
  const cookieHeader = cookieStore
    .getAll()
    .map((cookie) => `${cookie.name}=${cookie.value}`)
    .join('; ');

  const response = await fetch(`${API_BASE_URL}${path}`, {
    headers: {
      Accept: 'application/json',
      ...(cookieHeader ? { Cookie: cookieHeader } : {}),
    },
    cache: 'no-store',
  });

  const payload = await response.json().catch(() => ({}));

  if (!response.ok) {
    const message = typeof payload?.message === 'string'
      ? payload.message
      : 'Impossible de charger les données administrateur.';

    throw new Error(message);
  }

  return payload as T;
}

async function getAdminDashboardData(): Promise<AdminDashboardData> {
  const [analytics, users, courses] = await Promise.all([
    apiGet<ApiEnvelope<AdminAnalytics>>('/admin/analytics'),
    apiGet<PaginatedResponse<AdminUser>>('/admin/users?per_page=8'),
    apiGet<PaginatedResponse<AdminCourse>>('/admin/courses?per_page=8'),
  ]);

  return {
    analytics: analytics.data,
    users,
    courses,
  };
}

export default async function AdminDashboardPage() {
  let initialData: AdminDashboardData | null = null;
  let initialError: string | null = null;

  try {
    initialData = await getAdminDashboardData();
  } catch (error) {
    initialError = error instanceof Error ? error.message : 'Impossible de charger les données administrateur.';
  }

  return <AdminDashboard initialData={initialData} initialError={initialError} />;
}
