<?php

namespace App\Services;

use App\Models\Attempt;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Progress;
use App\Models\User;
use Illuminate\Support\Collection;

class LearningGateService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function buildCourseLessonStates(Course $course, User $user): array
    {
        if ($this->canBypassLocks($course, $user)) {
            return $this->buildInstructorStates($course);
        }

        $course->loadMissing(['modules.lessons.quiz']);
        $lessons = $this->orderedLessons($course);
        $lessonIds = $lessons->pluck('id')->all();
        $quizIds = $lessons
            ->map(fn (Lesson $lesson) => $lesson->quiz?->id)
            ->filter()
            ->values()
            ->all();

        $completedLessonIds = Progress::query()
            ->where('user_id', $user->id)
            ->whereIn('lesson_id', $lessonIds)
            ->where('completed', true)
            ->pluck('lesson_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $bestAttempts = Attempt::query()
            ->where('user_id', $user->id)
            ->whereIn('quiz_id', $quizIds)
            ->whereNotNull('submitted_at')
            ->orderByDesc('score')
            ->get()
            ->groupBy('quiz_id')
            ->map(fn (Collection $attempts) => $attempts->first());

        $states = [];
        $lock = null;

        foreach ($lessons as $lesson) {
            $quiz = $lesson->quiz;
            $attempt = $quiz ? $bestAttempts->get($quiz->id) : null;
            $quizScore = $attempt?->score;
            $quizPassed = $quiz ? (bool) ($attempt?->passed ?? false) : null;
            $isCompleted = in_array($lesson->id, $completedLessonIds, true);

            $states[$lesson->id] = [
                'is_locked' => $lock !== null,
                'lock_reason' => $lock['reason'] ?? null,
                'locked_by_lesson_id' => $lock['lesson_id'] ?? null,
                'locked_by_quiz_id' => $lock['quiz_id'] ?? null,
                'is_completed' => $isCompleted,
                'quiz_id' => $quiz?->id,
                'quiz_passed' => $quizPassed,
                'quiz_score' => $quizScore,
                'passing_score' => $quiz?->passing_score,
            ];

            if ($quiz) {
                if (! $quizPassed) {
                    $lock = [
                        'reason' => 'quiz_not_passed',
                        'lesson_id' => $lesson->id,
                        'quiz_id' => $quiz->id,
                    ];
                }

                continue;
            }

            if (! $isCompleted) {
                $lock = [
                    'reason' => 'previous_lesson_not_completed',
                    'lesson_id' => $lesson->id,
                    'quiz_id' => null,
                ];
            }
        }

        return $states;
    }

    public function enrichCourseForUser(Course $course, User $user): Course
    {
        $course->loadMissing(['modules.lessons.quiz']);
        $states = $this->buildCourseLessonStates($course, $user);

        $course->modules->each(function ($module) use ($states): void {
            $module->lessons->each(function (Lesson $lesson) use ($states): void {
                $state = $states[$lesson->id] ?? $this->defaultState($lesson);
                $lesson->setAttribute('learning_state', $state);
                $lesson->setAttribute('is_locked', $state['is_locked']);
                $lesson->setAttribute('quiz_id', $state['quiz_id']);
                $lesson->setAttribute('quiz_passed', $state['quiz_passed']);
            });
        });

        return $course;
    }

    /**
     * @return array<string, mixed>
     */
    public function lessonState(Lesson $lesson, User $user): array
    {
        $course = $lesson->module->course;
        $states = $this->buildCourseLessonStates($course, $user);

        return $states[$lesson->id] ?? $this->defaultState($lesson);
    }

    public function isLessonAccessible(Lesson $lesson, User $user): bool
    {
        return ! (bool) $this->lessonState($lesson, $user)['is_locked'];
    }

    public function hasPassedLessonQuiz(Lesson $lesson, int $userId): bool
    {
        $quiz = $lesson->quiz;

        if (! $quiz) {
            return true;
        }

        return Attempt::query()
            ->where('user_id', $userId)
            ->where('quiz_id', $quiz->id)
            ->where('passed', true)
            ->whereNotNull('submitted_at')
            ->exists();
    }

    private function canBypassLocks(Course $course, User $user): bool
    {
        return $course->instructor_id === $user->id || $user->role === 'admin';
    }

    /**
     * @return Collection<int, Lesson>
     */
    private function orderedLessons(Course $course): Collection
    {
        $course->loadMissing(['modules.lessons.quiz']);

        return $course->modules
            ->sortBy('position')
            ->flatMap(fn ($module) => $module->lessons->sortBy('position')->values())
            ->values();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildInstructorStates(Course $course): array
    {
        return $this->orderedLessons($course)
            ->mapWithKeys(fn (Lesson $lesson) => [$lesson->id => $this->defaultState($lesson)])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultState(Lesson $lesson): array
    {
        $lesson->loadMissing('quiz');

        return [
            'is_locked' => false,
            'lock_reason' => null,
            'locked_by_lesson_id' => null,
            'locked_by_quiz_id' => null,
            'is_completed' => false,
            'quiz_id' => $lesson->quiz?->id,
            'quiz_passed' => null,
            'quiz_score' => null,
            'passing_score' => $lesson->quiz?->passing_score,
        ];
    }
}
