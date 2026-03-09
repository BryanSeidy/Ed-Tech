<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ModuleController extends Controller
{
    /**
     * Display a listing of modules for a course.
     */
    public function index(Course $course)
    {
        $user = Auth::user();

        // Check if user is enrolled or is the instructor
        if ($course->instructor_id !== $user->id && !$course->enrollments()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $modules = $course->modules()
            ->with(['lessons' => function ($query) {
                $query->orderBy('position')->select('id', 'module_id', 'title', 'position', 'duration');
            }])
            ->orderBy('position')
            ->get();

        return response()->json($modules);
    }

    /**
     * Store a newly created module.
     */
    public function store(Request $request, Course $course)
    {
        // Check if user is the instructor
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'position' => 'required|integer|min:1',
        ]);

        // Check if position is unique within the course
        if ($course->modules()->where('position', $request->position)->exists()) {
            return response()->json(['message' => 'Position already taken'], 400);
        }

        $module = $course->modules()->create($request->only([
            'title', 'description', 'position'
        ]));

        return response()->json($module, 201);
    }

    /**
     * Display the specified module.
     */
    public function show(Module $module)
    {
        $course = $module->course;
        $user = Auth::user();

        // Check if user is enrolled or is the instructor
        if ($course->instructor_id !== $user->id && !$course->enrollments()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $module->load([
            'lessons' => function ($query) use ($user) {
                $query->orderBy('position')
                      ->with(['progress' => function ($q) use ($user) {
                          $q->where('user_id', $user->id);
                      }]);
            },
            'course:id,title'
        ]);

        return response()->json($module);
    }

    /**
     * Update the specified module.
     */
    public function update(Request $request, Module $module)
    {
        $course = $module->course;

        // Check if user is the instructor
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'position' => 'sometimes|required|integer|min:1',
        ]);

        // Check position uniqueness if updating position
        if ($request->has('position') && $request->position !== $module->position) {
            if ($course->modules()->where('position', $request->position)->where('id', '!=', $module->id)->exists()) {
                return response()->json(['message' => 'Position already taken'], 400);
            }
        }

        $module->update($request->only([
            'title', 'description', 'position'
        ]));

        return response()->json($module);
    }

    /**
     * Remove the specified module.
     */
    public function destroy(Module $module)
    {
        $course = $module->course;

        // Check if user is the instructor
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $module->delete();

        return response()->json(['message' => 'Module deleted successfully']);
    }

    /**
     * Reorder modules in a course.
     */
    public function reorder(Request $request, Course $course)
    {
        // Check if user is the instructor
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'modules' => 'required|array',
            'modules.*.id' => 'required|integer|exists:modules,id',
            'modules.*.position' => 'required|integer|min:1',
        ]);

        // Validate that all modules belong to the course
        $moduleIds = collect($request->modules)->pluck('id');
        $courseModuleIds = $course->modules()->pluck('id');

        if ($moduleIds->diff($courseModuleIds)->isNotEmpty()) {
            return response()->json(['message' => 'Some modules do not belong to this course'], 400);
        }

        // Update positions
        foreach ($request->modules as $moduleData) {
            $course->modules()
                ->where('id', $moduleData['id'])
                ->update(['position' => $moduleData['position']]);
        }

        return response()->json(['message' => 'Modules reordered successfully']);
    }

    /**
     * Get module statistics for instructor.
     */
    public function statistics(Module $module)
    {
        $course = $module->course;

        // Check if user is the instructor
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $stats = [
            'total_lessons' => $module->lessons()->count(),
            'total_duration' => $module->lessons()->sum('duration'),
            'completed_lessons' => $module->lessons()
                ->whereHas('progress', function ($query) {
                    $query->where('completed', true);
                })
                ->count(),
            'average_completion_rate' => $this->calculateAverageCompletionRate($module),
        ];

        return response()->json($stats);
    }

    /**
     * Get next module in the course.
     */
    public function nextModule(Module $module)
    {
        $course = $module->course;
        $user = Auth::user();

        // Check if user is enrolled or instructor
        if ($course->instructor_id !== $user->id && !$course->enrollments()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $nextModule = $course->modules()
            ->where('position', '>', $module->position)
            ->orderBy('position')
            ->first();

        if (!$nextModule) {
            return response()->json(['message' => 'No next module available']);
        }

        return response()->json($nextModule);
    }

    /**
     * Get previous module in the course.
     */
    public function previousModule(Module $module)
    {
        $course = $module->course;
        $user = Auth::user();

        // Check if user is enrolled or instructor
        if ($course->instructor_id !== $user->id && !$course->enrollments()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $previousModule = $course->modules()
            ->where('position', '<', $module->position)
            ->orderBy('position', 'desc')
            ->first();

        if (!$previousModule) {
            return response()->json(['message' => 'No previous module available']);
        }

        return response()->json($previousModule);
    }

    /**
     * Get module progress for authenticated user.
     */
    public function progress(Module $module)
    {
        $course = $module->course;
        $user = Auth::user();

        // Check if user is enrolled
        if (!$course->enrollments()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Not enrolled in this course'], 403);
        }

        $totalLessons = $module->lessons()->count();
        $completedLessons = $module->lessons()
            ->whereHas('progress', function ($query) use ($user) {
                $query->where('user_id', $user->id)->where('completed', true);
            })
            ->count();

        $progress = [
            'total_lessons' => $totalLessons,
            'completed_lessons' => $completedLessons,
            'completion_percentage' => $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100, 2) : 0,
            'is_completed' => $totalLessons > 0 && $completedLessons === $totalLessons,
        ];

        return response()->json($progress);
    }

    /**
     * Calculate average completion rate for a module.
     */
    private function calculateAverageCompletionRate(Module $module)
    {
        $totalEnrollments = $module->course->enrollments()->count();

        if ($totalEnrollments === 0) {
            return 0;
        }

        $totalLessons = $module->lessons()->count();

        if ($totalLessons === 0) {
            return 0;
        }

        $completedLessons = 0;
        foreach ($module->course->enrollments as $enrollment) {
            $userCompleted = $module->lessons()
                ->whereHas('progress', function ($query) use ($enrollment) {
                    $query->where('user_id', $enrollment->user_id)->where('completed', true);
                })
                ->count();
            $completedLessons += $userCompleted;
        }

        return round(($completedLessons / ($totalEnrollments * $totalLessons)) * 100, 2);
    }
}
