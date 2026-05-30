export type CertificateCourse = {
  id: number;
  title: string;
  description?: string | null;
  instructor_name?: string | null;
};

export type Certificate = {
  id: number;
  certificate_number: string;
  certificate_url: string;
  download_url: string;
  verification_url: string;
  issued_at: string;
  course: CertificateCourse;
};

export type PublicCertificate = {
  certificate_number: string;
  issued_at: string;
  student_name: string;
  course: CertificateCourse;
};

export type CertificatesResponse = {
  data: Certificate[];
};

export type VerifyCertificateResponse = {
  valid: boolean;
  data?: PublicCertificate;
  message?: string;
};
