<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Database\Eloquent\Collection;

class CourseService
{
    public function listPublished(): Collection
    {
        return Course::query()
            ->where('is_published', true)
            ->with(['instructor', 'modules.lessons'])
            ->latest()
            ->get();
    }

    public function findById(int $courseId): Course
    {
        return Course::query()->with(['instructor', 'modules.lessons.quiz'])->findOrFail($courseId);
    }

    public function enroll(int $userId, int $courseId): Enrollment
    {
        return Enrollment::firstOrCreate([
            'user_id' => $userId,
            'course_id' => $courseId,
        ]);
    }
}
