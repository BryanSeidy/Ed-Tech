'use client';

import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { FormEvent, useMemo, useState } from 'react';
import { HttpError } from '@/src/lib/http';
import { useAuth } from '@/src/features/auth/useAuth';

type Mode = 'login' | 'register';

export function AuthCard({ mode }: Readonly<{ mode: Mode }>) {
  const { login, register } = useAuth();
  const router = useRouter();
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  const isRegister = mode === 'register';

  const title = useMemo(
    () => (isRegister ? 'Créer votre compte' : 'Connexion à votre espace'),
    [isRegister],
  );

  async function onSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setError(null);

    if (!email || !password || (isRegister && !name)) {
      setError('Merci de compléter tous les champs obligatoires.');
      return;
    }

    try {
      setIsSubmitting(true);
      if (isRegister) {
        await register({ name, email, password });
      } else {
        await login({ email, password });
      }
      router.push('/dashboard');
    } catch (err) {
      if (err instanceof HttpError) {
        setError(err.message);
      } else {
        setError('Impossible de traiter votre demande. Réessayez.');
      }
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <section className="card">
      <h1>{title}</h1>
      <p>Utilisez votre adresse email institutionnelle pour une meilleure traçabilité.</p>
      <form className="form-grid" onSubmit={onSubmit} noValidate>
        {isRegister && (
          <label htmlFor="name">
            Nom complet
            <input
              id="name"
              name="name"
              value={name}
              onChange={(event) => setName(event.target.value)}
              autoComplete="name"
              required
            />
          </label>
        )}

        <label htmlFor="email">
          Email
          <input
            id="email"
            name="email"
            type="email"
            value={email}
            onChange={(event) => setEmail(event.target.value)}
            autoComplete="email"
            required
          />
        </label>

        <label htmlFor="password">
          Mot de passe
          <input
            id="password"
            name="password"
            type="password"
            value={password}
            onChange={(event) => setPassword(event.target.value)}
            autoComplete={isRegister ? 'new-password' : 'current-password'}
            minLength={8}
            required
          />
        </label>

        {error && <div className="error">{error}</div>}

        <button className="button primary" type="submit" disabled={isSubmitting}>
          {isSubmitting ? 'Traitement...' : isRegister ? 'Créer mon compte' : 'Se connecter'}
        </button>
      </form>
      <p className="helper">
        {isRegister ? 'Déjà inscrit ? ' : 'Pas encore de compte ? '}
        <Link href={isRegister ? '/login' : '/register'}>
          {isRegister ? 'Se connecter' : 'Créer un compte'}
        </Link>
      </p>
    </section>
  );
}
