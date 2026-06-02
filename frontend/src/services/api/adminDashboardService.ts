import { http } from '@/src/lib/http';

export type AdminRole = 'admin' | 'instructor' | 'student';
export type CoursePublicationStatus = 'pending' | 'published' | 'archived';

export type AdminAnalytics = {
  users_by_role: Record<AdminRole, number>;
  total_users: number;
  total_published_courses: number;
  total_certificates_issued: number;
  total_live_sessions_held: number;
};

export type AdminUser = {
  id: number;
  name: string;
  email: string;
  role: AdminRole;
  created_at: string | null;
};

export type AdminCourse = {
  id: number;
  title: string;
  status: CoursePublicationStatus;
  is_published: boolean;
  instructor: {
    id: number;
    name: string;
    email: string;
  } | null;
  modules_count: number;
  created_at: string | null;
};

export type PaginationLinks = {
  first: string | null;
  last: string | null;
  prev: string | null;
  next: string | null;
};

export type PaginationMeta = {
  current_page: number;
  from: number | null;
  last_page: number;
  per_page: number;
  to: number | null;
  total: number;
};

export type PaginatedResponse<T> = {
  data: T[];
  links?: PaginationLinks;
  meta?: PaginationMeta;
  current_page?: number;
  last_page?: number;
  per_page?: number;
  total?: number;
  from?: number | null;
  to?: number | null;
};

type ApiEnvelope<T> = {
  data: T;
};

export type AdminDashboardData = {
  analytics: AdminAnalytics;
  users: PaginatedResponse<AdminUser>;
  courses: PaginatedResponse<AdminCourse>;
};

export type UserListParams = {
  search?: string;
  role?: AdminRole | 'all';
  page?: number;
  perPage?: number;
};

export type CourseListParams = {
  search?: string;
  status?: CoursePublicationStatus | 'all';
  page?: number;
  perPage?: number;
};

function toQuery(params: Record<string, string | number | undefined>): string {
  const searchParams = new URLSearchParams();

  Object.entries(params).forEach(([key, value]) => {
    if (value !== undefined && value !== '') {
      searchParams.set(key, String(value));
    }
  });

  const query = searchParams.toString();

  return query ? `?${query}` : '';
}

export const adminDashboardService = {
  analytics: () => http<ApiEnvelope<AdminAnalytics>>('/admin/analytics'),
  users: (params: UserListParams = {}) => http<PaginatedResponse<AdminUser>>(
    `/admin/users${toQuery({
      search: params.search,
      role: params.role === 'all' ? undefined : params.role,
      page: params.page,
      per_page: params.perPage,
    })}`,
  ),
  courses: (params: CourseListParams = {}) => http<PaginatedResponse<AdminCourse>>(
    `/admin/courses${toQuery({
      search: params.search,
      status: params.status === 'all' ? undefined : params.status,
      page: params.page,
      per_page: params.perPage,
    })}`,
  ),
  updateUserRole: (userId: number, role: AdminRole) => http<ApiEnvelope<AdminUser>>(`/admin/users/${userId}/role`, {
    method: 'PATCH',
    body: { role },
  }),
  updateCourseStatus: (courseId: number, status: CoursePublicationStatus) => http<ApiEnvelope<AdminCourse>>(`/admin/courses/${courseId}/status`, {
    method: 'PATCH',
    body: { status },
  }),
  async dashboard(): Promise<AdminDashboardData> {
    const [analytics, users, courses] = await Promise.all([
      this.analytics(),
      this.users({ perPage: 8 }),
      this.courses({ perPage: 8 }),
    ]);

    return {
      analytics: analytics.data,
      users,
      courses,
    };
  },
};
