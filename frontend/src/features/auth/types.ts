export interface AuthUser {
  id: number;
  name: string;
  email: string;
  avatar?: string | null;
}

export interface AuthResponse {
  data?: AuthUser;
  user?: AuthUser;
}

export interface LoginPayload {
  email: string;
  password: string;
  remember?: boolean;
}

export interface RegisterPayload {
  name: string;
  email: string;
  password: string;
}
