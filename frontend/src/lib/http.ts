export class HttpError extends Error {
  status: number;
  fields?: Record<string, string[]>;

  constructor(message: string, status: number, fields?: Record<string, string[]>) {
    super(message);
    this.status = status;
    this.fields = fields;
  }
}

const API_BASE_URL = process.env.NEXT_PUBLIC_API_URL ?? 'http://localhost:8000/api';

type RequestOptions = Omit<RequestInit, 'body'> & { body?: unknown };

type ApiErrorShape = {
  message?: string;
  errors?: Record<string, string[]>;
};

export async function http<T>(path: string, options: RequestOptions = {}): Promise<T> {
  const headers = new Headers(options.headers);
  headers.set('Accept', 'application/json');

  let body: BodyInit | undefined;
  if (options.body !== undefined) {
    headers.set('Content-Type', 'application/json');
    body = JSON.stringify(options.body);
  }

  const response = await fetch(`${API_BASE_URL}${path}`, {
    ...options,
    body,
    headers,
    credentials: 'include',
    cache: 'no-store',
  });

  const data = (await response.json().catch(() => ({}))) as ApiErrorShape;

  if (!response.ok) {
    throw new HttpError(
      data.message ?? 'Une erreur réseau est survenue.',
      response.status,
      data.errors,
    );
  }

  return data as T;
}
