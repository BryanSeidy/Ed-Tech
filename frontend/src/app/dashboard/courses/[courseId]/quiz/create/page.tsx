import CreateQuizClient from './CreateQuizClient';

export function generateStaticParams() {
  return [{ courseId: '1' }, { courseId: '2' }, { courseId: '3' }, { courseId: '4' }];
}

export default function CreateQuizPage() {
  return <CreateQuizClient />;
}
