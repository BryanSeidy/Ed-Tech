<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Progress;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by email verification status
        if ($request->has('verified')) {
            $query->whereNotNull('email_verified_at');
        }

        // Filter by creation date range
        if ($request->has('created_from')) {
            $query->where('created_at', '>=', $request->created_from);
        }

        if ($request->has('created_to')) {
            $query->where('created_at', '<=', $request->created_to);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json($users);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json($user, 201);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        return response()->json($user);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'sometimes|required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->only(['name', 'email']);

        if ($request->has('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json($user);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        // Prevent deletion of own account
        if ($user->id === Auth::id()) {
            return response()->json(['message' => 'Cannot delete your own account'], 400);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    /**
     * Get the authenticated user's profile.
     */
    public function profile()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load([
            'courses',
            'enrollments.course',
            'certificates.course'
        ]);

        return response()->json($user);
    }

    /**
     * Update the authenticated user's profile.
     */
    public function updateProfile(Request $request)
    {
          /** @var \App\Models\User $user */
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->update($request->only(['name', 'email']));

        return response()->json($user);
    }

    /**
     * Change the authenticated user's password.
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Check current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 400);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'Password changed successfully']);
    }

    /**
     * Get courses created by the user (instructor).
     */
    public function getUserCourses(Request $request, User $user)
    {
        $query = $user->courses()->with('instructor');

        // Filter by published status
        if ($request->has('published')) {
            $query->where('is_published', $request->boolean('published'));
        }

        // Search by title
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%");
        }

        $courses = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json($courses);
    }

    /**
     * Get courses the user is enrolled in (student).
     */
    public function getUserEnrollments(Request $request, User $user)
    {
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
     * Get user's progress across all courses.
     */
    public function getUserProgress(Request $request, User $user)
    {
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
     * Get user's certificates.
     */
    public function getUserCertificates(Request $request, User $user)
    {
        $query = $user->certificates()->with('course');

        // Filter by course
        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // Search by course title
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('course', function ($courseQuery) use ($search) {
                $courseQuery->where('title', 'like', "%{$search}%");
            });
        }

        $certificates = $query->orderBy('issued_at', 'desc')->paginate(10);

        return response()->json($certificates);
    }

    /**
     * Get user statistics.
     */
    public function getUserStatistics(User $user)
    {
        $stats = [
            'total_courses_created' => $user->courses()->count(),
            'total_enrollments' => $user->enrollments()->count(),
            'total_certificates' => $user->certificates()->count(),
            'total_completed_lessons' => $user->progress()->where('completed', true)->count(),
            'courses_in_progress' => $user->enrollments()
                ->whereHas('course.modules.lessons', function ($query) {
                    $query->whereHas('progress', function ($progressQuery) {
                        $progressQuery->where('completed', false);
                    });
                })
                ->count(),
            'completed_courses' => $user->enrollments()
                ->whereDoesntHave('course.modules.lessons', function ($query) {
                    $query->whereDoesntHave('progress', function ($progressQuery) {
                        $progressQuery->where('completed', true);
                    });
                })
                ->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Get authenticated user's dashboard data.
     */
    public function dashboard()
    {
        $user = Auth::user();

        /** @var \App\Models\User $user */
        $dashboard = [
            'user' => $user,
            'stats' => [
                'courses_created' => $user->courses()->count(),
                'enrollments' => $user->enrollments()->count(),
                'certificates' => $user->certificates()->count(),
                'completed_lessons' => $user->progress()->where('completed', true)->count(),
            ],
            'recent_courses' => $user->enrolledCourses()->with('instructor')->limit(5)->get(),
            'recent_certificates' => $user->certificates()->with('course')->limit(5)->get(),
            'in_progress_courses' => $user->enrolledCourses()
                ->whereHas('modules.lessons', function ($query) {
                    $query->whereHas('progress', function ($progressQuery) {
                        $progressQuery->where('completed', false);
                    });
                })
                ->with('instructor')
                ->limit(5)
                ->get(),
        ];

        return response()->json($dashboard);
    }

    /**
     * Get authenticated user's created courses.
     */
    public function myCourses(Request $request)
    {

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = $user->courses()->with('instructor');

        // Filter by published status
        if ($request->has('published')) {
            $query->where('is_published', $request->boolean('published'));
        }

        // Search by title
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%");
        }

        $courses = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json($courses);
    }

    /**
     * Get authenticated user's enrollments.
     */
    public function myEnrollments(Request $request)
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
     * Get authenticated user's progress.
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
     * Get authenticated user's certificates.
     */
    public function myCertificates(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = $user->certificates()->with('course');

        // Filter by course
        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // Search by course title
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('course', function ($courseQuery) use ($search) {
                $courseQuery->where('title', 'like', "%{$search}%");
            });
        }

        $certificates = $query->orderBy('issued_at', 'desc')->paginate(10);

        return response()->json($certificates);
    }

    /**
     * Get authenticated user's statistics.
     */
    public function myStatistics()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $stats = [
            'courses_created' => $user->courses()->count(),
            'enrollments' => $user->enrollments()->count(),
            'certificates' => $user->certificates()->count(),
            'completed_lessons' => $user->progress()->where('completed', true)->count(),
            'courses_in_progress' => $user->enrollments()
                ->whereHas('course.modules.lessons', function ($query) {
                    $query->whereHas('progress', function ($progressQuery) {
                        $progressQuery->where('completed', false);
                    });
                })
                ->count(),
            'completed_courses' => $user->enrollments()
                ->whereDoesntHave('course.modules.lessons', function ($query) {
                    $query->whereDoesntHave('progress', function ($progressQuery) {
                        $progressQuery->where('completed', true);
                    });
                })
                ->count(),
        ];

        return response()->json($stats);
    }
}
