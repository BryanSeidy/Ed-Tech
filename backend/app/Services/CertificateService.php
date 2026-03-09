<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\User;
use App\Models\Course;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificateService
{
    /**
     * Generate a certificate for a user who completed a course.
     */
    public function generateCertificate(int $userId, int $courseId): Certificate
    {
        $user = User::findOrFail($userId);
        $course = Course::findOrFail($courseId);

        // Generate certificate filename
        $filename = 'certificates/' . Str::slug($course->title) . '_' . $user->id . '_' . time() . '.pdf';

        // For now, we'll just store a placeholder. In a real implementation,
        // you would generate an actual PDF certificate using a library like TCPDF or DomPDF
        // with the course details, user name, completion date, etc.

        // Create certificate record
        $certificate = Certificate::create([
            'user_id' => $userId,
            'course_id' => $courseId,
            'certificate_url' => $filename,
            'issued_at' => now(),
        ]);

        // TODO: Generate actual PDF certificate file
        // $this->generateCertificatePDF($certificate);

        return $certificate;
    }

    /**
     * Generate PDF certificate file.
     * This is a placeholder - implement actual PDF generation.
     */
    private function generateCertificatePDF(Certificate $certificate): void
    {
        // Implementation would use a PDF library to create a certificate
        // with course title, user name, completion date, instructor name, etc.

        // Example using a library like DomPDF:
        /*
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('certificates.template', [
            'certificate' => $certificate,
            'user' => $certificate->user,
            'course' => $certificate->course,
        ]);

        Storage::disk('public')->put($certificate->certificate_url, $pdf->output());
        */
    }

    /**
     * Verify certificate authenticity.
     */
    public function verifyCertificate(int $certificateId): ?Certificate
    {
        return Certificate::with(['user', 'course'])->find($certificateId);
    }

    /**
     * Get certificates for a user.
     */
    public function getUserCertificates(int $userId, array $filters = [])
    {
        $query = Certificate::where('user_id', $userId)->with('course');

        if (isset($filters['course_id'])) {
            $query->where('course_id', $filters['course_id']);
        }

        if (isset($filters['issued_from'])) {
            $query->where('issued_at', '>=', $filters['issued_from']);
        }

        if (isset($filters['issued_to'])) {
            $query->where('issued_at', '<=', $filters['issued_to']);
        }

        return $query->orderBy('issued_at', 'desc')->get();
    }

    /**
     * Check if user is eligible for certificate.
     */
    public function checkEligibility(int $userId, int $courseId): array
    {
        // Check if user is enrolled
        $enrollment = \App\Models\Enrollment::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if (!$enrollment) {
            return [
                'eligible' => false,
                'reason' => 'User is not enrolled in this course'
            ];
        }

        // Check if certificate already exists
        $existingCertificate = Certificate::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if ($existingCertificate) {
            return [
                'eligible' => false,
                'reason' => 'Certificate already exists',
                'certificate' => $existingCertificate
            ];
        }

        // Check course completion
        $course = Course::with('modules.lessons')->find($courseId);
        $totalLessons = $course->modules->sum(function ($module) {
            return $module->lessons->count();
        });

        $completedLessons = \App\Models\Progress::where('user_id', $userId)
            ->whereHas('lesson', function ($query) use ($courseId) {
                $query->whereHas('module', function ($moduleQuery) use ($courseId) {
                    $moduleQuery->where('course_id', $courseId);
                });
            })
            ->where('completed', true)
            ->count();

        $eligible = $completedLessons >= $totalLessons;

        return [
            'eligible' => $eligible,
            'completed_lessons' => $completedLessons,
            'total_lessons' => $totalLessons,
            'reason' => $eligible ? null : 'Course not completed yet'
        ];
    }
}
