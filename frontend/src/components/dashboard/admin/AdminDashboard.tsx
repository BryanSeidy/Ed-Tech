'use client';

import { useMemo, useState, useTransition } from 'react';
import { ProtectedView } from '@/src/components/auth/ProtectedView';
import { useAuth } from '@/src/features/auth/useAuth';
import { HttpError } from '@/src/lib/http';
import {
  adminDashboardService,
  type AdminCourse,
  type AdminDashboardData,
  type AdminRole,
  type AdminUser,
  type CoursePublicationStatus,
  type PaginatedResponse,
} from '@/src/services/api/adminDashboardService';

type AdminDashboardProps = Readonly<{
  initialData: AdminDashboardData | null;
  initialError?: string | null;
}>;

const ROLE_LABELS: Record<AdminRole, string> = {
  admin: 'Admin',
  instructor: 'Formateur',
  student: 'Apprenant',
};

const STATUS_LABELS: Record<CoursePublicationStatus, string> = {
  pending: 'En attente',
  published: 'Publié',
  archived: 'Archivé',
};

function getPagination<T>(page: PaginatedResponse<T>) {
  return {
    currentPage: page.meta?.current_page ?? page.current_page ?? 1,
    lastPage: page.meta?.last_page ?? page.last_page ?? 1,
    total: page.meta?.total ?? page.total ?? page.data.length,
  };
}

function formatDate(value: string | null): string {
  if (!value) {
    return '—';
  }

  return new Intl.DateTimeFormat('fr-FR', { dateStyle: 'medium' }).format(new Date(value));
}

function initials(name: string): string {
  return name
    .split(' ')
    .map((part) => part[0])
    .join('')
    .slice(0, 2)
    .toUpperCase();
}

function roleBadgeClass(role: AdminRole): string {
  const classes = {
    admin: 'bg-violet-50 text-violet-700 ring-violet-200',
    instructor: 'bg-blue-50 text-blue-700 ring-blue-200',
    student: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
  } satisfies Record<AdminRole, string>;

  return classes[role];
}

function statusBadgeClass(status: CoursePublicationStatus): string {
  const classes = {
    pending: 'bg-amber-50 text-amber-700 ring-amber-200',
    published: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    archived: 'bg-slate-100 text-slate-600 ring-slate-200',
  } satisfies Record<CoursePublicationStatus, string>;

  return classes[status];
}

function MetricCard({ label, value, helper, accent }: Readonly<{ label: string; value: number; helper: string; accent: string }>) {
  return (
    <article className="relative overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-[0_18px_45px_rgba(15,23,42,0.06)]">
      <div className={`absolute right-5 top-5 h-2.5 w-2.5 rounded-full ${accent}`} />
      <p className="text-sm font-semibold text-slate-500">{label}</p>
      <p className="mt-4 text-4xl font-black tracking-[-0.04em] text-slate-950">{value.toLocaleString('fr-FR')}</p>
      <p className="mt-3 text-sm leading-6 text-slate-500">{helper}</p>
    </article>
  );
}

function EmptyState({ title, description }: Readonly<{ title: string; description: string }>) {
  return (
    <div className="rounded-3xl border border-dashed border-blue-200 bg-blue-50/50 p-8 text-center">
      <p className="text-base font-bold text-slate-950">{title}</p>
      <p className="mt-2 text-sm leading-6 text-slate-500">{description}</p>
    </div>
  );
}

