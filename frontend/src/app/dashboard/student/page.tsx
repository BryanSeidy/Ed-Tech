'use client';

import { useRouter } from 'next/navigation';
import { ProtectedView } from '@/src/components/auth/ProtectedView';
import { CertificateWall } from '@/src/components/dashboard/CertificateWall';
import { CourseProgressBars } from '@/src/components/dashboard/CourseProgressBars';
import { LiveClassCard } from '@/src/components/dashboard/LiveClassCard';
import { QuizEvaluationCard } from '@/src/components/dashboard/QuizEvaluationCard';
import { StudentDashboardShell } from '@/src/components/dashboard/StudentDashboardShell';
import { WelcomeResumeCard } from '@/src/components/dashboard/WelcomeResumeCard';
import { useAuth } from '@/src/features/auth/useAuth';
import { learningStats, nextLiveClass, studentCertificates, studentCourses, studentQuizzes } from '@/src/data/mockStudentData';

export default function DashboardPage() {
  const { user, logout } = useAuth();
  const router = useRouter();

  async function handleLogout() {
    await logout();
    router.push('/auth/login');
  }

  return (
    <ProtectedView allowedRoles={['student']}>
      <StudentDashboardShell userName={user?.name ?? 'Étudiant'} onLogout={handleLogout}>
        <WelcomeResumeCard userName={user?.name ?? 'Étudiant'} stats={learningStats} />
        <LiveClassCard liveClass={nextLiveClass} />
        <QuizEvaluationCard quizzes={studentQuizzes} />
        <CourseProgressBars courses={studentCourses} />
        <CertificateWall certificates={studentCertificates} />
      </StudentDashboardShell>
    </ProtectedView>
  );
}
