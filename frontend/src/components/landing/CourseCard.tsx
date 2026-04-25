import Image from 'next/image';
import { Badge } from '@/src/components/ui/Badge';
import { Card } from '@/src/components/ui/Card';
import { IconArrowUpRight, IconBook, IconStar, IconUsers } from '@/src/components/landing/icons';

export type LandingCourse = {
  id: number;
  image: string;
  title: string;
  mentor: string;
  lessons: string;
  students: string;
  price: string;
  rating: string;
};

export function CourseCard({ course }: { course: LandingCourse }) {
  return (
    <Card className="group !p-0 overflow-hidden">
      <div className="relative">
        <Image alt={course.title} className="h-48 w-full object-cover" height={320} src={course.image} width={420} />
        <Badge className="absolute left-3 top-3">
          <span className="text-[var(--accent)]">
            <IconStar />
          </span>
          {course.rating}
        </Badge>
      </div>

      <div className="space-y-4 p-4 md:p-6">
        <h3 className="text-lg font-semibold text-[var(--foreground)]">{course.title}</h3>
        <p className="text-sm text-[var(--muted)]">Mentor: {course.mentor}</p>

        <div className="flex items-center gap-4 text-sm text-[var(--muted)]">
          <span className="inline-flex items-center gap-2"><IconBook /> {course.lessons}</span>
          <span className="inline-flex items-center gap-2"><IconUsers /> {course.students}</span>
        </div>

        <div className="flex items-center justify-between">
          <p className="text-3xl font-bold text-[var(--foreground)]">{course.price}</p>
          <button
            aria-label={`Voir ${course.title}`}
            className="inline-flex h-11 w-11 items-center justify-center rounded-full border border-[var(--border)] text-[var(--foreground)] transition duration-500 ease-out group-hover:scale-110 group-hover:rotate-12 group-hover:bg-[var(--primary)] group-hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--primary)]"
            type="button"
          >
            <IconArrowUpRight />
          </button>
        </div>
      </div>
    </Card>
  );
}
