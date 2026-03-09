import { http } from '@/src/lib/http';
import type { AuthResponse, LoginPayload, RegisterPayload } from '@/src/features/auth/types';

export const authApi = {
  register: (payload: RegisterPayload) =>
    http<AuthResponse>('/auth/register', { method: 'POST', body: payload }),
  login: (payload: LoginPayload) =>
    http<AuthResponse>('/auth/login', { method: 'POST', body: payload }),
  me: () => http<AuthResponse>('/auth/me'),
  logout: () => http<{ message: string }>('/auth/logout', { method: 'POST' }),
};
