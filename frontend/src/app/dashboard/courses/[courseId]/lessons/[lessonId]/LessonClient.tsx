'use client';

import Link from 'next/link';
import { useParams } from 'next/navigation';
import { useEffect, useState } from 'react';
import { ProtectedView } from '@/src/components/auth/ProtectedView';
import { AppNav } from '@/src/components/layout/AppNav';
import { HttpError } from '@/src/lib/http';
import { courseService, type LessonSummary } from '@/src/services/api/courseService';
import { quizService } from '@/src/services/api/quizService';

export default function LessonPage() {
  const params = useParams<{ courseId: string; lessonId: string }>();
  const courseId = Number(params.courseId);
  const lessonId = Number(params.lessonId);

  const [lesson, setLesson] = useState<(LessonSummary & { content?: string | null; video_url?: string | null }) | null>(null);
  const [quizId, setQuizId] = useState<number | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!lessonId || !courseId) return;

    void (async () => {
      try {
        const lessonData = await courseService.lesson(lessonId);
        setLesson(lessonData);
        await quizService.getQuiz(lessonId);
        setQuizId(lessonId);
      } catch (e) {
        if (e instanceof HttpError && e.status === 404) {
          setQuizId(null);
          return;
        }
        setError(e instanceof HttpError ? e.message : 'Impossible de charger la leçon.');
      }
    })();
  }, [lessonId, courseId]);

  async function markAsDone() {
    try {
      await courseService.markLessonDone(lessonId);
      setError(null);
    } catch (e) {
      setError(e instanceof HttpError ? e.message : 'Impossible de marquer la leçon comme terminée.');
    }
  }

  return (
    <ProtectedView>
      <main className="page-shell">
        <AppNav />
        <section className="card wide-card">
          <h1>{lesson?.title ?? 'Leçon'}</h1>
          {error ? <p className="error">{error}</p> : null}
          {lesson?.video_url ? (
            <p>
              Vidéo: <a href={lesson.video_url} target="_blank" rel="noreferrer">ouvrir</a>
            </p>
          ) : null}
          <p>{lesson?.content ?? 'Contenu textuel non disponible.'}</p>

          <div className="inline-actions">
            <button className="button primary" onClick={markAsDone} type="button">
              Marquer comme terminée
            </button>
            {quizId ? (
              <Link className="button ghost" href={`/dashboard/courses/${courseId}/quiz/${quizId}`}>
                Passer le quiz
              </Link>
            ) : null}
          </div>
        </section>
      </main>
    </ProtectedView>
  );
}
