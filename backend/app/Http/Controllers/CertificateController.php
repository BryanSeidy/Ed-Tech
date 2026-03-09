<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Progress;
use App\Models\Lesson;
use App\Services\CertificateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CertificateController extends Controller
{
    protected $certificateService;

    public function __construct(CertificateService $certificateService)
    {
        $this->certificateService = $certificateService;
    }

    /**
     * Display a listing of certificates.
     */
    public function index(Request $request)
    {
        $query = Certificate::with(['user', 'course']);

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by course
        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // Filter by date range
        if ($request->has('issued_from')) {
            $query->where('issued_at', '>=', $request->issued_from);
        }

        if ($request->has('issued_to')) {
            $query->where('issued_at', '<=', $request->issued_to);
        }

        // Search by course title or user name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('course', function ($courseQuery) use ($search) {
                    $courseQuery->where('title', 'like', "%{$search}%");
                })
                ->orWhereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }

        $certificates = $query->orderBy('issued_at', 'desc')->paginate(10);

        return response()->json($certificates);
    }

    /**
     * Store a newly created certificate.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'certificate_url' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if certificate already exists
        $existingCertificate = Certificate::where('user_id', $request->user_id)
            ->where('course_id', $request->course_id)
            ->first();

        if ($existingCertificate) {
            return response()->json(['message' => 'Certificate already exists for this user and course'], 400);
        }

        $certificate = Certificate::create([
            'user_id' => $request->user_id,
            'course_id' => $request->course_id,
            'certificate_url' => $request->certificate_url,
            'issued_at' => now(),
        ]);

        return response()->json($certificate->load(['user', 'course']), 201);
    }

    /**
     * Display the specified certificate.
     */
    public function show(Certificate $certificate)
    {
        $certificate->load(['user', 'course']);

        return response()->json($certificate);
    }

    /**
     * Update the specified certificate.
     */
    public function update(Request $request, Certificate $certificate)
    {
        $validator = Validator::make($request->all(), [
            'certificate_url' => 'sometimes|string|max:255',
            'issued_at' => 'sometimes|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $certificate->update($request->only(['certificate_url', 'issued_at']));

        return response()->json($certificate->load(['user', 'course']));
    }

    /**
     * Remove the specified certificate.
     */
    public function destroy(Certificate $certificate)
    {
        // Delete certificate file if it exists
        if ($certificate->certificate_url && Storage::disk('public')->exists($certificate->certificate_url)) {
            Storage::disk('public')->delete($certificate->certificate_url);
        }

        $certificate->delete();

        return response()->json(['message' => 'Certificate deleted successfully']);
    }

    /**
     * Generate a certificate for a user who completed a course.
     */
    public function generate(Request $request)
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
        $enrollment = Enrollment::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if (!$enrollment) {
            return response()->json(['message' => 'User is not enrolled in this course'], 400);
        }

        // Check if certificate already exists
        $existingCertificate = Certificate::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if ($existingCertificate) {
            return response()->json(['message' => 'Certificate already exists for this user and course'], 400);
        }

        // Check if course is completed (all lessons completed)
        $course = Course::with('modules.lessons')->find($courseId);
        $totalLessons = $course->modules->sum(function ($module) {
            return $module->lessons->count();
        });

        $completedLessons = Progress::where('user_id', $userId)
            ->whereHas('lesson', function ($query) use ($courseId) {
                $query->whereHas('module', function ($moduleQuery) use ($courseId) {
                    $moduleQuery->where('course_id', $courseId);
                });
            })
            ->where('completed', true)
            ->count();

        if ($completedLessons < $totalLessons) {
            return response()->json([
                'message' => 'Course not completed yet',
                'completed_lessons' => $completedLessons,
                'total_lessons' => $totalLessons
            ], 400);
        }

        // Generate certificate
        try {
            $certificate = $this->certificateService->generateCertificate($userId, $courseId);

            return response()->json([
                'message' => 'Certificate generated successfully',
                'certificate' => $certificate->load(['user', 'course'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to generate certificate', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Download certificate file.
     */
    public function download(Certificate $certificate)
    {
        // Check if user owns the certificate
        if ($certificate->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$certificate->certificate_url || !Storage::disk('public')->exists($certificate->certificate_url)) {
            return response()->json(['message' => 'Certificate file not found'], 404);
        }
        return response()->download(Storage::disk('public')->path($certificate->certificate_url));
    }

    /**
     * Verify certificate authenticity.
     */
    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'certificate_id' => 'required|exists:certificates,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $certificate = Certificate::with(['user', 'course'])->find($request->certificate_id);

        return response()->json([
            'valid' => true,
            'certificate' => [
                'id' => $certificate->id,
                'user_name' => $certificate->user->name,
                'course_title' => $certificate->course->title,
                'issued_at' => $certificate->issued_at,
                'certificate_url' => $certificate->certificate_url,
            ]
        ]);
    }

    /**
     * Get certificates for the authenticated user.
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
     * Check if a user can get a certificate for a course.
     */
    public function checkEligibility(Request $request)
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

        // Check if user is enrolled
        $enrollment = Enrollment::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if (!$enrollment) {
            return response()->json([
                'eligible' => false,
                'reason' => 'User is not enrolled in this course'
            ]);
        }

        // Check if certificate already exists
        $existingCertificate = Certificate::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if ($existingCertificate) {
            return response()->json([
                'eligible' => false,
                'reason' => 'Certificate already exists',
                'certificate' => $existingCertificate->load(['user', 'course'])
            ]);
        }

        // Check course completion
        $course = Course::with('modules.lessons')->find($courseId);
        $totalLessons = $course->modules->sum(function ($module) {
            return $module->lessons->count();
        });

        $completedLessons = Progress::where('user_id', $userId)
            ->whereHas('lesson', function ($query) use ($courseId) {
                $query->whereHas('module', function ($moduleQuery) use ($courseId) {
                    $moduleQuery->where('course_id', $courseId);
                });
            })
            ->where('completed', true)
            ->count();

        $eligible = $completedLessons >= $totalLessons;

        return response()->json([
            'eligible' => $eligible,
            'completed_lessons' => $completedLessons,
            'total_lessons' => $totalLessons,
            'reason' => $eligible ? null : 'Course not completed yet'
        ]);
    }
}
