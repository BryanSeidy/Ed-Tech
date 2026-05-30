import { http } from '@/src/lib/http';

export type QuizAnswer = { id: number; answer_text: string; is_correct?: boolean };
export type QuizQuestion = { id: number; question_text: string; type: string; answers: QuizAnswer[] };
export type QuizData = { id: number; title: string; passing_score: number; questions: QuizQuestion[] };
export type CreateQuizPayload = {
  lesson_id: number;
  title: string;
  description?: string;
  passing_score: number;
  is_published: boolean;
  questions: Array<{
    question_text: string;
    type: 'single_choice';
    answers: Array<{ answer_text: string; is_correct: boolean }>;
  }>;
};

export const quizService = {
  getQuiz: (quizId: number) => http<{ success: boolean; data: QuizData }>(`/quizzes/${quizId}`),
  startAttempt: (quizId: number) =>
    http<{ attempt: { id: number } }>(`/quizzes/${quizId}/attempts`, { method: 'POST' }),
  createQuiz: (payload: CreateQuizPayload) =>
    http<{ success: boolean; data: QuizData; message: string }>('/quizzes', { method: 'POST', body: payload }),
  submitAttempt: (attemptId: number, answers: Array<{ question_id: number; answer_id: number }>) =>
    http<{
      message: string;
      attempt: { id: number; score: number };
      results: { score: number; total_questions: number; percentage: number; passing_score: number; passed: boolean };
    }>(`/attempts/${attemptId}`, {
      method: 'PUT',
      body: { answers },
    }),
};
