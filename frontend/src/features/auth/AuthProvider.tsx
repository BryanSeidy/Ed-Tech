'use client';

import { createContext, useCallback, useMemo, useState } from 'react';
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

function getCachedUser(): AuthUser | null {
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

export function AuthProvider({ children }: Readonly<{ children: React.ReactNode }>) {
  const [user, setUser] = useState<AuthUser | null>(() => getCachedUser());

  const refresh = useCallback(async () => {
    const response = await authApi.me();
    setUser(response.user);
    window.localStorage.setItem(AUTH_KEY, JSON.stringify(response.user));
  }, []);

  const login = useCallback(async (payload: LoginPayload) => {
    const response = await authApi.login(payload);
    setUser(response.user);
    window.localStorage.setItem(AUTH_KEY, JSON.stringify(response.user));
  }, []);

  const register = useCallback(async (payload: RegisterPayload) => {
    const response = await authApi.register(payload);
    setUser(response.user);
    window.localStorage.setItem(AUTH_KEY, JSON.stringify(response.user));
  }, []);

  const logout = useCallback(async () => {
    await authApi.logout();
    setUser(null);
    window.localStorage.removeItem(AUTH_KEY);
  }, []);

  const value = useMemo(
    () => ({ user, isLoading: false, login, register, logout, refresh }),
    [user, login, register, logout, refresh],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}
