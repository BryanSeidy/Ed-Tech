'use client';

import { useContext } from 'react';
import { AuthContext } from '@/src/features/auth/AuthProvider';

export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth doit être utilisé dans AuthProvider.');
  }
  return context;
}
