import { CoursesSection } from '@/src/components/landing/CoursesSection';
import { Hero } from '@/src/components/landing/Hero';
import { Navbar } from '@/src/components/landing/Navbar';
import { StatsSection } from '@/src/components/landing/StatsSection';

export default function HomePage() {
  return (
    <main className="bg-[var(--background)] text-[var(--foreground)]">
      <Navbar />
      <Hero />
      <StatsSection />
      <CoursesSection />
    </main>
  );
}
