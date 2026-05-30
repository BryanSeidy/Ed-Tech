'use client';

import { FormEvent, useEffect, useMemo, useState } from 'react';
import { ProtectedView } from '@/src/components/auth/ProtectedView';
import { AppNav } from '@/src/components/layout/AppNav';
import { LiveRoom } from '@/src/components/live/LiveRoom';
import { useAuth } from '@/src/features/auth/useAuth';
import { HttpError } from '@/src/lib/http';
import { liveService, type LiveCourse, type LiveSession } from '@/src/services/api/liveService';

function formatDateTime(value: string): string {
  return new Intl.DateTimeFormat('fr-FR', {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(new Date(value));
}

function toDatetimeLocalValue(date: Date): string {
  const offsetMs = date.getTimezoneOffset() * 60_000;
  return new Date(date.getTime() - offsetMs).toISOString().slice(0, 16);
}

function sessionStatus(session: LiveSession): string {
  if (session.can_join) return 'Ouverte maintenant';

  const now = Date.now();
  const startsAt = new Date(session.start_at).getTime();
  const endsAt = new Date(session.end_at).getTime();

  if (now > endsAt) return 'Terminée';
  if (now < startsAt) return 'Planifiée';
  return 'Ouverture imminente';
}

export default function PlanningPage() {
  const { user } = useAuth();
  const [sessions, setSessions] = useState<LiveSession[]>([]);
  const [courses, setCourses] = useState<LiveCourse[]>([]);
  const [selectedSession, setSelectedSession] = useState<LiveSession | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);
  const [courseId, setCourseId] = useState('');
  const [title, setTitle] = useState('');
  const [description, setDescription] = useState('');
  const [startAt, setStartAt] = useState(() => toDatetimeLocalValue(new Date(Date.now() + 60 * 60 * 1000)));
  const [durationMinutes, setDurationMinutes] = useState(60);
  const [isSubmitting, setIsSubmitting] = useState(false);

  const canPlanLive = user?.role === 'instructor' || user?.role === 'admin';

  async function loadPlanning() {
    try {
      const [sessionsResponse, coursesResponse] = await Promise.all([
        liveService.upcoming(),
        canPlanLive
          ? (user?.role === 'admin' ? liveService.allCourses() : liveService.teachingCourses())
          : Promise.resolve({ data: [] as LiveCourse[] }),
      ]);
      setSessions(sessionsResponse.data);
      setCourses(coursesResponse.data);
      setError(null);
    } catch (err) {
      setError(err instanceof HttpError ? err.message : 'Impossible de charger le planning live.');
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    if (!user) return;

    void loadPlanning();
    const interval = window.setInterval(() => void loadPlanning(), 60_000);

    return () => window.clearInterval(interval);
    // loadPlanning intentionally captures the latest form-independent role state.
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [user?.id, user?.role]);

  const orderedSessions = useMemo(
    () => [...sessions].sort((a, b) => new Date(a.start_at).getTime() - new Date(b.start_at).getTime()),
    [sessions],
  );

  async function createLiveSession(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setError(null);
    setSuccess(null);

    if (!courseId || !title.trim() || !startAt || durationMinutes < 15) {
      setError('Merci de sélectionner un cours, un titre, une date et une durée valide.');
      return;
    }

    try {
      setIsSubmitting(true);
      await liveService.create({
        course_id: Number(courseId),
        title: title.trim(),
        description: description.trim() || null,
        start_at: new Date(startAt).toISOString(),
        duration_minutes: durationMinutes,
        provider: 'jitsi',
      });
      setTitle('');
      setDescription('');
      setStartAt(toDatetimeLocalValue(new Date(Date.now() + 60 * 60 * 1000)));
      setDurationMinutes(60);
      setSuccess('Classe virtuelle planifiée avec succès.');
      await loadPlanning();
    } catch (err) {
      setError(err instanceof HttpError ? err.message : 'Impossible de planifier cette classe virtuelle.');
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <ProtectedView>
      <main className="page-shell">
        <AppNav />
        <section className="card wide-card planning-shell">
          <div className="topbar">
            <div>
              <h1>Planning des classes virtuelles</h1>
              <p>Consultez vos prochains lives et rejoignez les salons Jitsi au bon moment.</p>
            </div>
          </div>

          {error ? <p className="error">{error}</p> : null}
          {success ? <p className="success">{success}</p> : null}

          {canPlanLive ? (
            <section className="item-card planning-form-card">
              <h2>Planifier une classe virtuelle</h2>
              <form className="form-grid" onSubmit={createLiveSession}>
                <label htmlFor="courseId">
                  Cours
                  <select id="courseId" onChange={(event) => setCourseId(event.target.value)} required value={courseId}>
                    <option value="">Sélectionner un cours</option>
                    {courses.map((course) => (
                      <option key={course.id} value={course.id}>{course.title}</option>
                    ))}
                  </select>
                </label>

                <label htmlFor="liveTitle">
                  Titre
                  <input id="liveTitle" onChange={(event) => setTitle(event.target.value)} required value={title} />
                </label>

                <label htmlFor="liveDescription">
                  Description
                  <textarea
                    id="liveDescription"
                    onChange={(event) => setDescription(event.target.value)}
                    rows={3}
                    value={description}
                  />
                </label>

                <div className="planning-form-row">
                  <label htmlFor="startAt">
                    Date et heure
                    <input
                      id="startAt"
                      onChange={(event) => setStartAt(event.target.value)}
                      required
                      type="datetime-local"
                      value={startAt}
                    />
                  </label>

                  <label htmlFor="durationMinutes">
                    Durée (minutes)
                    <input
                      id="durationMinutes"
                      max={480}
                      min={15}
                      onChange={(event) => setDurationMinutes(Number(event.target.value))}
                      required
                      type="number"
                      value={durationMinutes}
                    />
                  </label>
                </div>

                <button className="button primary" disabled={isSubmitting} type="submit">
                  {isSubmitting ? 'Planification...' : 'Planifier le live'}
                </button>
              </form>
            </section>
          ) : null}

          <section className="planning-list">
            <h2>Prochaines sessions</h2>
            {loading ? <p>Chargement des sessions...</p> : null}
            {!loading && orderedSessions.length === 0 ? (
              <p className="helper">Aucune classe virtuelle planifiée pour le moment.</p>
            ) : null}

            {orderedSessions.map((session) => (
              <article className="item-card live-session-card" key={session.id}>
                <div>
                  <h3>{session.title}</h3>
                  <p>{session.description ?? 'Classe virtuelle Jitsi Meet'}</p>
                  <p className="helper">
                    {session.course?.title ?? 'Cours'} · {formatDateTime(session.start_at)} → {formatDateTime(session.end_at)}
                  </p>
                </div>
                <div className="live-session-actions">
                  <span className={`badge ${session.can_join ? 'success-badge' : 'warning-badge'}`}>
                    {sessionStatus(session)}
                  </span>
                  <button
                    className="button primary"
                    disabled={!session.can_join}
                    onClick={() => setSelectedSession(session)}
                    type="button"
                  >
                    Rejoindre la classe
                  </button>
                </div>
              </article>
            ))}
          </section>
        </section>

        {selectedSession ? (
          <LiveRoom session={selectedSession} onClose={() => setSelectedSession(null)} />
        ) : null}
      </main>
    </ProtectedView>
  );
}
