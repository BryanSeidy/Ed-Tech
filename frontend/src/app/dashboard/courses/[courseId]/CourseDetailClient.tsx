'use client';

import Link from 'next/link';
import { useEffect, useState } from 'react';
import { useParams } from 'next/navigation';
import { ProtectedView } from '@/src/components/auth/ProtectedView';
import { AppNav } from '@/src/components/layout/AppNav';
import { HttpError } from '@/src/lib/http';
import { courseService, type CourseSummary } from '@/src/services/api/courseService';
import { progressService, type CourseProgress } from '@/src/services/api/progressService';

export default function CourseDetailPage() {
  const params = useParams<{ courseId: string }>();
  const courseId = Number(params.courseId);

  const [course, setCourse] = useState<CourseSummary | null>(null);
  const [progress, setProgress] = useState<CourseProgress | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

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
              <h1>{course.title}</h1>
              <p>{course.description}</p>

              <p className="helper">
                Progression: {progress?.completed_lessons ?? 0}/{progress?.total_lessons ?? 0} leçons ({progress?.progress_percentage ?? 0}%)
              </p>

              <div className="list-grid">
                {course.modules?.map((module) => (
                  <article className="item-card" key={module.id}>
                    <h3>{module.position}. {module.title}</h3>
                    <ul>
                      {module.lessons?.map((lesson) => (
                        <li key={lesson.id}>
                          <Link href={`/dashboard/courses/${course.id}/lessons/${lesson.id}`}>
                            {lesson.position}. {lesson.title}
                          </Link>
                        </li>
                      ))}
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
