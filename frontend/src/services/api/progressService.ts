import { http } from '@/src/lib/http';

export type CourseProgress = {
  course_id: number;
  user_id?: number;
  total_lessons: number;
  completed_lessons: number;
  progress_percentage: number;
};

export const progressService = {
  byCourse: (courseId: number) => http<CourseProgress>(`/courses/${courseId}/progress`),
};
