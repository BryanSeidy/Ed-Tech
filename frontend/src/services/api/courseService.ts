import { http } from '@/src/lib/http';

export type LearningState = {
  is_locked: boolean;
  lock_reason: 'quiz_not_passed' | 'previous_lesson_not_completed' | null;
  locked_by_lesson_id: number | null;
  locked_by_quiz_id: number | null;
  is_completed: boolean;
  quiz_id: number | null;
  quiz_passed: boolean | null;
  quiz_score: number | null;
  passing_score: number | null;
};

export type LessonSummary = {
  id: number;
  module_id: number;
  title: string;
  content?: string | null;
  video_url?: string | null;
  duration?: number | null;
  position: number;
  is_locked?: boolean;
  quiz_id?: number | null;
  quiz_passed?: boolean | null;
  learning_state?: LearningState;
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
  lesson: (lessonId: number) =>
    http<LessonSummary & { module: ModuleSummary; progress: Array<{ completed: boolean }> }>(`/lessons/${lessonId}`),
  markLessonDone: (lessonId: number) =>
    http<{ message: string }>(`/lessons/${lessonId}/progress`, { method: 'POST' }),
};
