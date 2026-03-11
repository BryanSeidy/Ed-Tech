<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\Progress;
use Carbon\CarbonImmutable;

class ProgressService
{
    public function markCompleted(int $userId, int $lessonId): Progress
    {
        Lesson::query()->findOrFail($lessonId);

        return Progress::updateOrCreate(
            ['user_id' => $userId, 'lesson_id' => $lessonId],
            ['completed' => true, 'completed_at' => CarbonImmutable::now()]
        );
    }

    public function getCourseProgress(int $userId, int $courseId): array
    {
        $lessonIds = Lesson::query()
            ->whereHas('module', fn ($query) => $query->where('course_id', $courseId))
            ->pluck('id');

        $totalLessons = $lessonIds->count();
        $completedLessons = Progress::query()
            ->where('user_id', $userId)
            ->whereIn('lesson_id', $lessonIds)
            ->where('completed', true)
            ->count();

        $percentage = $totalLessons > 0 ? (int) round(($completedLessons / $totalLessons) * 100) : 0;

        return [
            'course_id' => $courseId,
            'total_lessons' => $totalLessons,
            'completed_lessons' => $completedLessons,
            'percentage' => $percentage,
        ];
    }
}
