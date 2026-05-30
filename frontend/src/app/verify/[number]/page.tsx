import Link from 'next/link';
import type { Metadata } from 'next';
import type { VerifyCertificateResponse } from '@/src/types/Certificate';

const API_BASE_URL = process.env.NEXT_PUBLIC_API_URL ?? 'http://localhost:8000/api';

type VerifyPageProps = {
  params: Promise<{ number: string }>;
};

async function fetchCertificate(number: string): Promise<VerifyCertificateResponse> {
  const response = await fetch(`${API_BASE_URL}/certificates/verify/${encodeURIComponent(number)}`, {
    headers: { Accept: 'application/json' },
    cache: 'no-store',
  });

  const payload = (await response.json().catch(() => ({ valid: false }))) as VerifyCertificateResponse;

  if (!response.ok) {
    return { valid: false, message: payload.message ?? 'Certificat introuvable.' };
  }

  return payload;
}

function formatDate(value?: string): string {
  if (!value) {
    return 'Date indisponible';
  }

  return new Intl.DateTimeFormat('fr-FR', { dateStyle: 'long' }).format(new Date(value));
}

export async function generateMetadata({ params }: VerifyPageProps): Promise<Metadata> {
  const { number } = await params;

  return {
    title: `Vérification certificat ${number} | ED-TECH`,
    description: 'Vérification publique et institutionnelle d’un certificat ED-TECH.',
  };
}

export default async function VerifyCertificatePage({ params }: VerifyPageProps) {
  const { number } = await params;
  const verification = await fetchCertificate(number);
  const certificate = verification.data;

  return (
    <main className="verify-shell">
      <section className="verify-card">
        <Link className="brand" href="/">
          ED-TECH
        </Link>

        {verification.valid && certificate ? (
          <>
            <div className="verify-status success-status" aria-label="Certificat valide">
              <span>✓</span>
              Certificat vérifié
            </div>
            <h1>{certificate.student_name} a validé cette certification</h1>
            <p className="verify-lead">
              ED-TECH confirme que ce certificat a été émis automatiquement après finalisation complète du parcours et
              réussite des quiz obligatoires.
            </p>

            <dl className="verify-details">
              <div>
                <dt>Programme</dt>
                <dd>{certificate.course.title}</dd>
              </div>
              <div>
                <dt>Bénéficiaire</dt>
                <dd>{certificate.student_name}</dd>
              </div>
              <div>
                <dt>Numéro de certificat</dt>
                <dd>{certificate.certificate_number}</dd>
              </div>
              <div>
                <dt>Date d’émission</dt>
                <dd>{formatDate(certificate.issued_at)}</dd>
              </div>
              {certificate.course.instructor_name ? (
                <div>
                  <dt>Formateur</dt>
                  <dd>{certificate.course.instructor_name}</dd>
                </div>
              ) : null}
            </dl>
          </>
        ) : (
          <>
            <div className="verify-status error-status" aria-label="Certificat non trouvé">
              <span>!</span>
              Vérification impossible
            </div>
            <h1>Certificat introuvable</h1>
            <p className="verify-lead">
              Aucun certificat ED-TECH actif ne correspond au numéro <strong>{number}</strong>. Vérifiez le numéro ou
              contactez le titulaire du certificat.
            </p>
          </>
        )}
      </section>
    </main>
  );
}
