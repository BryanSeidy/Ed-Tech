<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    /**
     * Display a listing of the courses.
     */
    public function index(Request $request)
    {
        $query = Course::with('instructor');

        // Filter by published status
        if ($request->has('published')) {
            $query->where('is_published', $request->boolean('published'));
        }

        // Filter by instructor
        if ($request->has('instructor_id')) {
            $query->where('instructor_id', $request->instructor_id);
        }

        // Search by title or description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $courses = $query->paginate(10);

        return response()->json($courses);
    }

    /**
     * Store a newly created course.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_published' => 'boolean',
        ]);

        $data = $request->only(['title', 'description', 'is_published']);
        $data['instructor_id'] = Auth::id();

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('courses', 'public');
        }

        $course = Course::create($data);

        return response()->json($course->load('instructor'), 201);
    }

    /**
     * Display the specified course.
     */
    public function show(Course $course)
    {
        $course->load(['instructor', 'modules.lessons', 'quizzes']);

        return response()->json($course);
    }

    /**
     * Update the specified course.
     */
    public function update(Request $request, Course $course)
    {
        // Check if user is the instructor
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_published' => 'boolean',
        ]);

        $data = $request->only(['title', 'description', 'is_published']);

        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            if ($course->thumbnail) {
                Storage::disk('public')->delete($course->thumbnail);
            }
            $data['thumbnail'] = $request->file('thumbnail')->store('courses', 'public');
        }

        $course->update($data);

        return response()->json($course->load('instructor'));
    }

    /**
     * Remove the specified course.
     */
    public function destroy(Course $course)
    {
        // Check if user is the instructor
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Delete thumbnail
        if ($course->thumbnail) {
            Storage::disk('public')->delete($course->thumbnail);
        }

        $course->delete();

        return response()->json(['message' => 'Course deleted successfully']);
    }

    /**
     * Enroll a user in a course.
     */
    public function enroll(Course $course)
    {
        if (!$course->is_published) {
            return response()->json(['message' => 'Course is not published'], 400);
        }

        $user = Auth::user();

        // Check if already enrolled
        if ($course->enrollments()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Already enrolled in this course'], 400);
        }

        Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        return response()->json(['message' => 'Successfully enrolled in the course']);
    }

    /**
     * Unenroll a user from a course.
     */
    public function unenroll(Course $course)
    {
        $user = Auth::user();

        $enrollment = $course->enrollments()->where('user_id', $user->id)->first();

        if (!$enrollment) {
            return response()->json(['message' => 'Not enrolled in this course'], 400);
        }

        $enrollment->delete();

        return response()->json(['message' => 'Successfully unenrolled from the course']);
    }

    /**
     * Get courses for the authenticated user.
     */
    public function myCourses(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = $user->enrolledCourses()->with('instructor');

        // Filter by published status
        if ($request->has('published')) {
            $query->where('is_published', $request->boolean('published'));
        }

        // Search by title
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%");
        }

        $courses = $query->paginate(10);

        return response()->json($courses);
    }

    /**
     * Get courses created by the authenticated instructor.
     */
    public function myCreatedCourses(Request $request)
    {
        $user = Auth::user();

        $query = Course::where('instructor_id', $user->id)->with('enrollments');

        if ($request->has('published')) {
            $query->where('is_published', $request->boolean('published'));
        }

        $courses = $query->paginate(10);

        return response()->json($courses);
    }

    /**
     * Publish a course.
     */
    public function publish(Course $course)
    {
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $course->update(['is_published' => true]);

        return response()->json(['message' => 'Course published successfully']);
    }

    /**
     * Unpublish a course.
     */
    public function unpublish(Course $course)
    {
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $course->update(['is_published' => false]);

        return response()->json(['message' => 'Course unpublished successfully']);
    }

    /**
     * Get course statistics for instructor.
     */
    public function statistics(Course $course)
    {
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $stats = [
            'total_enrollments' => $course->enrollments()->count(),
            'total_modules' => $course->modules()->count(),
            'total_lessons' => $course->modules()->withCount('lessons')->get()->sum('lessons_count'),
            'total_quizzes' => $course->quizzes()->count(),
        ];

        return response()->json($stats);
    }
}
