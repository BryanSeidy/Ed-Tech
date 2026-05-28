import QuizClient from './QuizClient';

export function generateStaticParams() {
  return [
    { courseId: '1', quizId: '1' },
    { courseId: '1', quizId: '2' },
    { courseId: '2', quizId: '3' },
    { courseId: '3', quizId: '4' },
  ];
}

export default function QuizPage() {
  return <QuizClient />;
}
