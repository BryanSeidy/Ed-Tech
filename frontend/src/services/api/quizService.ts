import { http } from '@/src/lib/http';

export type QuizAnswer = { id: number; answer_text: string; is_correct?: boolean };
export type QuizQuestion = { id: number; question_text: string; type: string; answers: QuizAnswer[] };
export type QuizData = { id: number; title: string; questions: QuizQuestion[] };

export const quizService = {
  getQuiz: (quizId: number) => http<{ success: boolean; data: QuizData }>(`/quizzes/${quizId}`),
  startAttempt: (quizId: number) =>
    http<{ attempt: { id: number } }>(`/quizzes/${quizId}/attempts`, { method: 'POST' }),
  submitAttempt: (attemptId: number, answers: Array<{ question_id: number; answer_id: number }>) =>
    http<{
      message: string;
      attempt: { id: number; score: number };
      results: { score: number; total_questions: number; percentage: number };
    }>(`/attempts/${attemptId}`, {
      method: 'PUT',
      body: { answers },
    }),
};
