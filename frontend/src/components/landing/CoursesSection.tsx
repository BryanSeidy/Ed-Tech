import { Button } from '@/src/components/ui/Button';
import { CourseCard, type LandingCourse } from '@/src/components/landing/CourseCard';

const courses: LandingCourse[] = [
  {
    id: 1,
    image: '/course-1.svg',
    title: 'Moral Foundation of Law',
    mentor: 'Guy Hawkins',
    lessons: '21 Lessons',
    students: '800+ Students',
    price: '$350',
    rating: '5.0 (144)',
  },
  {
    id: 2,
    image: '/course-2.svg',
    title: 'International Human Rights',
    mentor: 'Esther Howard',
    lessons: '16 Lessons',
    students: '450+ Students',
    price: '$499',
    rating: '5.0 (122)',
  },
  {
    id: 3,
    image: '/course-3.svg',
    title: 'International Law',
    mentor: 'Brooklyn Simmons',
    lessons: '24 Lessons',
    students: '1.2K+ Students',
    price: '$699',
    rating: '5.0 (544)',
  },
  {
    id: 4,
    image: '/course-4.svg',
    title: 'Bachelor of Laws Essentials',
    mentor: 'Guy Hawkins',
    lessons: '30 Lessons',
    students: '900+ Students',
    price: '$549',
    rating: '5.0 (210)',
  },
];

export function CoursesSection() {
  return (
    <section className="py-16 md:py-24" aria-label="Courses">
      <div className="max-w-7xl mx-auto px-4 md:px-6 space-y-8">
        <div className="flex items-center justify-between gap-4">
          <h2 className="text-2xl md:text-3xl font-semibold text-[var(--foreground)]">Cours</h2>
          <Button ariaLabel="Voir tous les cours" href="/dashboard/courses" variant="secondary">
            Voir tous (12)
          </Button>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          {courses.map((course) => (
            <CourseCard course={course} key={course.id} />
          ))}
        </div>
      </div>
    </section>
  );
}