function UsersTable({ users, onRoleChange, pendingId }: Readonly<{
  users: AdminUser[];
  onRoleChange: (userId: number, role: AdminRole) => void;
  pendingId: number | null;
}>) {
  if (users.length === 0) {
    return <EmptyState title="Aucun utilisateur trouvé" description="Ajustez la recherche ou les filtres pour afficher les comptes de la plateforme." />;
  }

  return (
    <div className="overflow-hidden rounded-3xl border border-slate-200 bg-white">
      <div className="hidden grid-cols-[minmax(220px,1.4fr)_minmax(220px,1fr)_150px_160px_150px] gap-4 border-b border-slate-100 bg-slate-50 px-5 py-3 text-xs font-bold uppercase tracking-[0.14em] text-slate-400 lg:grid">
        <span>Avatar / Nom</span>
        <span>Email</span>
        <span>Rôle</span>
        <span>Inscription</span>
        <span>Action</span>
      </div>
      <div className="divide-y divide-slate-100">
        {users.map((user) => (
          <article key={user.id} className="grid gap-4 px-5 py-4 lg:grid-cols-[minmax(220px,1.4fr)_minmax(220px,1fr)_150px_160px_150px] lg:items-center">
            <div className="flex items-center gap-3">
              <div className="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-blue-600 to-sky-400 text-sm font-black text-white shadow-sm">
                {initials(user.name)}
              </div>
              <div>
                <h3 className="font-bold text-slate-950">{user.name}</h3>
                <p className="text-xs text-slate-400">ID #{user.id}</p>
              </div>
            </div>
            <p className="break-all text-sm font-medium text-slate-600">{user.email}</p>
            <span className={`w-fit rounded-full px-3 py-1 text-xs font-bold ring-1 ${roleBadgeClass(user.role)}`}>{ROLE_LABELS[user.role]}</span>
            <p className="text-sm text-slate-500">{formatDate(user.created_at)}</p>
            <select
              aria-label={`Modifier le rôle de ${user.name}`}
              className="rounded-2xl border-slate-200 bg-slate-50 px-3 py-2 text-sm font-bold text-slate-700 disabled:opacity-60"
              value={user.role}
              disabled={pendingId === user.id}
              onChange={(event) => onRoleChange(user.id, event.target.value as AdminRole)}
            >
              {Object.entries(ROLE_LABELS).map(([role, label]) => <option key={role} value={role}>{label}</option>)}
            </select>
          </article>
        ))}
      </div>
    </div>
  );
}

function CoursesModeration({ courses, onStatusChange, pendingId }: Readonly<{
  courses: AdminCourse[];
  onStatusChange: (courseId: number, status: CoursePublicationStatus) => void;
  pendingId: number | null;
}>) {
  if (courses.length === 0) {
    return <EmptyState title="Aucun cours à modérer" description="Les cours créés par les formateurs apparaîtront ici dès leur soumission." />;
  }

  return (
    <div className="space-y-3">
      {courses.map((course) => {
        const primaryAction: CoursePublicationStatus = course.status === 'published' ? 'archived' : 'published';

        return (
          <article key={course.id} className="flex flex-col gap-4 rounded-3xl border border-slate-200 bg-white p-5 shadow-[0_14px_35px_rgba(15,23,42,0.04)] md:flex-row md:items-center md:justify-between">
            <div className="min-w-0">
              <div className="flex flex-wrap items-center gap-2">
                <h3 className="truncate text-lg font-black tracking-[-0.02em] text-slate-950">{course.title}</h3>
                <span className={`rounded-full px-3 py-1 text-xs font-bold ring-1 ${statusBadgeClass(course.status)}`}>{STATUS_LABELS[course.status]}</span>
              </div>
              <p className="mt-2 text-sm text-slate-500">
                {course.instructor?.name ?? 'Instructeur non assigné'} · {course.modules_count} module(s) · créé le {formatDate(course.created_at)}
              </p>
            </div>
            <div className="flex flex-wrap gap-2">
              <button
                type="button"
                disabled={pendingId === course.id}
                onClick={() => onStatusChange(course.id, primaryAction)}
                className="rounded-2xl bg-blue-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
              >
                {primaryAction === 'published' ? 'Publier' : 'Archiver'}
              </button>
              {course.status !== 'pending' && (
                <button
                  type="button"
                  disabled={pendingId === course.id}
                  onClick={() => onStatusChange(course.id, 'pending')}
                  className="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-600 transition hover:-translate-y-0.5 hover:border-blue-200 hover:text-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
                >
                  Repasser en attente
                </button>
              )}
            </div>
          </article>
        );
      })}
    </div>
  );
}

