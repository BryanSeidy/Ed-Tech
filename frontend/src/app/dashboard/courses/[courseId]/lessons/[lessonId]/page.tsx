import LessonClient from './LessonClient';

export function generateStaticParams() {
  return [
    { courseId: '1', lessonId: '1' },
    { courseId: '1', lessonId: '2' },
    { courseId: '2', lessonId: '3' },
    { courseId: '3', lessonId: '4' },
  ];
}

export default function LessonPage() {
  return <LessonClient />;
}
