<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Progress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LessonController extends Controller
{
    /**
     * Display a listing of lessons for a module.
     */
    public function index(Request $request, Module $module)
    {
        // Check if user is enrolled in the course or is the instructor
        $course = $module->course;
        $user = Auth::user();

        if ($course->instructor_id !== $user->id && !$course->enrollments()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $lessons = $module->lessons()
            ->orderBy('position')
            ->with(['progress' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->get();

        return response()->json($lessons);
    }

    /**
     * Store a newly created lesson.
     */
    public function store(Request $request, Module $module)
    {
        $course = $module->course;

        // Check if user is the instructor
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'video_url' => 'nullable|url',
            'duration' => 'nullable|integer|min:0',
            'position' => 'required|integer|min:1',
        ]);

        // Check if position is unique within the module
        if ($module->lessons()->where('position', $request->position)->exists()) {
            return response()->json(['message' => 'Position already taken'], 400);
        }

        $lesson = $module->lessons()->create($request->only([
            'title', 'content', 'video_url', 'duration', 'position'
        ]));

        return response()->json($lesson, 201);
    }

    /**
     * Display the specified lesson.
     */
    public function show(Lesson $lesson)
    {
        $course = $lesson->course;
        $user = Auth::user();

        // Check if user is enrolled or is the instructor
        if ($course->instructor_id !== $user->id && !$course->enrollments()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $lesson->load(['module', 'progress' => function ($query) use ($user) {
            $query->where('user_id', $user->id);
        }]);

        return response()->json($lesson);
    }

    /**
     * Update the specified lesson.
     */
    public function update(Request $request, Lesson $lesson)
    {
        $course = $lesson->course;

        // Check if user is the instructor
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'nullable|string',
            'video_url' => 'nullable|url',
            'duration' => 'nullable|integer|min:0',
            'position' => 'sometimes|required|integer|min:1',
        ]);

        // Check position uniqueness if updating position
        if ($request->has('position') && $request->position !== $lesson->position) {
            if ($lesson->module->lessons()->where('position', $request->position)->where('id', '!=', $lesson->id)->exists()) {
                return response()->json(['message' => 'Position already taken'], 400);
            }
        }

        $lesson->update($request->only([
            'title', 'content', 'video_url', 'duration', 'position'
        ]));

        return response()->json($lesson);
    }

    /**
     * Remove the specified lesson.
     */
    public function destroy(Lesson $lesson)
    {
        $course = $lesson->course;

        // Check if user is the instructor
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $lesson->delete();

        return response()->json(['message' => 'Lesson deleted successfully']);
    }

    /**
     * Mark lesson as completed for the authenticated user.
     */
    public function markCompleted(Lesson $lesson)
    {
        $course = $lesson->course;
        $user = Auth::user();

        // Check if user is enrolled
        if (!$course->enrollments()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Not enrolled in this course'], 403);
        }

        $progress = Progress::updateOrCreate(
            [
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
            ],
            [
                'completed' => true,
                'completed_at' => now(),
            ]
        );

        return response()->json(['message' => 'Lesson marked as completed', 'progress' => $progress]);
    }

    /**
     * Mark lesson as not completed for the authenticated user.
     */
    public function markIncomplete(Lesson $lesson)
    {
        $course = $lesson->course;
        $user = Auth::user();

        // Check if user is enrolled
        if (!$course->enrollments()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Not enrolled in this course'], 403);
        }

        $progress = Progress::updateOrCreate(
            [
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
            ],
            [
                'completed' => false,
                'completed_at' => null,
            ]
        );

        return response()->json(['message' => 'Lesson marked as incomplete', 'progress' => $progress]);
    }

    /**
     * Get user's progress for a specific lesson.
     */
    public function getProgress(Lesson $lesson)
    {
        $user = Auth::user();

        $progress = $lesson->progress()->where('user_id', $user->id)->first();

        if (!$progress) {
            return response()->json(['completed' => false, 'completed_at' => null]);
        }

        return response()->json($progress);
    }

    /**
     * Get next lesson in the module.
     */
    public function nextLesson(Lesson $lesson)
    {
        $course = $lesson->course;
        $user = Auth::user();

        // Check if user is enrolled or instructor
        if ($course->instructor_id !== $user->id && !$course->enrollments()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $nextLesson = $lesson->module->lessons()
            ->where('position', '>', $lesson->position)
            ->orderBy('position')
            ->first();

        if (!$nextLesson) {
            return response()->json(['message' => 'No next lesson available']);
        }

        return response()->json($nextLesson);
    }

    /**
     * Get previous lesson in the module.
     */
    public function previousLesson(Lesson $lesson)
    {
        $course = $lesson->course;
        $user = Auth::user();

        // Check if user is enrolled or instructor
        if ($course->instructor_id !== $user->id && !$course->enrollments()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $previousLesson = $lesson->module->lessons()
            ->where('position', '<', $lesson->position)
            ->orderBy('position', 'desc')
            ->first();

        if (!$previousLesson) {
            return response()->json(['message' => 'No previous lesson available']);
        }

        return response()->json($previousLesson);
    }

    /**
     * Reorder lessons in a module.
     */
    public function reorder(Request $request, Module $module)
    {
        $course = $module->course;

        // Check if user is the instructor
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'lessons' => 'required|array',
            'lessons.*.id' => 'required|integer|exists:lessons,id',
            'lessons.*.position' => 'required|integer|min:1',
        ]);

        // Validate that all lessons belong to the module
        $lessonIds = collect($request->lessons)->pluck('id');
        $moduleLessonIds = $module->lessons()->pluck('id');

        if ($lessonIds->diff($moduleLessonIds)->isNotEmpty()) {
            return response()->json(['message' => 'Some lessons do not belong to this module'], 400);
        }

        // Update positions
        foreach ($request->lessons as $lessonData) {
            $module->lessons()
                ->where('id', $lessonData['id'])
                ->update(['position' => $lessonData['position']]);
        }

        return response()->json(['message' => 'Lessons reordered successfully']);
    }
}
