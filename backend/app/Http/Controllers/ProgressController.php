<?php

namespace App\Http\Controllers;

use App\Models\Progress;
use App\Models\User;
use App\Models\Lesson;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProgressController extends Controller
{
    /**
     * Display a listing of progress records. Afficher la liste des progressions 
     */
    public function index(Request $request)
    {
        $query = Progress::with(['user', 'lesson.module.course']);

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by lesson
        if ($request->has('lesson_id')) {
            $query->where('lesson_id', $request->lesson_id);
        }

        // Filter by course
        if ($request->has('course_id')) {
            $query->whereHas('lesson.module', function ($moduleQuery) use ($request) {
                $moduleQuery->where('course_id', $request->course_id);
            });
        }

        // Filter by completion status
        if ($request->has('completed')) {
            $query->where('completed', $request->boolean('completed'));
        }

        // Filter by date range
        if ($request->has('completed_from')) {
            $query->where('completed_at', '>=', $request->completed_from);
        }

        if ($request->has('completed_to')) {
            $query->where('completed_at', '<=', $request->completed_to);
        }

        $progress = $query->orderBy('completed_at', 'desc')->paginate(10);

        return response()->json($progress);
    }

    /**
     * Store a newly created progress record. ajouter une nouvelle progression
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'lesson_id' => 'required|exists:lessons,id',
            'completed' => 'boolean',
            'completed_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if progress record already exists verifier si la progression existe deja 
        $existingProgress = Progress::where('user_id', $request->user_id)
            ->where('lesson_id', $request->lesson_id)
            ->first();

        if ($existingProgress) {
            return response()->json(['message' => 'Progress record already exists for this user and lesson'], 400);
        }

        $progress = Progress::create([
            'user_id' => $request->user_id,
            'lesson_id' => $request->lesson_id,
            'completed' => $request->completed ?? false,
            'completed_at' => $request->completed ? ($request->completed_at ?? now()) : null,
        ]);

        return response()->json($progress->load(['user', 'lesson.module.course']), 201);
    }

    /**
     * Display the specified progress record. Afficher un progression specifique
     */
    public function show(Progress $progress)
    {
        $progress->load(['user', 'lesson.module.course']);

        return response()->json($progress);
    }

    /**
     * Update the specified progress record. Mise a jour des donnees de progression
     */
    public function update(Request $request, Progress $progress)
    {
        $validator = Validator::make($request->all(), [
            'completed' => 'sometimes|boolean',
            'completed_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->only(['completed', 'completed_at']);

        // If marking as completed and no completed_at provided, set current time // si 
        if (isset($data['completed']) && $data['completed'] && !isset($data['completed_at'])) {
            $data['completed_at'] = now();
        }

        // If marking as incomplete, clear completed_at
        if (isset($data['completed']) && !$data['completed']) {
            $data['completed_at'] = null;
        }

        $progress->update($data);

        return response()->json($progress->load(['user', 'lesson.module.course']));
    }

    /**
     * Remove the specified progress record.
     */
    public function destroy(Progress $progress)
    {
        $progress->delete();

        return response()->json(['message' => 'Progress record deleted successfully']);
    }

    /**
     * Mark a lesson as completed for the authenticated user. Markr que la lecon est terminer pour l'utilisateur connecter
     */
    public function markCompleted(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lesson_id' => 'required|exists:lessons,id',
            'user_id' => 'sometimes|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $userId = $request->user_id ?? Auth::id();
        $lessonId = $request->lesson_id;

        // Check if user is enrolled in the course containing this lesson
        $lesson = Lesson::with('module.course')->find($lessonId);
        $enrollment = \App\Models\Enrollment::where('user_id', $userId)
            ->where('course_id', $lesson->module->course_id)
            ->first();

        if (!$enrollment) {
            return response()->json(['message' => 'User is not enrolled in this course'], 403);
        }

        $progress = Progress::updateOrCreate(
            [
                'user_id' => $userId,
                'lesson_id' => $lessonId,
            ],
            [
                'completed' => true,
                'completed_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'Lesson marked as completed',
            'progress' => $progress->load(['user', 'lesson.module.course'])
        ]);
    }

    /**
     * Mark a lesson as incomplete for the authenticated user.
     */
    public function markIncomplete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lesson_id' => 'required|exists:lessons,id',
            'user_id' => 'sometimes|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $userId = $request->user_id ?? Auth::id();
        $lessonId = $request->lesson_id;

        $progress = Progress::where('user_id', $userId)
            ->where('lesson_id', $lessonId)
            ->first();

        if (!$progress) {
            return response()->json(['message' => 'Progress record not found'], 404);
        }

        $progress->update([
            'completed' => false,
            'completed_at' => null,
        ]);

        return response()->json([
            'message' => 'Lesson marked as incomplete',
            'progress' => $progress->load(['user', 'lesson.module.course'])
        ]);
    }

    /**
     * Get progress for a specific user.
     */
    public function getUserProgress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'sometimes|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $userId = $request->user_id ?? Auth::id();

        $query = Progress::where('user_id', $userId)->with(['lesson.module.course']);

        // Filter by course
        if ($request->has('course_id')) {
            $query->whereHas('lesson.module', function ($moduleQuery) use ($request) {
                $moduleQuery->where('course_id', $request->course_id);
            });
        }

        // Filter by completion status
        if ($request->has('completed')) {
            $query->where('completed', $request->boolean('completed'));
        }

        $progress = $query->orderBy('completed_at', 'desc')->get();

        return response()->json($progress);
    }

    /**
     * Get progress statistics for a course.
     */
    public function getCourseProgress(Request $request, Course $course)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'sometimes|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $userId = $request->user_id;

        // Get all lessons in the course
        $totalLessons = $course->modules()->with('lessons')->get()
            ->sum(function ($module) {
                return $module->lessons->count();
            });

        $query = Progress::whereHas('lesson.module', function ($moduleQuery) use ($course) {
            $moduleQuery->where('course_id', $course->id);
        });

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $completedLessons = $query->where('completed', true)->count();

        // If specific user, get their progress
        if ($userId) {
            $userProgress = $query->with(['lesson.module'])->get();

            $progressPercentage = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100, 2) : 0;

            return response()->json([
                'course_id' => $course->id,
                'user_id' => $userId,
                'total_lessons' => $totalLessons,
                'completed_lessons' => $completedLessons,
                'progress_percentage' => $progressPercentage,
                'progress_records' => $userProgress,
            ]);
        }

        // General course statistics
        $enrolledUsers = $course->enrollments()->count();
        $completedUsers = \App\Models\Enrollment::where('course_id', $course->id)
            ->whereHas('user.progress', function ($progressQuery) use ($course) {
                $progressQuery->whereHas('lesson.module', function ($moduleQuery) use ($course) {
                    $moduleQuery->where('course_id', $course->id);
                })->where('completed', true);
            })
            ->distinct('user_id')
            ->count();

        return response()->json([
            'course_id' => $course->id,
            'total_lessons' => $totalLessons,
            'enrolled_users' => $enrolledUsers,
            'completed_users' => $completedUsers,
            'completion_rate' => $enrolledUsers > 0 ? round(($completedUsers / $enrolledUsers) * 100, 2) : 0,
        ]);
    }

    /**
     * Bulk update progress records.
     */
    public function bulkUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'progress_records' => 'required|array',
            'progress_records.*.user_id' => 'required|exists:users,id',
            'progress_records.*.lesson_id' => 'required|exists:lessons,id',
            'progress_records.*.completed' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $results = [];
        foreach ($request->progress_records as $record) {
            $progress = Progress::updateOrCreate(
                [
                    'user_id' => $record['user_id'],
                    'lesson_id' => $record['lesson_id'],
                ],
                [
                    'completed' => $record['completed'],
                    'completed_at' => $record['completed'] ? ($record['completed_at'] ?? now()) : null,
                ]
            );
            $results[] = $progress->load(['user', 'lesson.module.course']);
        }

        return response()->json([
            'message' => 'Progress records updated successfully',
            'updated_records' => $results,
        ]);
    }

    /**
     * Get progress for the authenticated user across all courses.
     */
    public function myProgress(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = $user->progress()->with(['lesson.module.course']);

        // Filter by course
        if ($request->has('course_id')) {
            $query->whereHas('lesson.module', function ($moduleQuery) use ($request) {
                $moduleQuery->where('course_id', $request->course_id);
            });
        }

        // Filter by completion status
        if ($request->has('completed')) {
            $query->where('completed', $request->boolean('completed'));
        }

        $progress = $query->orderBy('completed_at', 'desc')->paginate(10);

        return response()->json($progress);
    }

    /**
     * Reset progress for a user in a course.
     */
    public function resetCourseProgress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'user_id' => 'sometimes|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $userId = $request->user_id ?? Auth::id();
        $courseId = $request->course_id;

        // Check if user is enrolled in the course
        $enrollment = \App\Models\Enrollment::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if (!$enrollment) {
            return response()->json(['message' => 'User is not enrolled in this course'], 403);
        }

        $deletedCount = Progress::where('user_id', $userId)
            ->whereHas('lesson.module', function ($moduleQuery) use ($courseId) {
                $moduleQuery->where('course_id', $courseId);
            })
            ->delete();

        return response()->json([
            'message' => 'Course progress reset successfully',
            'deleted_records' => $deletedCount,
        ]);
    }
}
