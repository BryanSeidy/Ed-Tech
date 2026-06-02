<?php

namespace App\Http\Controllers;

use App\Models\Attempt;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LiveSession;
use App\Models\Progress;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class InstructorDashboardController extends Controller
{
    public function stats(Request $request): JsonResponse
    {
        $instructor = $this->authorizeInstructor($request->user());

        $courseIds = Course::query()
            ->where('instructor_id', $instructor->id)
            ->pluck('id');

        $totalStudents = Enrollment::query()
            ->whereIn('course_id', $courseIds)
            ->distinct('user_id')
            ->count('user_id');

        $publishedCourses = Course::query()
            ->where('instructor_id', $instructor->id)
            ->where('is_published', true)
            ->count();

        $upcomingLiveSessions = LiveSession::query()
            ->whereIn('course_id', $courseIds)
            ->where('end_at', '>=', now())
            ->count();

        $totalLessonSlots = (int) Course::query()
            ->where('courses.instructor_id', $instructor->id)
            ->join('modules', 'modules.course_id', '=', 'courses.id')
            ->join('lessons', 'lessons.module_id', '=', 'modules.id')
            ->join('enrollments', 'enrollments.course_id', '=', 'courses.id')
            ->count();

        $completedLessons = (int) Progress::query()
            ->join('lessons', 'lessons.id', '=', 'progress.lesson_id')
            ->join('modules', 'modules.id', '=', 'lessons.module_id')
            ->join('courses', 'courses.id', '=', 'modules.course_id')
            ->join('enrollments', function ($join): void {
                $join->on('enrollments.course_id', '=', 'courses.id')
                    ->on('enrollments.user_id', '=', 'progress.user_id');
            })
            ->where('courses.instructor_id', $instructor->id)
            ->where('progress.completed', true)
            ->count();

        $averageCompletionRate = $totalLessonSlots > 0
            ? round(($completedLessons / $totalLessonSlots) * 100, 1)
            : 0.0;

        $quizAttempts = Attempt::query()
            ->join('quizzes', 'quizzes.id', '=', 'attempts.quiz_id')
            ->join('lessons', 'lessons.id', '=', 'quizzes.lesson_id')
            ->join('modules', 'modules.id', '=', 'lessons.module_id')
            ->join('courses', 'courses.id', '=', 'modules.course_id')
            ->where('courses.instructor_id', $instructor->id);

        $totalAttempts = (clone $quizAttempts)->count();
        $passedAttempts = (clone $quizAttempts)->where('attempts.passed', true)->count();
        $quizSuccessRate = $totalAttempts > 0 ? round(($passedAttempts / $totalAttempts) * 100, 1) : 0.0;

        return response()->json([
            'data' => [
                'total_students' => $totalStudents,
                'published_courses' => $publishedCourses,
                'average_completion_rate' => $averageCompletionRate,
                'upcoming_live_sessions' => $upcomingLiveSessions,
                'quiz_success_rate' => $quizSuccessRate,
            ],
        ]);
    }

    public function courses(Request $request): JsonResponse
    {
        $instructor = $this->authorizeInstructor($request->user());

        $courses = Course::query()
            ->where('instructor_id', $instructor->id)
            ->withCount([
                'enrollments as students_count',
                'modules as modules_count',
                'liveSessions as upcoming_live_sessions_count' => fn ($query) => $query->where('end_at', '>=', now()),
            ])
            ->withCount(['modules as lessons_count' => function ($query): void {
                $query->join('lessons', 'lessons.module_id', '=', 'modules.id')
                    ->select(DB::raw('count(lessons.id)'));
            }])
            ->latest()
            ->get()
            ->map(fn (Course $course) => [
                'id' => $course->id,
                'title' => $course->title,
                'students_count' => (int) $course->students_count,
                'is_published' => (bool) $course->is_published,
                'publication_state' => $course->is_published ? 'published' : 'draft',
                'modules_count' => (int) $course->modules_count,
                'lessons_count' => (int) $course->lessons_count,
                'upcoming_live_sessions_count' => (int) $course->upcoming_live_sessions_count,
                'created_at' => $course->created_at?->toIso8601String(),
            ]);

        return response()->json(['data' => $courses]);
    }

    public function liveSessions(Request $request): JsonResponse
    {
        $instructor = $this->authorizeInstructor($request->user());

        $sessions = LiveSession::query()
            ->with('course:id,title,instructor_id')
            ->whereHas('course', fn ($query) => $query->where('instructor_id', $instructor->id))
            ->where('end_at', '>=', now()->subMinutes(10))
            ->orderBy('start_at')
            ->limit(12)
            ->get()
            ->map(fn (LiveSession $session) => [
                'id' => $session->id,
                'course_id' => $session->course_id,
                'course_title' => $session->course?->title,
                'title' => $session->title,
                'description' => $session->description,
                'start_at' => $session->start_at->toIso8601String(),
                'end_at' => $session->end_at->toIso8601String(),
                'duration_minutes' => $session->duration_minutes,
                'provider' => $session->provider,
                'room_name' => $session->room_name,
                'can_start' => $session->start_at->lessThanOrEqualTo(now()->addMinutes(15)) && $session->end_at->greaterThanOrEqualTo(now()),
            ]);

        return response()->json(['data' => $sessions]);
    }

    private function authorizeInstructor(?User $user): User
    {
        if (! $user || $user->role !== 'instructor') {
            throw new AccessDeniedHttpException('Accès réservé aux formateurs.');
        }

        return $user;
    }
}
