import Link from 'next/link';

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
    <main className="landing">
      <header className="landing-nav">
        <div className="brand">Ed-Tech</div>
        <div className="landing-actions">
          <Link className="button ghost" href="/auth/login">
            Se connecter
          </Link>
          <Link className="button primary" href="/auth/register">
            Commencer gratuitement
          </Link>
        </div>
      </header>

      <section className="hero">
        <div>
          <p className="eyebrow">Plateforme d’apprentissage adaptatif</p>
          <h1>Apprenez plus vite. Réussissez avec précision.</h1>
          <p className="hero-copy">
            Une plateforme intelligente qui adapte chaque cours à votre niveau, votre rythme et vos objectifs.
          </p>
          <div className="hero-cta">
            <Link className="button primary" href="/auth/register">
              Commencer gratuitement
            </Link>
            <Link className="button ghost" href="#flow">
              Voir comment ça marche
            </Link>
          </div>
        </div>
        <div className="hero-visual">
          <p className="helper">Dashboard intelligent</p>
          <div className="mock-row">
            <span>Progression hebdo</span>
            <strong>+18%</strong>
          </div>
          <div className="mock-row">
            <span>Parcours adaptatif</span>
            <strong>Actif</strong>
          </div>
          <div className="mock-row">
            <span>Prochaine session</span>
            <strong>18:30</strong>
          </div>
        </div>
      </section>

      <section className="section split">
        <div>
          <h2>Arrêtez de perdre du temps sur des contenus inutiles</h2>
          <ul className="bullet-list">
            <li>Chaque étudiant avance à l’aveugle.</li>
            <li>Les cours ne s’adaptent pas à votre niveau réel.</li>
            <li>Les révisions sont inefficaces et désorganisées.</li>
          </ul>
        </div>
        <p className="statement">Notre IA restructure votre apprentissage en temps réel.</p>
      </section>

      <section className="section">
        <h2>Une expérience conçue pour progresser vite</h2>
        <div className="cards-grid">
          {features.map((feature) => (
            <article className="item-card" key={feature.title}>
              <h3>{feature.title}</h3>
              <p>{feature.description}</p>
            </article>
          ))}
        </div>
      </section>

      <section className="section" id="flow">
        <h2>Comment ça marche</h2>
        <div className="flow-grid">
          {flowSteps.map((step, index) => (
            <div className="flow-step" key={step}>
              <span>{index + 1}</span>
              <p>{step}</p>
            </div>
          ))}
        </div>
        <Link className="button primary" href="/auth/register">
          Tester le système maintenant
        </Link>
      </section>

      <section className="section split">
        <div>
          <h2>Preuve & crédibilité</h2>
          <p className="stat">+92% des utilisateurs améliorent leurs résultats</p>
          <p className="helper">Apprentissage optimisé basé sur des méthodes scientifiques.</p>
        </div>
        <div className="quotes">
          <blockquote>“J’ai réduit mon temps de révision de moitié.”</blockquote>
          <blockquote>“Chaque session est utile, aucun effort perdu.”</blockquote>
        </div>
      </section>

      <section className="section">
        <h2>Immersion produit</h2>
        <p>Une interface conçue pour rester concentré, progresser et réussir.</p>
        <div className="cards-grid">
          <article className="item-card"><h3>Dashboard étudiant</h3><p>Suivez vos objectifs en temps réel.</p></article>
          <article className="item-card"><h3>Générateur d’épreuves</h3><p>Exercices ciblés par compétence.</p></article>
          <article className="item-card"><h3>Timeline de révision</h3><p>Sessions optimisées automatiquement.</p></article>
        </div>
      </section>

      <section className="section pricing">
        <h2>Pricing simple et clair</h2>
        <div className="cards-grid">
          <article className="item-card">
            <h3>Gratuit</h3>
            <p>Accès de base + tests limités.</p>
          </article>
          <article className="item-card accent-card">
            <h3>Premium</h3>
            <p>Expérience complète + IA avancée.</p>
          </article>
        </div>
        <Link className="button primary" href="/auth/register">
          Commencer gratuitement
        </Link>
      </section>

      <section className="section final-cta">
        <h2>Votre réussite commence maintenant</h2>
        <Link className="button primary" href="/auth/register">
          Créer mon compte
        </Link>
      </section>
    </main>
  );
}
