'use client';

import { createContext, useCallback, useEffect, useMemo, useState } from 'react';
import { authApi } from '@/src/features/auth/authApi';
import type { AuthUser, LoginPayload, RegisterPayload } from '@/src/features/auth/types';

type AuthContextValue = {
  user: AuthUser | null;
  isLoading: boolean;
  login: (payload: LoginPayload) => Promise<void>;
  register: (payload: RegisterPayload) => Promise<void>;
  logout: () => Promise<void>;
  refresh: () => Promise<void>;
};

export const AuthContext = createContext<AuthContextValue | null>(null);

const AUTH_KEY = 'edtech_auth_user';

function readCachedUser(): AuthUser | null {
  if (typeof window === 'undefined') {
    return null;
  }

  const raw = window.localStorage.getItem(AUTH_KEY);

  if (!raw) {
    return null;
  }

  try {
    return JSON.parse(raw) as AuthUser;
  } catch {
    window.localStorage.removeItem(AUTH_KEY);

    return null;
  }
}

function extractUser(payload: { user?: AuthUser; data?: AuthUser }): AuthUser {
  const user = payload.user ?? payload.data;

  if (!user) {
    throw new Error('Réponse utilisateur invalide.');
  }

  return user;
}

export function AuthProvider({ children }: Readonly<{ children: React.ReactNode }>) {
  const [user, setUser] = useState<AuthUser | null>(() => readCachedUser());
  const [isLoading, setIsLoading] = useState<boolean>(true);

  useEffect(() => {
    let mounted = true;

    void (async () => {
      try {
        const response = await authApi.me();
        const nextUser = extractUser(response);

        if (!mounted) {
          return;
        }

        setUser(nextUser);
        window.localStorage.setItem(AUTH_KEY, JSON.stringify(nextUser));
      } catch {
        if (!mounted) {
          return;
        }

        setUser(readCachedUser());
      } finally {
        if (mounted) {
          setIsLoading(false);
        }
      }
    })();

    return () => {
      mounted = false;
    };
  }, []);

  const refresh = useCallback(async () => {
    const response = await authApi.me();
    const nextUser = extractUser(response);
    setUser(nextUser);
    window.localStorage.setItem(AUTH_KEY, JSON.stringify(nextUser));
  }, []);

  const login = useCallback(async (payload: LoginPayload) => {
    const response = await authApi.login(payload);
    const nextUser = extractUser(response);
    setUser(nextUser);
    window.localStorage.setItem(AUTH_KEY, JSON.stringify(nextUser));
  }, []);

  const register = useCallback(async (payload: RegisterPayload) => {
    const response = await authApi.register(payload);
    const nextUser = extractUser(response);
    setUser(nextUser);
    window.localStorage.setItem(AUTH_KEY, JSON.stringify(nextUser));
  }, []);

  const logout = useCallback(async () => {
    await authApi.logout();
    setUser(null);
    window.localStorage.removeItem(AUTH_KEY);
  }, []);

  const value = useMemo(
    () => ({ user, isLoading, login, register, logout, refresh }),
    [user, isLoading, login, register, logout, refresh],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}
