<?php

namespace App\Services;

use App\Models\Certificate;

class CertificateService
{
    public function issue(int $userId, int $courseId): Certificate
    {
        $certificateUrl = sprintf('/certificates/%d/%d.pdf', $courseId, $userId);

        return Certificate::firstOrCreate(
            ['user_id' => $userId, 'course_id' => $courseId],
            ['certificate_url' => $certificateUrl]
        );
    }
}
