import { cookies } from 'next/headers';
import { redirect } from 'next/navigation';
import { getRoleDashboardRoute } from '@/src/features/auth/roleRoutes';
import type { AuthResponse, AuthUser } from '@/src/features/auth/types';

const API_BASE_URL = process.env.NEXT_PUBLIC_API_URL ?? 'http://localhost:8000/api';

type ApiEnvelope<T> = {
  data: T;
};

type ApiErrorShape = {
  message?: string;
};

export type InstructorStats = {
  total_students: number;
  published_courses: number;
  average_completion_rate: number;
  upcoming_live_sessions: number;
  quiz_success_rate: number;
};

export type InstructorCourse = {
  id: number;
  title: string;
  students_count: number;
  is_published: boolean;
  publication_state: 'published' | 'draft';
  modules_count: number;
  lessons_count: number;
  upcoming_live_sessions_count: number;
  created_at: string | null;
};

export type InstructorLiveSession = {
  id: number;
  course_id: number;
  course_title: string | null;
  title: string;
  description: string | null;
  start_at: string;
  end_at: string;
  duration_minutes: number;
  provider: 'jitsi';
  room_name: string;
  can_start: boolean;
};

async function serverApi<T>(path: string): Promise<T> {
  const cookieHeader = (await cookies()).toString();
  const response = await fetch(`${API_BASE_URL}${path}`, {
    credentials: 'include',
    cache: 'no-store',
    headers: {
      Accept: 'application/json',
      Cookie: cookieHeader,
    },
  });

  if (response.status === 401) {
    redirect('/auth/login');
  }

  const payload = (await response.json().catch(() => ({}))) as T & ApiErrorShape;

  if (!response.ok) {
    throw new Error(payload.message ?? 'Impossible de charger les données du dashboard formateur.');
  }

  return payload;
}

async function getAuthenticatedUser(): Promise<AuthUser> {
  const response = await serverApi<AuthResponse>('/auth/me');
  const user = response.user ?? response.data;

  if (!user) {
    redirect('/auth/login');
  }

  if (user.role !== 'instructor') {
    redirect(getRoleDashboardRoute(user.role));
  }

  return user;
}

export async function getInstructorDashboardData() {
  const user = await getAuthenticatedUser();
  const [statsResponse, coursesResponse, liveSessionsResponse] = await Promise.all([
    serverApi<ApiEnvelope<InstructorStats>>('/instructor/stats'),
    serverApi<ApiEnvelope<InstructorCourse[]>>('/instructor/courses'),
    serverApi<ApiEnvelope<InstructorLiveSession[]>>('/instructor/live-sessions'),
  ]);

  return {
    user,
    stats: statsResponse.data,
    courses: coursesResponse.data,
    liveSessions: liveSessionsResponse.data,
  };
}
