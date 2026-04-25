import CourseDetailClient from './CourseDetailClient';

export function generateStaticParams() {
  return [{ courseId: '1' }, { courseId: '2' }, { courseId: '3' }, { courseId: '4' }];
}

export default function CourseDetailPage() {
  return <CourseDetailClient />;
}