export function AdminDashboard({ initialData, initialError = null }: AdminDashboardProps) {
  const { user } = useAuth();
  const [analytics, setAnalytics] = useState(initialData?.analytics ?? null);
  const [users, setUsers] = useState<PaginatedResponse<AdminUser> | null>(initialData?.users ?? null);
  const [courses, setCourses] = useState<PaginatedResponse<AdminCourse> | null>(initialData?.courses ?? null);
  const [userSearch, setUserSearch] = useState('');
  const [userRole, setUserRole] = useState<AdminRole | 'all'>('all');
  const [courseSearch, setCourseSearch] = useState('');
  const [courseStatus, setCourseStatus] = useState<CoursePublicationStatus | 'all'>('all');
  const [pendingUserId, setPendingUserId] = useState<number | null>(null);
  const [pendingCourseId, setPendingCourseId] = useState<number | null>(null);
  const [error, setError] = useState<string | null>(initialError ?? null);
  const [isPending, startTransition] = useTransition();

  const userPagination = useMemo(() => getPagination(users ?? { data: [] }), [users]);
  const coursePagination = useMemo(() => getPagination(courses ?? { data: [] }), [courses]);

  const refreshAnalytics = async () => {
    const response = await adminDashboardService.analytics();
    setAnalytics(response.data);
  };

  const loadUsers = (page = 1) => {
    startTransition(() => {
      void (async () => {
        try {
          setError(null);
          const response = await adminDashboardService.users({ search: userSearch, role: userRole, page, perPage: 8 });
          setUsers(response);
        } catch (err) {
          setError(err instanceof HttpError ? err.message : 'Impossible de charger les utilisateurs.');
        }
      })();
    });
  };

  const loadCourses = (page = 1) => {
    startTransition(() => {
      void (async () => {
        try {
          setError(null);
          const response = await adminDashboardService.courses({ search: courseSearch, status: courseStatus, page, perPage: 8 });
          setCourses(response);
        } catch (err) {
          setError(err instanceof HttpError ? err.message : 'Impossible de charger les cours.');
        }
      })();
    });
  };

  const handleRoleChange = (userId: number, role: AdminRole) => {
    startTransition(() => {
      void (async () => {
        try {
          setError(null);
          setPendingUserId(userId);
          const response = await adminDashboardService.updateUserRole(userId, role);
          setUsers((current) => current ? { ...current, data: current.data.map((item) => item.id === userId ? response.data : item) } : current);
          await refreshAnalytics();
        } catch (err) {
          setError(err instanceof HttpError ? err.message : 'Impossible de modifier le rôle utilisateur.');
        } finally {
          setPendingUserId(null);
        }
      })();
    });
  };

  const handleStatusChange = (courseId: number, status: CoursePublicationStatus) => {
    startTransition(() => {
      void (async () => {
        try {
          setError(null);
          setPendingCourseId(courseId);
          const response = await adminDashboardService.updateCourseStatus(courseId, status);
          setCourses((current) => current ? { ...current, data: current.data.map((item) => item.id === courseId ? response.data : item) } : current);
          await refreshAnalytics();
        } catch (err) {
          setError(err instanceof HttpError ? err.message : 'Impossible de modifier le statut du cours.');
        } finally {
          setPendingCourseId(null);
        }
      })();
    });
  };

  return (
    <ProtectedView allowedRoles={['admin']}>
      <main className="min-h-screen bg-[#F8FAFC] px-4 py-6 text-slate-950 sm:px-6 lg:px-8">
        <div className="mx-auto grid w-full max-w-screen-2xl gap-6">
          <section className="overflow-hidden rounded-[2rem] border border-slate-200 bg-white p-7 shadow-[0_24px_70px_rgba(15,23,42,0.07)] sm:p-9">
            <p className="text-xs font-black uppercase tracking-[0.22em] text-blue-600">Centre de contrôle administrateur</p>
            <div className="mt-4 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
              <div>
                <h1 className="max-w-4xl text-4xl font-black tracking-[-0.055em] text-slate-950 sm:text-5xl">Pilotage global Ed-Tech</h1>
                <p className="mt-4 max-w-3xl text-base leading-7 text-slate-500">Bienvenue {user?.name ?? 'Administrateur'}. Les métriques, utilisateurs et validations ci-dessous proviennent exclusivement de l’API Laravel sécurisée.</p>
              </div>
              <div className="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm font-bold text-blue-700">
                {isPending ? 'Synchronisation…' : 'Données temps réel'}
              </div>
            </div>
          </section>

          {error && <div className="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700">{error}</div>}

          <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <MetricCard label="Total apprenants" value={analytics?.users_by_role.student ?? 0} helper="Comptes étudiants inscrits sur la plateforme." accent="bg-emerald-400" />
            <MetricCard label="Total formateurs" value={analytics?.users_by_role.instructor ?? 0} helper="Experts disposant d’un espace de création." accent="bg-blue-500" />
            <MetricCard label="Cours actifs" value={analytics?.total_published_courses ?? 0} helper="Cours validés et accessibles aux apprenants." accent="bg-sky-400" />
            <MetricCard label="Certifications délivrées" value={analytics?.total_certificates_issued ?? 0} helper={`${(analytics?.total_live_sessions_held ?? 0).toLocaleString('fr-FR')} sessions live tenues.`} accent="bg-violet-400" />
          </section>

          <section className="rounded-[2rem] border border-slate-200 bg-white/80 p-5 shadow-sm backdrop-blur sm:p-6">
            <div className="mb-5 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
              <div>
                <p className="text-xs font-black uppercase tracking-[0.18em] text-blue-600">Gestion des utilisateurs</p>
                <h2 className="mt-2 text-2xl font-black tracking-[-0.035em]">Comptes & rôles</h2>
              </div>
              <div className="flex flex-col gap-2 sm:flex-row">
                <input value={userSearch} onChange={(event) => setUserSearch(event.target.value)} placeholder="Rechercher nom ou email" className="rounded-2xl border-slate-200 bg-white px-4 py-2 text-sm" />
                <select value={userRole} onChange={(event) => setUserRole(event.target.value as AdminRole | 'all')} className="rounded-2xl border-slate-200 bg-white px-4 py-2 text-sm font-bold">
                  <option value="all">Tous les rôles</option>
                  {Object.entries(ROLE_LABELS).map(([role, label]) => <option key={role} value={role}>{label}</option>)}
                </select>
                <button type="button" onClick={() => loadUsers()} className="rounded-2xl bg-slate-950 px-4 py-2 text-sm font-bold text-white">Filtrer</button>
              </div>
            </div>
            <UsersTable users={users?.data ?? []} pendingId={pendingUserId} onRoleChange={handleRoleChange} />
            <div className="mt-4 flex items-center justify-between text-sm text-slate-500">
              <span>{userPagination.total.toLocaleString('fr-FR')} utilisateur(s)</span>
              <div className="flex gap-2">
                <button type="button" disabled={userPagination.currentPage <= 1} onClick={() => loadUsers(userPagination.currentPage - 1)} className="rounded-xl border border-slate-200 px-3 py-2 font-bold disabled:opacity-40">Précédent</button>
                <button type="button" disabled={userPagination.currentPage >= userPagination.lastPage} onClick={() => loadUsers(userPagination.currentPage + 1)} className="rounded-xl border border-slate-200 px-3 py-2 font-bold disabled:opacity-40">Suivant</button>
              </div>
            </div>
          </section>

          <section className="rounded-[2rem] border border-slate-200 bg-white/80 p-5 shadow-sm backdrop-blur sm:p-6">
            <div className="mb-5 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
              <div>
                <p className="text-xs font-black uppercase tracking-[0.18em] text-blue-600">Modération des cours</p>
                <h2 className="mt-2 text-2xl font-black tracking-[-0.035em]">Validation & publication</h2>
              </div>
              <div className="flex flex-col gap-2 sm:flex-row">
                <input value={courseSearch} onChange={(event) => setCourseSearch(event.target.value)} placeholder="Rechercher un cours" className="rounded-2xl border-slate-200 bg-white px-4 py-2 text-sm" />
                <select value={courseStatus} onChange={(event) => setCourseStatus(event.target.value as CoursePublicationStatus | 'all')} className="rounded-2xl border-slate-200 bg-white px-4 py-2 text-sm font-bold">
                  <option value="all">Tous les statuts</option>
                  {Object.entries(STATUS_LABELS).map(([status, label]) => <option key={status} value={status}>{label}</option>)}
                </select>
                <button type="button" onClick={() => loadCourses()} className="rounded-2xl bg-slate-950 px-4 py-2 text-sm font-bold text-white">Filtrer</button>
              </div>
            </div>
            <CoursesModeration courses={courses?.data ?? []} pendingId={pendingCourseId} onStatusChange={handleStatusChange} />
            <div className="mt-4 flex items-center justify-between text-sm text-slate-500">
              <span>{coursePagination.total.toLocaleString('fr-FR')} cours</span>
              <div className="flex gap-2">
                <button type="button" disabled={coursePagination.currentPage <= 1} onClick={() => loadCourses(coursePagination.currentPage - 1)} className="rounded-xl border border-slate-200 px-3 py-2 font-bold disabled:opacity-40">Précédent</button>
                <button type="button" disabled={coursePagination.currentPage >= coursePagination.lastPage} onClick={() => loadCourses(coursePagination.currentPage + 1)} className="rounded-xl border border-slate-200 px-3 py-2 font-bold disabled:opacity-40">Suivant</button>
              </div>
            </div>
          </section>
        </div>
      </main>
    </ProtectedView>
  );
}
