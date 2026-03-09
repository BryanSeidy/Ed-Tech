export interface AuthUser {
  id: number;
  name: string;
  email: string;
  avatar?: string | null;
}

export interface AuthResponse {
  user: AuthUser;
}

export interface LoginPayload {
  email: string;
  password: string;
}

export interface RegisterPayload {
  name: string;
  email: string;
  password: string;
}
