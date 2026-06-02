import { DashboardCard, SectionHeader } from '@/src/components/dashboard/DashboardPrimitives';
import { CertificateIcon, DownloadIcon } from '@/src/components/dashboard/DashboardIcons';
import type { StudentCertificate } from '@/src/data/mockStudentData';

export function CertificateWall({ certificates }: Readonly<{ certificates: StudentCertificate[] }>) {
  return (
    <DashboardCard className="col-span-1 md:col-span-6 lg:col-span-12">
      <SectionHeader eyebrow="Certificats" title="Mur des certificats reçus" />
      <div className="grid gap-4 lg:grid-cols-2">
        {certificates.map((certificate) => (
          <article key={certificate.id} className="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950 sm:flex-row sm:items-center sm:justify-between">
            <div className="flex items-center gap-4">
              <div className="grid size-20 shrink-0 place-items-center rounded-2xl border border-primary/20 bg-white text-primary dark:border-primary/30 dark:bg-slate-900 dark:text-secondary">
                <CertificateIcon className="size-9" />
              </div>
              <div>
                <p className="text-xs font-bold uppercase tracking-widest text-primary dark:text-secondary">{certificate.credentialNumber}</p>
                <h3 className="mt-1 font-bold text-slate-950 dark:text-white">{certificate.courseTitle}</h3>
                <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">Émis le {certificate.issueDate}</p>
              </div>
            </div>
            <button type="button" className="inline-flex cursor-pointer items-center justify-center gap-2 rounded-xl bg-white px-4 py-3 text-sm font-bold text-slate-700 ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:text-primary dark:bg-slate-900 dark:text-slate-200 dark:ring-slate-700 dark:hover:text-secondary">
              <DownloadIcon className="size-4" />
              Télécharger
            </button>
          </article>
        ))}
      </div>
    </DashboardCard>
  );
}
