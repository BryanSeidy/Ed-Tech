<?php

namespace App\Actions\Certificates;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Progress;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class IssueCertificateAction
{
    public function execute(User $user, Course $course): ?Certificate
    {
        return DB::transaction(function () use ($user, $course): ?Certificate {
            $existingCertificate = Certificate::query()
                ->where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->lockForUpdate()
                ->first();

            if ($existingCertificate) {
                return $existingCertificate;
            }

            $eligibility = $this->eligibility($user, $course);
            if (! $eligibility['eligible']) {
                return null;
            }

            return app(\App\Services\CertificateService::class)->generateCertificate($user->id, $course->id);
        });
    }

    /**
     * @return array{eligible: bool, reason: string|null, total_lessons: int, completed_lessons: int, mandatory_quizzes: int, passed_mandatory_quizzes: int, missing_quiz_ids: array<int>}
     */
    public function eligibility(User $user, Course $course): array
    {
        $course->loadMissing(['modules.lessons.quiz']);

        $isEnrolled = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->exists();

        if (! $isEnrolled) {
            return $this->result(false, 'not_enrolled');
        }

        if (Certificate::query()->where('user_id', $user->id)->where('course_id', $course->id)->exists()) {
            return $this->result(false, 'certificate_already_exists');
        }

        $lessonIds = $course->modules
            ->flatMap(fn ($module) => $module->lessons->pluck('id'))
            ->values();

        $totalLessons = $lessonIds->count();
        if ($totalLessons === 0) {
            return $this->result(false, 'course_has_no_lessons');
        }

        $completedLessons = Progress::query()
            ->where('user_id', $user->id)
            ->whereIn('lesson_id', $lessonIds)
            ->where('completed', true)
            ->distinct('lesson_id')
            ->count('lesson_id');

        $mandatoryQuizzes = $course->modules
            ->flatMap(fn ($module) => $module->lessons)
            ->pluck('quiz')
            ->filter(fn ($quiz) => $quiz !== null && (bool) $quiz->is_published)
            ->values();

        $passedQuizIds = DB::table('attempts')
            ->join('quizzes', 'quizzes.id', '=', 'attempts.quiz_id')
            ->where('attempts.user_id', $user->id)
            ->whereNotNull('attempts.submitted_at')
            ->whereIn('attempts.quiz_id', $mandatoryQuizzes->pluck('id'))
            ->whereColumn('attempts.score', '>=', 'quizzes.passing_score')
            ->distinct()
            ->pluck('attempts.quiz_id');

        $missingQuizIds = $mandatoryQuizzes->pluck('id')->diff($passedQuizIds)->values()->all();
        $eligible = $completedLessons === $totalLessons && count($missingQuizIds) === 0;

        return [
            'eligible' => $eligible,
            'reason' => $eligible ? null : ($completedLessons < $totalLessons ? 'lessons_incomplete' : 'mandatory_quizzes_not_passed'),
            'total_lessons' => $totalLessons,
            'completed_lessons' => $completedLessons,
            'mandatory_quizzes' => $mandatoryQuizzes->count(),
            'passed_mandatory_quizzes' => $passedQuizIds->count(),
            'missing_quiz_ids' => $missingQuizIds,
        ];
    }

    /** @return array{eligible: bool, reason: string, total_lessons: int, completed_lessons: int, mandatory_quizzes: int, passed_mandatory_quizzes: int, missing_quiz_ids: array<int>} */
    private function result(bool $eligible, string $reason): array
    {
        return [
            'eligible' => $eligible,
            'reason' => $reason,
            'total_lessons' => 0,
            'completed_lessons' => 0,
            'mandatory_quizzes' => 0,
            'passed_mandatory_quizzes' => 0,
            'missing_quiz_ids' => [],
        ];
    }
}
