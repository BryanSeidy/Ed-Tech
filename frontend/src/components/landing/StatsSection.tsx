import { Card } from '@/src/components/ui/Card';
import { IconBook, IconPlay, IconUsers } from '@/src/components/landing/icons';

const stats = [
  { value: '5,000+', label: 'Students', icon: <IconUsers />, tone: 'bg-[var(--primary)]' },
  { value: '30+', label: 'Instructors', icon: <IconBook />, tone: 'bg-[var(--accent)]' },
  { value: '200+', label: 'Learning Videos', icon: <IconPlay />, tone: 'bg-[var(--primary)]' },
  { value: '100+', label: 'Study Materials', icon: <IconBook />, tone: 'bg-[var(--accent)]' },
];

export function StatsSection() {
  return (
    <section className="bg-[var(--secondary)] py-16 md:py-24" aria-label="Statistiques plateforme">
      <div className="max-w-7xl mx-auto px-4 md:px-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {stats.map((item, index) => (
          <Card className="!p-4 md:!p-6" key={item.label}>
            <div className={`flex items-center gap-4 animate-in-up delay-${(index % 3) + 1}`}>
              <div className={`inline-flex h-12 w-12 items-center justify-center rounded-full text-white ${item.tone}`}>
                {item.icon}
              </div>
              <div>
                <p className="text-3xl font-bold text-[var(--foreground)]">{item.value}</p>
                <p className="text-sm text-[var(--muted)]">{item.label}</p>
              </div>
            </div>
          </Card>
        ))}
      </div>
    </section>
  );
}
