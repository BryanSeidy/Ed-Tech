export class HttpError extends Error {
  status: number;
  fields?: Record<string, string[]>;
  code?: string;

  constructor(message: string, status: number, fields?: Record<string, string[]>, code?: string) {
    super(message);
    this.name = 'HttpError';
    this.status = status;
    this.fields = fields;
    this.code = code;
  }
}

const API_BASE_URL = process.env.NEXT_PUBLIC_API_URL ?? 'http://localhost:8000/api';
const AUTH_LOGIN_PATH = '/auth/login';
const UNSAFE_METHODS = new Set(['POST', 'PUT', 'PATCH', 'DELETE']);

export const AUTH_UNAUTHORIZED_EVENT = 'edtech:auth:unauthorized';

type RequestOptions = Omit<RequestInit, 'body'> & {
  body?: unknown;
  skipUnauthorizedRedirect?: boolean;
};

type ApiErrorShape = {
  code?: string;
  message?: string;
  errors?: Record<string, string[]>;
};

let csrfCookiePromise: Promise<void> | null = null;

function getApiOrigin(): string {
  return new URL(API_BASE_URL).origin;
}

function isBrowser(): boolean {
  return typeof window !== 'undefined';
}

function getCookie(name: string): string | null {
  if (!isBrowser()) {
    return null;
  }

  const encodedName = `${encodeURIComponent(name)}=`;
  const cookie = document.cookie
    .split('; ')
    .find((entry) => entry.startsWith(encodedName));

  if (!cookie) {
    return null;
  }

  return decodeURIComponent(cookie.slice(encodedName.length));
}

function shouldUseCsrf(method: string): boolean {
  return UNSAFE_METHODS.has(method.toUpperCase());
}

async function ensureCsrfCookie(): Promise<void> {
  if (!isBrowser()) {
    return;
  }

  if (!csrfCookiePromise) {
    csrfCookiePromise = fetch(`${getApiOrigin()}/sanctum/csrf-cookie`, {
      credentials: 'include',
      headers: {
        Accept: 'application/json',
      },
    })
      .then((response) => {
        if (!response.ok) {
          throw new HttpError('Impossible d’initialiser la protection CSRF.', response.status);
        }
      })
      .finally(() => {
        csrfCookiePromise = null;
      });
  }

  return csrfCookiePromise;
}

function redirectToLogin(): void {
  if (!isBrowser()) {
    return;
  }

  window.dispatchEvent(new CustomEvent(AUTH_UNAUTHORIZED_EVENT));

  const currentPath = `${window.location.pathname}${window.location.search}`;
  const isAuthPage = window.location.pathname.startsWith('/auth/');

  if (isAuthPage) {
    return;
  }

  const next = encodeURIComponent(currentPath);
  window.location.assign(`${AUTH_LOGIN_PATH}?next=${next}`);
}

export async function http<T>(path: string, options: RequestOptions = {}): Promise<T> {
  const method = (options.method ?? 'GET').toUpperCase();

  if (shouldUseCsrf(method)) {
    await ensureCsrfCookie();
  }

  const headers = new Headers(options.headers);
  headers.set('Accept', 'application/json');

  const csrfToken = getCookie('XSRF-TOKEN');
  if (csrfToken && shouldUseCsrf(method) && !headers.has('X-XSRF-TOKEN')) {
    headers.set('X-XSRF-TOKEN', csrfToken);
  }

  let body: BodyInit | undefined;
  if (options.body !== undefined) {
    headers.set('Content-Type', 'application/json');
    body = JSON.stringify(options.body);
  }

  const { skipUnauthorizedRedirect = false, ...fetchOptions } = options;
  const response = await fetch(`${API_BASE_URL}${path}`, {
    ...fetchOptions,
    method,
    body,
    headers,
    credentials: 'include',
    cache: 'no-store',
  });

  const data = (await response.json().catch(() => ({}))) as ApiErrorShape;

  if (!response.ok) {
    if (response.status === 401 && !skipUnauthorizedRedirect) {
      redirectToLogin();
    }

    throw new HttpError(
      data.message ?? 'Une erreur réseau est survenue.',
      response.status,
      data.errors,
      data.code,
    );
  }

  return data as T;
}
