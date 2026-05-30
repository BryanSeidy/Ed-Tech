'use client';

import { useEffect, useState } from 'react';
import { certificateService } from '@/src/services/api/certificateService';
import type { Certificate } from '@/src/types/Certificate';

function formatDate(value: string): string {
  return new Intl.DateTimeFormat('fr-FR', { dateStyle: 'long' }).format(new Date(value));
}

export function CertificatesPanel() {
  const [certificates, setCertificates] = useState<Certificate[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    let mounted = true;

    certificateService
      .list()
      .then((response) => {
        if (mounted) {
          setCertificates(response.data);
          setError(null);
        }
      })
      .catch((err: Error) => {
        if (mounted) {
          setError(err.message || 'Impossible de charger vos certificats.');
        }
      })
      .finally(() => {
        if (mounted) {
          setLoading(false);
        }
      });

    return () => {
      mounted = false;
    };
  }, []);

  return (
    <section className="certificates-section" aria-labelledby="certificates-title">
      <div className="section-heading">
        <div>
          <p className="eyebrow">Certification</p>
          <h2 id="certificates-title">Mes certificats</h2>
        </div>
        <span className="badge success-badge">Automatique</span>
      </div>

      {loading ? <p className="helper">Chargement des certificats…</p> : null}
      {error ? <p className="error">{error}</p> : null}

      {!loading && !error && certificates.length === 0 ? (
        <div className="empty-state">
          <strong>Aucun certificat obtenu pour le moment.</strong>
          <p>Terminez 100% des leçons et réussissez tous les quiz obligatoires pour déclencher l’émission automatique.</p>
        </div>
      ) : null}

      <div className="certificate-grid">
        {certificates.map((certificate) => (
          <article className="certificate-card" key={certificate.id}>
            <div>
              <p className="certificate-number">{certificate.certificate_number}</p>
              <h3>{certificate.course.title}</h3>
              <p className="helper">Émis le {formatDate(certificate.issued_at)}</p>
              {certificate.course.instructor_name ? (
                <p className="helper">Formateur: {certificate.course.instructor_name}</p>
              ) : null}
            </div>
            <div className="certificate-actions">
              <a className="button primary" href={certificateService.downloadUrl(certificate.id)} target="_blank" rel="noreferrer">
                Télécharger le PDF
              </a>
              <a className="button ghost" href={`/verify/${certificate.certificate_number}`} target="_blank" rel="noreferrer">
                Vérifier
              </a>
            </div>
          </article>
        ))}
      </div>
    </section>
  );
}
