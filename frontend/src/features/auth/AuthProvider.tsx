'use client';

import { usePathname, useRouter } from 'next/navigation';
import { createContext, useCallback, useEffect, useMemo, useState } from 'react';
import { authApi } from '@/src/features/auth/authApi';
import { getDashboardRoleFromPathname, getRoleDashboardRoute } from '@/src/features/auth/roleRoutes';
import { AUTH_UNAUTHORIZED_EVENT } from '@/src/lib/http';
import type { AuthUser, LoginPayload, RegisterPayload } from '@/src/features/auth/types';

type AuthContextValue = {
  user: AuthUser | null;
  isLoading: boolean;
  login: (payload: LoginPayload) => Promise<AuthUser>;
  register: (payload: RegisterPayload) => Promise<AuthUser>;
  logout: () => Promise<void>;
  refresh: () => Promise<void>;
};

export const AuthContext = createContext<AuthContextValue | null>(null);

const AUTH_KEY = 'edtech_auth_user';
const VALID_AUTH_ROLES = new Set<AuthUser['role']>(['student', 'instructor', 'admin']);

function isAuthUser(value: unknown): value is AuthUser {
  if (!value || typeof value !== 'object') {
    return false;
  }

  const candidate = value as Partial<AuthUser>;

  return (
    typeof candidate.id === 'number' &&
    typeof candidate.name === 'string' &&
    typeof candidate.email === 'string' &&
    Boolean(candidate.role && VALID_AUTH_ROLES.has(candidate.role))
  );
}

function clearCachedUser(): void {
  if (typeof window !== 'undefined') {
    window.localStorage.removeItem(AUTH_KEY);
  }
}

function cacheUser(nextUser: AuthUser): void {
  window.localStorage.setItem(AUTH_KEY, JSON.stringify(nextUser));
}

function readCachedUser(): AuthUser | null {
  if (typeof window === 'undefined') {
    return null;
  }

  const raw = window.localStorage.getItem(AUTH_KEY);

  if (!raw) {
    return null;
  }

  try {
    const cachedUser = JSON.parse(raw) as unknown;

    if (isAuthUser(cachedUser)) {
      return cachedUser;
    }

    clearCachedUser();

    return null;
  } catch {
    clearCachedUser();

    return null;
  }
}

function extractUser(payload: { user?: AuthUser; data?: AuthUser }): AuthUser {
  const user = payload.user ?? payload.data;

  if (!isAuthUser(user)) {
    throw new Error('Réponse utilisateur invalide.');
  }

  return user;
}

export function AuthProvider({ children }: Readonly<{ children: React.ReactNode }>) {
  const [user, setUser] = useState<AuthUser | null>(() => readCachedUser());
  const [isLoading, setIsLoading] = useState<boolean>(true);
  const pathname = usePathname();
  const router = useRouter();

  useEffect(() => {
    let mounted = true;

    function handleUnauthorized() {
      setUser(null);
      clearCachedUser();
    }

    window.addEventListener(AUTH_UNAUTHORIZED_EVENT, handleUnauthorized);

    void (async () => {
      try {
        const response = await authApi.me();
        const nextUser = extractUser(response);

        if (!mounted) {
          return;
        }

        setUser(nextUser);
        cacheUser(nextUser);
      } catch {
        if (!mounted) {
          return;
        }

        clearCachedUser();
        setUser(null);
      } finally {
        if (mounted) {
          setIsLoading(false);
        }
      }
    })();

    return () => {
      mounted = false;
      window.removeEventListener(AUTH_UNAUTHORIZED_EVENT, handleUnauthorized);
    };
  }, []);

  useEffect(() => {
    if (isLoading || !user) {
      return;
    }

    const expectedDashboardRoute = getRoleDashboardRoute(user.role);
    const requestedDashboardRole = getDashboardRoleFromPathname(pathname);
    const isAuthRoute = pathname === '/auth/login' || pathname === '/auth/register';

    if (pathname === '/dashboard' || isAuthRoute || (requestedDashboardRole && requestedDashboardRole !== user.role)) {
      router.replace(expectedDashboardRoute);
    }
  }, [isLoading, pathname, router, user]);

  const refresh = useCallback(async () => {
    const response = await authApi.me();
    const nextUser = extractUser(response);
    setUser(nextUser);
    cacheUser(nextUser);
  }, []);

  const login = useCallback(async (payload: LoginPayload) => {
    const response = await authApi.login(payload);
    const nextUser = extractUser(response);
    setUser(nextUser);
    cacheUser(nextUser);

    return nextUser;
  }, []);

  const register = useCallback(async (payload: RegisterPayload) => {
    const response = await authApi.register(payload);
    const nextUser = extractUser(response);
    setUser(nextUser);
    cacheUser(nextUser);

    return nextUser;
  }, []);

  const logout = useCallback(async () => {
    await authApi.logout();
    setUser(null);
    clearCachedUser();
  }, []);

  const value = useMemo(
    () => ({ user, isLoading, login, register, logout, refresh }),
    [user, isLoading, login, register, logout, refresh],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}
