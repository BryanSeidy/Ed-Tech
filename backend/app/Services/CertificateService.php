<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class CertificateService
{
    public function generateCertificate(int $userId, int $courseId): Certificate
    {
        $user = User::findOrFail($userId);
        $course = Course::with('instructor')->findOrFail($courseId);

        $certificateNumber = $this->makeCertificateNumber();
        $filename = 'certificates/' . Str::slug($course->title) . '-' . Str::lower($certificateNumber) . '.pdf';

        $certificate = Certificate::create([
            'user_id' => $userId,
            'course_id' => $courseId,
            'certificate_number' => $certificateNumber,
            'certificate_url' => $filename,
            'issued_at' => now(),
        ])->load(['user', 'course.instructor']);

        $this->generateCertificatePDF($certificate);

        return $certificate;
    }

    public function verifyCertificate(string $certificateNumber): ?Certificate
    {
        return Certificate::query()
            ->with(['user:id,name,email', 'course:id,title,description,instructor_id', 'course.instructor:id,name'])
            ->where('certificate_number', Str::upper($certificateNumber))
            ->first();
    }

    public function getUserCertificates(int $userId, array $filters = [])
    {
        $query = Certificate::where('user_id', $userId)->with(['course:id,title,description,thumbnail,instructor_id', 'course.instructor:id,name']);

        if (isset($filters['course_id'])) {
            $query->where('course_id', $filters['course_id']);
        }

        return $query->orderBy('issued_at', 'desc')->get();
    }

    private function generateCertificatePDF(Certificate $certificate): void
    {
        $certificate->loadMissing(['user', 'course.instructor']);

        $verificationUrl = rtrim((string) config('app.frontend_url', 'http://localhost:3000'), '/')
            . '/verify/' . $certificate->certificate_number;

        $viewData = [
            'certificate' => $certificate,
            'user' => $certificate->user,
            'course' => $certificate->course,
            'verificationUrl' => $verificationUrl,
            'qrCodeSvg' => $this->makeQrCodeSvg($verificationUrl),
        ];

        Storage::disk('public')->makeDirectory('certificates');

        try {
            $pdf = app('dompdf.wrapper');
            $pdf->loadView('certificates.template', $viewData)->setPaper('a4', 'landscape');
            Storage::disk('public')->put($certificate->certificate_url, $pdf->output());
        } catch (Throwable $exception) {
            Storage::disk('public')->put($certificate->certificate_url, $this->fallbackPdf($viewData));
        }

        if (! Storage::disk('public')->exists($certificate->certificate_url)) {
            throw new RuntimeException('Certificate PDF could not be stored.');
        }
    }

    private function makeCertificateNumber(): string
    {
        do {
            $number = 'EDT-' . now()->format('Y') . '-' . Str::upper(Str::random(10));
        } while (Certificate::where('certificate_number', $number)->exists());

        return $number;
    }

    private function makeQrCodeSvg(string $verificationUrl): string
    {
        if (class_exists(\BaconQrCode\Writer::class)) {
            $renderer = new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(160, 1),
                new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
            );

            return (new \BaconQrCode\Writer($renderer))->writeString($verificationUrl);
        }

        $hash = hash('sha256', $verificationUrl);
        $cells = '';
        $size = 21;
        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $index = ($x + ($y * $size)) % strlen($hash);
                $bit = hexdec($hash[$index]) % 2 === 0;
                $finder = ($x < 7 && $y < 7) || ($x > 13 && $y < 7) || ($x < 7 && $y > 13);
                if ($bit || $finder) {
                    $cells .= '<rect x="' . $x . '" y="' . $y . '" width="1" height="1"/>';
                }
            }
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 21 21" shape-rendering="crispEdges"><rect width="21" height="21" fill="#fff"/><g fill="#0a2540">' . $cells . '</g></svg>';
    }

    /** @param array{certificate: Certificate, user: User, course: Course, verificationUrl: string, qrCodeSvg: string} $data */
    private function fallbackPdf(array $data): string
    {
        $lines = [
            'ED-TECH Certificate',
            'Certificate number: ' . $data['certificate']->certificate_number,
            'Awarded to: ' . $data['user']->name,
            'Course: ' . $data['course']->title,
            'Issued on: ' . $data['certificate']->issued_at->format('Y-m-d'),
            'Verify: ' . $data['verificationUrl'],
        ];

        $stream = "BT\n/F1 24 Tf\n72 520 Td\n";
        foreach ($lines as $line) {
            $stream .= '(' . str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line) . ") Tj\n0 -38 Td\n";
        }
        $stream .= "ET";

        $objects = [
            '1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj',
            '2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj',
            '3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >> endobj',
            '4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj',
            '5 0 obj << /Length ' . strlen($stream) . " >> stream\n" . $stream . "\nendstream endobj",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object . "\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= str_pad((string) $offsets[$i], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }
        $pdf .= "trailer << /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n" . $xref . "\n%%EOF";

        return $pdf;
    }
}
