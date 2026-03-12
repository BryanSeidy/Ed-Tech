import Link from 'next/link';

export default function HomePage() {
  return (
    <main className="auth-shell">
      <section className="card">
        <h1>Bienvenue sur Ed-Tech</h1>
        <p>Connectez-vous pour accéder à votre espace d’apprentissage.</p>
        <div className="actions">
          <Link className="button primary" href="/auth/login">
            Se connecter
          </Link>
          <Link className="button ghost" href="/auth/register">
            Créer un compte
          </Link>
        </div>
      </section>
    </main>
  );
}
