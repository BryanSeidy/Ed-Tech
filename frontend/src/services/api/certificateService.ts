import { http } from '@/src/lib/http';
import type { CertificatesResponse, VerifyCertificateResponse } from '@/src/types/Certificate';

const API_BASE_URL = process.env.NEXT_PUBLIC_API_URL ?? 'http://localhost:8000/api';

export const certificateService = {
  list: () => http<CertificatesResponse>('/certificates'),
  verify: (certificateNumber: string) =>
    http<VerifyCertificateResponse>(`/certificates/verify/${encodeURIComponent(certificateNumber)}`, {
      skipUnauthorizedRedirect: true,
    }),
  downloadUrl: (certificateId: number) => `${API_BASE_URL}/certificates/${certificateId}/download`,
};
