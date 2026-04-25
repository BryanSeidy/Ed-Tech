import { CoursesSection } from '@/src/components/landing/CoursesSection';
import { Hero } from '@/src/components/landing/Hero';
import { Navbar } from '@/src/components/landing/Navbar';
import { StatsSection } from '@/src/components/landing/StatsSection';

const features = [
  {
    title: 'Parcours intelligent personnalisé',
    description: 'Un programme qui évolue selon vos performances.',
  },
  {
    title: 'Génération d’exercices ciblés',
    description: 'Travaillez uniquement ce que vous ne maîtrisez pas.',
  },
  {
    title: 'Simulation d’examen réaliste',
    description: 'Préparez-vous dans les conditions réelles.',
  },
  {
    title: 'Planification automatique',
    description: 'Optimisez vos sessions sans réfléchir.',
  },
];

const flowSteps = [
  'Importer vos cours',
  'Analyser votre niveau',
  'Générer votre plan',
  'S’entraîner efficacement',
  'Réussir vos examens',
];

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
