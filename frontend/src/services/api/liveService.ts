import { http } from '@/src/lib/http';

export type LiveCourse = {
  id: number;
  title: string;
};

export type LiveSession = {
  id: number;
  course_id: number;
  lesson_id: number | null;
  title: string;
  description: string | null;
  start_at: string;
  end_at: string;
  duration_minutes: number;
  provider: 'jitsi';
  room_name: string;
  created_by: number;
  course: LiveCourse | null;
  creator: { id: number; name: string; email: string } | null;
  can_join: boolean;
  join_opens_at: string;
  is_host: boolean;
};

export type LiveRoomPayload = {
  provider: 'jitsi';
  room_name: string;
  url: string;
  join_token: string | null;
  expires_at: string;
  metadata: Record<string, unknown>;
};

export type LiveJoinResponse = {
  data: {
    session: LiveSession;
    room: LiveRoomPayload;
  };
};

export type LiveMessage = {
  id: number;
  live_session_id: number;
  message: string;
  created_at: string | null;
  user: { id: number; name: string } | null;
};

export type CreateLiveSessionPayload = {
  course_id: number;
  lesson_id?: number | null;
  title: string;
  description?: string | null;
  start_at: string;
  duration_minutes: number;
  provider?: 'jitsi';
};

type PaginatedCourses = { data: LiveCourse[] };

export const liveService = {
  upcoming: () => http<{ data: LiveSession[] }>('/live-sessions/upcoming'),
  byCourse: (courseId: number) => http<{ data: LiveSession[] }>(`/courses/${courseId}/live-sessions`),
  create: (payload: CreateLiveSessionPayload) =>
    http<{ data: LiveSession }>('/live-sessions', { method: 'POST', body: payload }),
  join: (sessionId: number) => http<LiveJoinResponse>(`/live-sessions/${sessionId}/join`),
  messages: (sessionId: number, afterId?: number) => {
    const query = afterId ? `?after_id=${afterId}` : '';
    return http<{ data: LiveMessage[] }>(`/live-sessions/${sessionId}/messages${query}`);
  },
  sendMessage: (sessionId: number, message: string) =>
    http<{ data: LiveMessage }>(`/live-sessions/${sessionId}/messages`, {
      method: 'POST',
      body: { message },
    }),
  teachingCourses: () => http<PaginatedCourses>('/courses/my/created'),
  allCourses: () => http<PaginatedCourses>('/courses'),
};
