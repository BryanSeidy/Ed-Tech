export type CourseState = 'active' | 'completed' | 'locked';
export type LiveClassState = 'locked' | 'upcoming' | 'startingSoon' | 'live';
export type QuizState = 'passed' | 'retry' | 'locked';

export type LearningStat = {
  label: string;
  value: string;
};

export type StudentCourse = {
  id: string;
  title: string;
  category: string;
  icon: string;
  progress: number;
  completedLessons: number;
  totalLessons: number;
  remainingQuizzes: number;
  state: CourseState;
};

export type StudentQuiz = {
  id: string;
  title: string;
  course: string;
  score: number | null;
  state: QuizState;
};

export type StudentCertificate = {
  id: string;
  courseTitle: string;
  issueDate: string;
  credentialNumber: string;
};

export type LiveClass = {
  title: string;
  course: string;
  instructor: string;
  instructorRole: string;
  dateLabel: string;
  timeLabel: string;
  countdown: {
    days: string;
    hours: string;
    minutes: string;
  };
  state: LiveClassState;
};

export const learningStats: LearningStat[] = [
  { label: 'Cours en progression', value: '4' },
  { label: 'Leçons terminées', value: '48' },
  { label: 'Heures cette semaine', value: '7h' },
];

export const studentCourses: StudentCourse[] = [
  {
    id: 'react-architecture',
    title: 'Architecture React moderne',
    category: 'Frontend',
    icon: 'RA',
    progress: 78,
    completedLessons: 18,
    totalLessons: 23,
    remainingQuizzes: 2,
    state: 'active',
  },
  {
    id: 'laravel-api',
    title: 'Laravel API professionnelle',
    category: 'Backend',
    icon: 'LA',
    progress: 100,
    completedLessons: 16,
    totalLessons: 16,
    remainingQuizzes: 0,
    state: 'completed',
  },
  {
    id: 'data-product',
    title: 'Data product management',
    category: 'Produit',
    icon: 'DP',
    progress: 42,
    completedLessons: 8,
    totalLessons: 19,
    remainingQuizzes: 4,
    state: 'active',
  },
  {
    id: 'ai-foundations',
    title: 'Fondations IA appliquée',
    category: 'Intelligence artificielle',
    icon: 'AI',
    progress: 0,
    completedLessons: 0,
    totalLessons: 14,
    remainingQuizzes: 6,
    state: 'locked',
  },
];

export const nextLiveClass: LiveClass = {
  title: 'Prochaine Classe en Direct',
  course: 'Architecture React moderne',
  instructor: 'Dr. Amine Laurent',
  instructorRole: 'Lead instructor · React 19',
  dateLabel: 'Jeudi 4 juin',
  timeLabel: '18:30 UTC',
  countdown: {
    days: '02',
    hours: '05',
    minutes: '18',
  },
  state: 'upcoming',
};

export const studentQuizzes: StudentQuiz[] = [
  {
    id: 'quiz-composition',
    title: 'Composition avancée',
    course: 'Architecture React moderne',
    score: 86,
    state: 'passed',
  },
  {
    id: 'quiz-testing',
    title: 'Tests et qualité',
    course: 'Laravel API professionnelle',
    score: 58,
    state: 'retry',
  },
  {
    id: 'quiz-data-kpi',
    title: 'Pilotage KPI produit',
    course: 'Data product management',
    score: null,
    state: 'locked',
  },
];

export const studentCertificates: StudentCertificate[] = [
  {
    id: 'cert-laravel',
    courseTitle: 'Laravel API professionnelle',
    issueDate: '18 mai 2026',
    credentialNumber: 'EDT-LAR-2026-1842',
  },
  {
    id: 'cert-accessibility',
    courseTitle: 'Accessibilité numérique',
    issueDate: '24 avril 2026',
    credentialNumber: 'EDT-A11Y-2026-0876',
  },
];
