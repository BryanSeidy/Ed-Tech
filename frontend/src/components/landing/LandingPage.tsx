import { Button } from '@/src/components/ui/Button';

const partners = ['Université Nova', 'Campus Sorbonne Tech', 'DataCloud', 'FinTech Labs', 'Institut Horizon'];

const ecosystem = [
  {
    eyebrow: 'Parcours guidés',
    title: 'Cours structurés pour progresser sans dispersion',
    text: 'Modules courts, objectifs lisibles et ressources centralisées pour avancer avec la rigueur d’un programme académique.',
    metric: '12 semaines',
    metricLabel: 'pour passer d’un socle clair à un portfolio certifiable',
  },
  {
    eyebrow: 'Quiz bloquants',
    title: 'Validation par seuil avant de débloquer la suite',
    text: 'Les évaluations vérifient la maîtrise réelle : vous avancez uniquement quand les compétences essentielles sont acquises.',
    metric: '80 %',
    metricLabel: 'seuil recommandé pour sécuriser chaque compétence',
  },
  {
    eyebrow: 'Certification PDF',
    title: 'Preuve de réussite prête à partager',
    text: 'Une certification automatisée et traçable valorise votre progression auprès d’une université, d’un recruteur ou d’un manager.',
    metric: '1 clic',
    metricLabel: 'pour générer une attestation professionnelle',
  },
];

const testimonials = [
  {
    quote:
      'Ed-tech m’a donné un cadre exigeant sans sacrifier ma flexibilité. Les classes live m’ont aidée à poser mes questions et les quiz m’ont obligée à maîtriser chaque étape.',
    name: 'Inès Mahfoud',
    role: 'Étudiante en master, Université Nova',
  },
  {
    quote:
      'En reconversion, je devais prouver vite que mes nouvelles compétences étaient solides. La certification et les sessions collaboratives ont rendu mon profil crédible en entretien.',
    name: 'Karim Bellanger',
    role: 'Ancien chef de projet, reconverti en product analyst',
  },
];

function SectionHeader({ kicker, title, description }: { kicker: string; title: string; description: string }) {
  return (
    <div className="mx-auto max-w-3xl text-center">
      <p className="text-sm font-semibold uppercase tracking-[0.22em] text-(--primary)">{kicker}</p>
      <h2 className="mt-3 text-3xl font-semibold tracking-[-0.03em] text-(--foreground) md:text-5xl">{title}</h2>
      <p className="mt-5 text-base leading-7 text-(--muted) md:text-lg">{description}</p>
    </div>
  );
}

