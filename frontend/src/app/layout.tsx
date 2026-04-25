import type { Metadata } from 'next';
import '@/src/app/globals.css';
import { AuthProvider } from '@/src/features/auth/AuthProvider';

export const metadata: Metadata = {
  title: 'ED-TECH | Plateforme e-learning juridique',
  description: 'Plateforme e-learning interactive pour étudiants en droit.',
};

export default function RootLayout({ children }: Readonly<{ children: React.ReactNode }>) {
  return (
    <html lang="fr">
      <body>
        <AuthProvider>{children}</AuthProvider>
      </body>
    </html>
  );
}
