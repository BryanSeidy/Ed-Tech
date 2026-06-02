import { InstructorDashboard } from '@/src/components/dashboard/instructor/InstructorDashboard';
import { getInstructorDashboardData } from '@/src/services/api/instructorDashboardService';

export default async function InstructorDashboardPage() {
  const { user, stats, courses, liveSessions } = await getInstructorDashboardData();

  return (
    <InstructorDashboard
      instructorName={user.name}
      stats={stats}
      courses={courses}
      liveSessions={liveSessions}
    />
  );
}
