<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnrollmentController extends Controller
{
    /**
     * Display a listing of enrollments for a course (instructor only).
     */
    public function index(Course $course)
    {
        // Check if user is the instructor
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $enrollments = $course->enrollments()
            ->with('user:id,name,email')
            ->orderBy('enrolled_at', 'desc')
            ->paginate(20);

        return response()->json($enrollments);
    }

    /**
     * Display the specified enrollment.
     */
    public function show(Enrollment $enrollment)
    {
        $user = Auth::user();

        // Check if user is the enrolled user or the course instructor
        if ($enrollment->user_id !== $user->id && $enrollment->course->instructor_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $enrollment->load(['user:id,name,email', 'course:id,title']);

        return response()->json($enrollment);
    }

    /**
     * Enroll a user in a course.
     */
    public function store(Request $request, Course $course)
    {
        $user = Auth::user();

        // Check if course is published
        if (!$course->is_published) {
            return response()->json(['message' => 'Course is not published'], 400);
        }

        // Check if already enrolled
        if ($course->enrollments()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Already enrolled in this course'], 400);
        }

        // Check if user is not the instructor (instructors shouldn't enroll in their own courses)
        if ($course->instructor_id === $user->id) {
            return response()->json(['message' => 'Cannot enroll in your own course'], 400);
        }

        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        $enrollment->load(['user:id,name,email', 'course:id,title']);

        return response()->json([
            'message' => 'Successfully enrolled in the course',
            'enrollment' => $enrollment
        ], 201);
    }

    /**
     * Remove the specified enrollment (unenroll).
     */
    public function destroy(Enrollment $enrollment)
    {
        $user = Auth::user();

        // Check if user is the enrolled user or the course instructor
        if ($enrollment->user_id !== $user->id && $enrollment->course->instructor_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $enrollment->delete();

        return response()->json(['message' => 'Successfully unenrolled from the course']);
    }

    /**
     * Get enrollments for the authenticated user.
     */
    public function myEnrollments(Request $request)
    {
        $user = Auth::user();

        $query = $user->enrollments()->with(['course:id,title,description,thumbnail,instructor_id']);

        // Filter by course status
        if ($request->has('published')) {
            $query->whereHas('course', function ($q) use ($request) {
                $q->where('is_published', $request->boolean('published'));
            });
        }

        // Search by course title
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('course', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        $enrollments = $query->orderBy('enrolled_at', 'desc')->paginate(10);

        return response()->json($enrollments);
    }

    /**
     * Get enrollment statistics for a course (instructor only).
     */
    public function statistics(Course $course)
    {
        // Check if user is the instructor
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $stats = [
            'total_enrollments' => $course->enrollments()->count(),
            'enrollments_this_month' => $course->enrollments()
                ->whereMonth('enrolled_at', now()->month)
                ->whereYear('enrolled_at', now()->year)
                ->count(),
            'enrollments_this_year' => $course->enrollments()
                ->whereYear('enrolled_at', now()->year)
                ->count(),
            'recent_enrollments' => $course->enrollments()
                ->with('user:id,name,email')
                ->orderBy('enrolled_at', 'desc')
                ->limit(5)
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Check if user is enrolled in a course.
     */
    public function checkEnrollment(Course $course)
    {
        $user = Auth::user();

        $enrollment = $course->enrollments()->where('user_id', $user->id)->first();

        if ($enrollment) {
            return response()->json([
                'enrolled' => true,
                'enrolled_at' => $enrollment->enrolled_at,
                'enrollment_id' => $enrollment->id
            ]);
        }

        return response()->json(['enrolled' => false]);
    }

    /**
     * Bulk enroll users in a course (instructor only).
     */
    public function bulkEnroll(Request $request, Course $course)
    {
        // Check if user is the instructor
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        $userIds = $request->user_ids;

        // Remove users who are already enrolled or are the instructor
        $existingEnrollments = $course->enrollments()->whereIn('user_id', $userIds)->pluck('user_id');
        $userIds = array_diff($userIds, $existingEnrollments->toArray(), [$course->instructor_id]);

        if (empty($userIds)) {
            return response()->json(['message' => 'All users are already enrolled or invalid'], 400);
        }

        $enrollments = [];
        foreach ($userIds as $userId) {
            $enrollments[] = [
                'user_id' => $userId,
                'course_id' => $course->id,
                'enrolled_at' => now(),
            ];
        }

        Enrollment::insert($enrollments);

        return response()->json([
            'message' => 'Users enrolled successfully',
            'enrolled_count' => count($enrollments)
        ]);
    }

    /**
     * Bulk unenroll users from a course (instructor only).
     */
    public function bulkUnenroll(Request $request, Course $course)
    {
        // Check if user is the instructor
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        $userIds = $request->user_ids;

        $deletedCount = $course->enrollments()->whereIn('user_id', $userIds)->delete();

        return response()->json([
            'message' => 'Users unenrolled successfully',
            'unenrolled_count' => $deletedCount
        ]);
    }

    /**
     * Export enrollments data for a course (instructor only).
     */
    public function export(Course $course)
    {
        // Check if user is the instructor
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $enrollments = $course->enrollments()
            ->with('user:id,name,email')
            ->orderBy('enrolled_at', 'desc')
            ->get()
            ->map(function ($enrollment) {
                return [
                    'user_name' => $enrollment->user->name,
                    'user_email' => $enrollment->user->email,
                    'enrolled_at' => $enrollment->enrolled_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'course_title' => $course->title,
            'total_enrollments' => $enrollments->count(),
            'enrollments' => $enrollments
        ]);
    }
}
