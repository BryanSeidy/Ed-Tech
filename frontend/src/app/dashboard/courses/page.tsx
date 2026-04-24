'use client';

import Link from 'next/link';
import { useEffect, useState } from 'react';
import { ProtectedView } from '@/src/components/auth/ProtectedView';
import { HttpError } from '@/src/lib/http';
import { courseService, type CourseSummary } from '@/src/services/api/courseService';

export default function CoursesPage() {
  const [courses, setCourses] = useState<CourseSummary[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    void (async () => {
      try {
        const data = await courseService.list();
        setCourses(data.data ?? []);
      } catch (e) {
        setError(e instanceof HttpError ? e.message : 'Impossible de charger les cours.');
      } finally {
        setLoading(false);
      }
    })();
  }, []);

  return (
    <ProtectedView>
      <main className="page-shell">
        <section className="card wide-card">
          <h1>Catalogue des cours</h1>
          {loading ? <p>Chargement des cours...</p> : null}
          {error ? <p className="error">{error}</p> : null}
          {!loading && !error ? (
            <div className="list-grid">
              {courses.map((course) => (
                <article className="item-card" key={course.id}>
                  <h3>{course.title}</h3>
                  <p>{course.description}</p>
                  <Link className="button primary" href={`/dashboard/courses/${course.id}`}>
                    Continuer
                  </Link>
                </article>
              ))}
            </div>
          ) : null}
        </section>
      </main>
    </ProtectedView>
  );
}