function InterfaceMockup() {
  return (
    <div className="relative mx-auto w-full max-w-xl rounded-4xl border border-(--border) bg-white/85 p-3 shadow-2xl shadow-(--primary)/10 backdrop-blur">
      <div className="rounded-3xl border border-slate-100 bg-[linear-gradient(135deg,#f8fbff_0%,#ffffff_48%,#eef6ff_100%)] p-4">
        <div className="flex items-center justify-between border-b border-slate-200 pb-4">
          <div>
            <p className="text-xs font-semibold uppercase tracking-[0.18em] text-(--primary)">Classe live</p>
            <p className="mt-1 text-lg font-semibold text-(--foreground)">Data Foundations · Cohorte 24</p>
          </div>
          <span className="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">En direct</span>
        </div>

        <div className="mt-4 grid gap-3 md:grid-cols-[1.2fr_0.8fr]">
          <div className="rounded-2xl bg-(--foreground) p-4 text-white">
            <div className="aspect-video rounded-xl bg-[radial-gradient(circle_at_25%_20%,rgba(125,211,252,.55),transparent_35%),linear-gradient(135deg,#123b73,#0a2540)] p-4">
              <div className="flex h-full flex-col justify-between">
                <div className="flex gap-2">
                  <span className="h-2 w-2 rounded-full bg-red-300" />
                  <span className="h-2 w-2 rounded-full bg-yellow-300" />
                  <span className="h-2 w-2 rounded-full bg-green-300" />
                </div>
                <div>
                  <p className="text-sm font-semibold">Atelier collaboratif Jitsi</p>
                  <p className="text-xs text-white/70">14 apprenants · 1 mentor</p>
                </div>
              </div>
            </div>
            <div className="mt-4 grid grid-cols-3 gap-2">
              {['Quiz seuil', 'Replay', 'Notes'].map((item) => (
                <div className="rounded-xl bg-white/10 p-3 text-center text-xs font-medium" key={item}>
                  {item}
                </div>
              ))}
            </div>
          </div>

          <div className="space-y-3">
            <div className="rounded-2xl border border-slate-200 bg-white p-4">
              <p className="text-xs font-semibold uppercase tracking-[0.16em] text-(--muted)">Progression</p>
              <p className="mt-2 text-3xl font-semibold text-(--foreground)">78%</p>
              <div className="mt-3 h-2 rounded-full bg-slate-100">
                <div className="h-2 w-[78%] rounded-full bg-(--primary)" />
              </div>
            </div>
            <div className="rounded-2xl border border-slate-200 bg-white p-4">
              <p className="text-sm font-semibold text-(--foreground)">Certification</p>
              <p className="mt-1 text-sm text-(--muted)">PDF généré après réussite des modules obligatoires.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

function HeroSection() {
  return (
    <section className="relative overflow-hidden px-4 pb-16 pt-12 md:px-6 md:pb-24 md:pt-20" id="hero">
      <div className="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_18%_12%,rgba(0,113,227,.12),transparent_28%),radial-gradient(circle_at_82%_18%,rgba(125,211,252,.22),transparent_28%)]" />
      <div className="mx-auto grid max-w-7xl items-center gap-12 lg:grid-cols-[0.95fr_1.05fr]">
        <div className="max-w-3xl">
          <div className="inline-flex items-center gap-2 rounded-full border border-(--border) bg-white px-4 py-2 text-sm font-medium text-(--muted) shadow-sm">
            <span className="h-2 w-2 rounded-full bg-emerald-400" />
            Nouvelle génération d’apprentissage certifiant
          </div>
          <h1 className="mt-6 text-5xl font-semibold tracking-[-0.055em] text-(--foreground) md:text-7xl">
            Ed-tech, la trajectoire la plus claire vers des compétences certifiées.
          </h1>
          <p className="mt-6 max-w-2xl text-lg leading-8 text-(--muted) md:text-xl">
            Réussissez vos études ou accélérez votre reconversion avec des cours structurés, des classes virtuelles
            collaboratives, des quiz exigeants et une certification automatisée prête à valoriser votre profil.
          </p>
          <div className="mt-8 flex flex-col gap-3 sm:flex-row">
            <Button ariaLabel="Découvrir les cours Ed-tech" href="/dashboard/courses" variant="primary">
              Découvrir les cours
            </Button>
            <Button ariaLabel="Rejoindre une classe virtuelle Ed-tech" href="/auth/login" variant="secondary">
              Rejoindre une classe
            </Button>
          </div>
          <div className="mt-10 grid max-w-xl grid-cols-3 gap-4 border-t border-(--border) pt-6">
            {[
              ['80%', 'seuil de maîtrise'],
              ['Live', 'cohortes Jitsi'],
              ['PDF', 'certificat généré'],
            ].map(([value, label]) => (
              <div key={value}>
                <p className="text-2xl font-semibold text-(--foreground)">{value}</p>
                <p className="mt-1 text-sm text-(--muted)">{label}</p>
              </div>
            ))}
          </div>
        </div>
        <InterfaceMockup />
      </div>
    </section>
  );
}

function CredibilitySection() {
  return (
    <section className="border-y border-(--border) bg-white/75 px-4 py-10 md:px-6" id="credibilite">
      <div className="mx-auto max-w-7xl">
        <p className="text-center text-sm font-medium uppercase tracking-[0.2em] text-(--muted)">
          Une certification pensée pour être lisible par les campus et les recruteurs
        </p>
        <div className="mt-8 grid grid-cols-2 gap-3 md:grid-cols-5">
          {partners.map((partner) => (
            <div
              className="flex h-20 items-center justify-center rounded-2xl border border-(--border) bg-white px-4 text-center text-sm font-semibold text-slate-500 shadow-sm"
              key={partner}
            >
              {partner}
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}

function PositioningSection() {
  return (
    <section className="px-4 py-20 md:px-6" id="positionnement">
      <div className="mx-auto grid max-w-7xl gap-6 lg:grid-cols-[0.9fr_1.1fr]">
        <div className="rounded-4xl border border-(--border) bg-white p-8 shadow-sm md:p-10">
          <p className="text-sm font-semibold uppercase tracking-[0.22em] text-(--primary)">Étape 1 · L’esprit Ed-tech</p>
          <h2 className="mt-4 text-3xl font-semibold tracking-[-0.035em] text-(--foreground) md:text-5xl">
            Une UVP simple : apprendre avec exigence, prouver avec confiance.
          </h2>
        </div>
        <div className="grid gap-4 md:grid-cols-2">
          <div className="rounded-4xl border border-(--border) bg-(--secondary) p-7">
            <h3 className="text-xl font-semibold text-(--foreground)">Pour l’étudiant</h3>
            <p className="mt-3 leading-7 text-(--muted)">
              Ed-tech transforme un objectif académique en progression visible : cours guidés, entraide en live et seuils de
              réussite qui sécurisent la maîtrise avant l’examen ou le stage.
            </p>
          </div>
          <div className="rounded-4xl border border-(--border) bg-white p-7 shadow-sm">
            <h3 className="text-xl font-semibold text-(--foreground)">Pour le professionnel</h3>
            <p className="mt-3 leading-7 text-(--muted)">
              Ed-tech rend la reconversion plus crédible : apprentissage efficace, preuves certifiées et rythme compatible
              avec un agenda d’actif.
            </p>
          </div>
          <div className="rounded-4xl border border-sky-100 bg-[linear-gradient(135deg,#ffffff,#eef7ff)] p-7 md:col-span-2">
            <h3 className="text-xl font-semibold text-(--foreground)">Pourquoi le style Stripe/Coursera inspire confiance</h3>
            <p className="mt-3 leading-7 text-(--muted)">
              Les grands espaces blancs, la grille nette, les bordures fines et les bleus institutionnels réduisent le bruit
              visuel. L’interface paraît stable, transparente et premium : le certificat final est perçu comme une preuve
              sérieuse plutôt qu’un simple badge décoratif.
            </p>
          </div>
        </div>
      </div>
    </section>
  );
}

function LiveSection() {
  return (
    <section className="bg-(--foreground) px-4 py-20 text-white md:px-6" id="live">
      <div className="mx-auto grid max-w-7xl items-center gap-10 lg:grid-cols-2">
        <div>
          <p className="text-sm font-semibold uppercase tracking-[0.22em] text-sky-300">Le pouvoir du live</p>
          <h2 className="mt-4 text-3xl font-semibold tracking-[-0.035em] md:text-5xl">
            Les cours en ligne ne devraient jamais vous laisser seul face à l’écran.
          </h2>
          <p className="mt-6 text-lg leading-8 text-white/70">
            Grâce à Jitsi Meet, Ed-tech relie apprenants, mentors et promotions dans des classes virtuelles fluides : vous
            questionnez, pratiquez, comparez vos raisonnements et repartez avec des décisions claires pour la suite.
          </p>
        </div>
        <div className="rounded-4xl border border-white/10 bg-white/10 p-5 shadow-2xl shadow-black/20 backdrop-blur">
          <div className="grid gap-3 sm:grid-cols-2">
            {['Salle Jitsi intégrée', 'Ateliers en cohorte', 'Présence mentor', 'Rituels anti-isolement'].map((item, index) => (
              <div className="rounded-2xl border border-white/10 bg-white/10 p-5" key={item}>
                <span className="text-sm font-semibold text-sky-300">0{index + 1}</span>
                <p className="mt-4 text-lg font-semibold">{item}</p>
                <p className="mt-2 text-sm leading-6 text-white/65">
                  Collaboration en temps réel pour transformer la théorie en réflexes professionnels.
                </p>
              </div>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
}

function EcosystemSection() {
  return (
    <section className="px-4 py-20 md:px-6" id="ecosysteme">
      <div className="mx-auto max-w-7xl">
        <SectionHeader
          kicker="Étape 2 · Écosystème pédagogique"
          title="Un bento pédagogique conçu pour la progression mesurable."
          description="Chaque pilier du MVP sert un résultat concret : comprendre, valider, certifier. L’expérience reste épurée, mais le parcours est exigeant."
        />
        <div className="mt-12 grid gap-5 lg:grid-cols-3">
          {ecosystem.map((item, index) => (
            <article
              className={`rounded-4xl border border-(--border) bg-white p-7 shadow-sm ${
                index === 0 ? 'lg:row-span-2' : ''
              }`}
              key={item.title}
            >
              <p className="text-sm font-semibold uppercase tracking-[0.18em] text-(--primary)">{item.eyebrow}</p>
              <h3 className="mt-4 text-2xl font-semibold tracking-tight text-(--foreground)">{item.title}</h3>
              <p className="mt-4 leading-7 text-(--muted)">{item.text}</p>
              <div className="mt-8 rounded-2xl bg-(--secondary) p-5">
                <p className="text-4xl font-semibold text-(--foreground)">{item.metric}</p>
                <p className="mt-2 text-sm leading-6 text-(--muted)">{item.metricLabel}</p>
              </div>
            </article>
          ))}
        </div>
      </div>
    </section>
  );
}

function TestimonialsSection() {
  return (
    <section className="bg-white px-4 py-20 md:px-6" id="temoignages">
      <div className="mx-auto max-w-7xl">
        <SectionHeader
          kicker="Témoignages croisés"
          title="Deux ambitions, une même preuve de progression."
          description="Ed-tech parle autant à la réussite académique qu’à l’employabilité rapide : la plateforme aligne motivation, méthode et crédibilité."
        />
        <div className="mt-12 grid gap-5 md:grid-cols-2">
          {testimonials.map((testimonial) => (
            <figure className="rounded-4xl border border-(--border) bg-(--secondary) p-8" key={testimonial.name}>
              <blockquote className="text-xl leading-9 tracking-[-0.015em] text-(--foreground)">“{testimonial.quote}”</blockquote>
              <figcaption className="mt-8 border-t border-(--border) pt-5">
                <p className="font-semibold text-(--foreground)">{testimonial.name}</p>
                <p className="mt-1 text-sm text-(--muted)">{testimonial.role}</p>
              </figcaption>
            </figure>
          ))}
        </div>
      </div>
    </section>
  );
}

function TechnicalSection() {
  return (
    <section className="px-4 py-20 md:px-6" id="architecture">
      <div className="mx-auto max-w-7xl rounded-4xl border border-(--border) bg-white p-8 shadow-sm md:p-10">
        <p className="text-sm font-semibold uppercase tracking-[0.22em] text-(--primary)">Étape 3 · Architecture UI</p>
        <div className="mt-6 grid gap-8 lg:grid-cols-[0.9fr_1.1fr]">
          <div>
            <h2 className="text-3xl font-semibold tracking-[-0.035em] text-(--foreground) md:text-5xl">
              Palette Tailwind 4 claire, premium et institutionnelle.
            </h2>
            <p className="mt-5 leading-7 text-(--muted)">
              Fond blanc pur, cobalt profond pour l’autorité, bleu accent pour l’innovation et surfaces ardoise très
              légères pour les respirations Coursera-like.
            </p>
          </div>
          <div className="grid gap-3 sm:grid-cols-2">
            {[
              ['Background', '#FFFFFF'],
              ['Ink / Texte', '#0A2540'],
              ['Cobalt CTA', '#0071E3'],
              ['Accent Sky', '#7DD3FC'],
            ].map(([name, value]) => (
              <div className="rounded-2xl border border-(--border) p-4" key={name}>
                <div className="h-16 rounded-xl border border-slate-200" style={{ backgroundColor: value }} />
                <p className="mt-3 font-semibold text-(--foreground)">{name}</p>
                <p className="text-sm text-(--muted)">{value}</p>
              </div>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
}

function FinalCtaSection() {
  return (
    <section className="px-4 pb-24 pt-6 md:px-6" id="inscription">
      <div className="mx-auto max-w-6xl overflow-hidden rounded-4xl border border-sky-100 bg-[radial-gradient(circle_at_10%_10%,rgba(125,211,252,.35),transparent_24%),linear-gradient(135deg,#ffffff_0%,#eef7ff_52%,#f8fbff_100%)] p-8 text-center shadow-2xl shadow-(--primary)/10 md:p-14">
        <p className="text-sm font-semibold uppercase tracking-[0.22em] text-(--primary)">Inscription sécurisée via Auth Sanctum</p>
        <h2 className="mx-auto mt-4 max-w-3xl text-3xl font-semibold tracking-[-0.04em] text-(--foreground) md:text-6xl">
          Lancez votre prochaine étape avec un parcours qui prouve vos résultats.
        </h2>
        <p className="mx-auto mt-5 max-w-2xl text-lg leading-8 text-(--muted)">
          Créez votre compte, rejoignez une classe et commencez à construire une certification crédible dès cette semaine.
        </p>
        <div className="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
          <Button ariaLabel="Créer un compte Ed-tech" href="/auth/register" variant="primary">
            Créer mon compte
          </Button>
          <Button ariaLabel="Se connecter à Ed-tech" href="/auth/login" variant="secondary">
            Se connecter
          </Button>
        </div>
      </div>
    </section>
  );
}

export function LandingPage() {
  return (
    <main className="min-h-screen bg-(--background) text-(--foreground)">
      <HeroSection />
      <CredibilitySection />
      <PositioningSection />
      <LiveSection />
      <EcosystemSection />
      <TestimonialsSection />
      <TechnicalSection />
      <FinalCtaSection />
    </main>
  );
}
