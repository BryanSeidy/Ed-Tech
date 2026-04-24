'use client';

import { useParams } from 'next/navigation';
import { useEffect, useMemo, useState } from 'react';
import { ProtectedView } from '@/src/components/auth/ProtectedView';
import { AppNav } from '@/src/components/layout/AppNav';
import { HttpError } from '@/src/lib/http';
import { quizService, type QuizData } from '@/src/services/api/quizService';

export default function QuizPage() {
  const params = useParams<{ quizId: string }>();
  const quizId = Number(params.quizId);

  const [quiz, setQuiz] = useState<QuizData | null>(null);
  const [answers, setAnswers] = useState<Record<number, number>>({});
  const [attemptId, setAttemptId] = useState<number | null>(null);
  const [result, setResult] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!quizId) return;

    void (async () => {
      try {
        const data = await quizService.getQuiz(quizId);
        setQuiz(data.data);
      } catch (e) {
        setError(e instanceof HttpError ? e.message : 'Impossible de charger le quiz.');
      }
    })();
  }, [quizId]);

  const isComplete = useMemo(
    () => (quiz ? quiz.questions.every((question) => Boolean(answers[question.id])) : false),
    [quiz, answers],
  );

  async function submitQuiz() {
    if (!quiz) return;

    try {
      const nextAttemptId = attemptId ?? (await quizService.startAttempt(quiz.id)).attempt.id;
      setAttemptId(nextAttemptId);

      const payload = quiz.questions.map((question) => ({
        question_id: question.id,
        answer_id: answers[question.id],
      }));
      const submission = await quizService.submitAttempt(nextAttemptId, payload);
      setResult(
        `Score: ${submission.results.score}/${submission.results.total_questions} (${submission.results.percentage}%)`,
      );
      setError(null);
    } catch (e) {
      setError(e instanceof HttpError ? e.message : 'Erreur lors de la soumission du quiz.');
    }
  }

  return (
    <ProtectedView>
      <main className="page-shell">
        <AppNav />
        <section className="card wide-card">
          <h1>{quiz?.title ?? 'Quiz'}</h1>
          {error ? <p className="error">{error}</p> : null}
          {result ? <p className="success">{result}</p> : null}

          {quiz?.questions.map((question) => (
            <article className="item-card" key={question.id}>
              <h3>{question.question_text}</h3>
              <div className="inline-actions">
                {question.answers.map((answer) => (
                  <label className="checkbox" key={answer.id}>
                    <input
                      type="radio"
                      name={`question-${question.id}`}
                      checked={answers[question.id] === answer.id}
                      onChange={() => setAnswers((prev) => ({ ...prev, [question.id]: answer.id }))}
                    />
                    {answer.answer_text}
                  </label>
                ))}
              </div>
            </article>
          ))}

          <button className="button primary" disabled={!isComplete} onClick={submitQuiz} type="button">
            Soumettre le quiz
          </button>
        </section>
      </main>
    </ProtectedView>
  );
}
