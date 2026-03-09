import type { Metadata } from 'next';
import '@/src/app/globals.css';
import { AuthProvider } from '@/src/features/auth/AuthProvider';

export const metadata: Metadata = {
  title: 'Ed-Tech Auth',
  description: 'Authentification sécurisée de la plateforme Ed-Tech',
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
