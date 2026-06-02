import { http } from '@/src/lib/http';

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

type ApiEnvelope<T> = {
  data: T;
};

export type InstructorDashboardData = {
  stats: InstructorStats;
  courses: InstructorCourse[];
  liveSessions: InstructorLiveSession[];
};

export const instructorDashboardService = {
  stats: () => http<ApiEnvelope<InstructorStats>>('/instructor/stats'),
  courses: () => http<ApiEnvelope<InstructorCourse[]>>('/instructor/courses'),
  liveSessions: () => http<ApiEnvelope<InstructorLiveSession[]>>('/instructor/live-sessions'),
  async dashboard(): Promise<InstructorDashboardData> {
    const [statsResponse, coursesResponse, liveSessionsResponse] = await Promise.all([
      this.stats(),
      this.courses(),
      this.liveSessions(),
    ]);

    return {
      stats: statsResponse.data,
      courses: coursesResponse.data,
      liveSessions: liveSessionsResponse.data,
    };
  },
};
