'use client';

import Link from 'next/link';
import { useEffect, useState } from 'react';
import { useParams } from 'next/navigation';
import { ProtectedView } from '@/src/components/auth/ProtectedView';
import { AppNav } from '@/src/components/layout/AppNav';
import { useAuth } from '@/src/features/auth/useAuth';
import { HttpError } from '@/src/lib/http';
import { courseService, type CourseSummary, type LessonSummary } from '@/src/services/api/courseService';
import { progressService, type CourseProgress } from '@/src/services/api/progressService';

function lockMessage(lesson: LessonSummary): string {
  if (lesson.learning_state?.lock_reason === 'quiz_not_passed') {
    return 'Quiz précédent non validé';
  }

  if (lesson.learning_state?.lock_reason === 'previous_lesson_not_completed') {
    return 'Leçon précédente non terminée';
  }

  return 'Verrouillé';
}

export default function CourseDetailPage() {
  const params = useParams<{ courseId: string }>();
  const courseId = Number(params.courseId);
  const { user } = useAuth();

  const [course, setCourse] = useState<CourseSummary | null>(null);
  const [progress, setProgress] = useState<CourseProgress | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const canCreateQuiz = user?.role === 'instructor' || user?.role === 'admin';

  useEffect(() => {
    if (!courseId) return;

    void (async () => {
      try {
        await courseService.enroll(courseId).catch(() => null);
        const [courseData, progressData] = await Promise.all([
          courseService.detail(courseId),
          progressService.byCourse(courseId),
        ]);
        setCourse(courseData);
        setProgress(progressData);
      } catch (e) {
        setError(e instanceof HttpError ? e.message : 'Impossible de charger ce cours.');
      } finally {
        setLoading(false);
      }
    })();
  }, [courseId]);

  return (
    <ProtectedView>
      <main className="page-shell">
        <AppNav />
        <section className="card wide-card">
          {loading ? <p>Chargement du cours...</p> : null}
          {error ? <p className="error">{error}</p> : null}
          {course ? (
            <>
              <div className="topbar">
                <div>
                  <h1>{course.title}</h1>
                  <p>{course.description}</p>
                </div>
                {canCreateQuiz ? (
                  <Link className="button ghost" href={`/dashboard/courses/${course.id}/quiz/create`}>
                    Créer un QCM
                  </Link>
                ) : null}
              </div>

              <p className="helper">
                Progression: {progress?.completed_lessons ?? 0}/{progress?.total_lessons ?? 0} leçons ({progress?.progress_percentage ?? 0}%)
              </p>

              <div className="list-grid">
                {course.modules?.map((module) => (
                  <article className="item-card" key={module.id}>
                    <h3>{module.position}. {module.title}</h3>
                    <ul>
                      {module.lessons?.map((lesson) => {
                        const isLocked = Boolean(lesson.learning_state?.is_locked ?? lesson.is_locked);
                        return (
                          <li className={isLocked ? 'locked-lesson' : undefined} key={lesson.id}>
                            {isLocked ? (
                              <span className="locked-link" aria-disabled="true">
                                {lesson.position}. {lesson.title}
                                <span className="badge warning-badge">{lockMessage(lesson)}</span>
                              </span>
                            ) : (
                              <Link href={`/dashboard/courses/${course.id}/lessons/${lesson.id}`}>
                                {lesson.position}. {lesson.title}
                                {lesson.learning_state?.quiz_passed ? (
                                  <span className="badge success-badge">Quiz validé</span>
                                ) : null}
                              </Link>
                            )}
                          </li>
                        );
                      })}
                    </ul>
                  </article>
                ))}
              </div>
            </>
          ) : null}
        </section>
      </main>
    </ProtectedView>
  );
}
