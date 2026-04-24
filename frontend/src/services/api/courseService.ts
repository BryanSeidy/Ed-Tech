import { http } from '@/src/lib/http';

export type LessonSummary = {
  id: number;
  module_id: number;
  title: string;
  content?: string | null;
  video_url?: string | null;
  duration?: number | null;
  position: number;
};

export type ModuleSummary = {
  id: number;
  course_id: number;
  title: string;
  position: number;
  lessons: LessonSummary[];
};

export type CourseSummary = {
  id: number;
  title: string;
  description: string;
  thumbnail?: string | null;
  is_published: boolean;
  modules?: ModuleSummary[];
};

export type PaginatedCourses = {
  data: CourseSummary[];
};

export const courseService = {
  list: () => http<PaginatedCourses>('/courses?published=1'),
  detail: (courseId: number) => http<CourseSummary>(`/courses/${courseId}`),
  enroll: (courseId: number) => http<{ message: string }>(`/courses/${courseId}/enroll`, { method: 'POST' }),
  lesson: (lessonId: number) => http<LessonSummary & { module: ModuleSummary; progress: Array<{ completed: boolean }> }>(`/lessons/${lessonId}`),
  markLessonDone: (lessonId: number) =>
    http<{ message: string }>(`/lessons/${lessonId}/progress`, { method: 'POST' }),
};
