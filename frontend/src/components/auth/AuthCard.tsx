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
  const [remember, setRemember] = useState(true);
  const [showPassword, setShowPassword] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({});
  const [isSubmitting, setIsSubmitting] = useState(false);

  const isRegister = mode === 'register';

  const title = useMemo(
    () => (isRegister ? 'Créer votre compte' : 'Connexion à votre espace'),
    [isRegister],
  );

  async function onSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setError(null);
    setFieldErrors({});

    if (!email || !password || (isRegister && !name)) {
      setError('Merci de compléter tous les champs obligatoires.');
      return;
    }

    try {
      setIsSubmitting(true);
      if (isRegister) {
        await register({ name, email, password });
      } else {
        await login({ email, password, remember });
      }
      router.push('/dashboard');
    } catch (err) {
      if (err instanceof HttpError) {
        setError(err.message);
        setFieldErrors(err.fields ?? {});
      } else {
        setError('Impossible de traiter votre demande. Réessayez.');
      }
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <section className="card" aria-live="polite">
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
              aria-invalid={Boolean(fieldErrors.name)}
            />
            {fieldErrors.name?.[0] && <span className="field-error">{fieldErrors.name[0]}</span>}
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
            aria-invalid={Boolean(fieldErrors.email)}
          />
          {fieldErrors.email?.[0] && <span className="field-error">{fieldErrors.email[0]}</span>}
        </label>

        <label htmlFor="password">
          Mot de passe
          <input
            id="password"
            name="password"
            type={showPassword ? 'text' : 'password'}
            value={password}
            onChange={(event) => setPassword(event.target.value)}
            autoComplete={isRegister ? 'new-password' : 'current-password'}
            minLength={8}
            required
            aria-invalid={Boolean(fieldErrors.password)}
          />
          {fieldErrors.password?.[0] && <span className="field-error">{fieldErrors.password[0]}</span>}
        </label>

        <div className="inline-actions">
          <label className="checkbox" htmlFor="showPassword">
            <input
              id="showPassword"
              type="checkbox"
              checked={showPassword}
              onChange={(event) => setShowPassword(event.target.checked)}
            />
            Afficher le mot de passe
          </label>

          {!isRegister && (
            <label className="checkbox" htmlFor="remember">
              <input
                id="remember"
                type="checkbox"
                checked={remember}
                onChange={(event) => setRemember(event.target.checked)}
              />
              Se souvenir de moi
            </label>
          )}
        </div>

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
