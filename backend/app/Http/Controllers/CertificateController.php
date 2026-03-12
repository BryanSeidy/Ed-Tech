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
     * Display a listing of certificates. lieste de certificats
     */
    public function index(Request $request)
    {
        $query = Certificate::with(['user', 'course']);

        // Filter by user filtre par utilisateur
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by course filtre par cours
        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // Filter by date range filtre par plage de dates d'émission    
        if ($request->has('issued_from')) {
            $query->where('issued_at', '>=', $request->issued_from);
        }

        if ($request->has('issued_to')) {
            $query->where('issued_at', '<=', $request->issued_to);
        }

        // Search by course title or user name recherche par titre de cours ou nom d'utilisateur
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
     * Store a newly created certificate. Ajout d'un nouveau certificat, avec validation des données d'entrée, vérification de l'existence d'un certificat pour le même utilisateur et cours, création du certificat dans la base de données, et une réponse indiquant que le certificat a été créé avec succès ou les erreurs de validation
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

        // Check if certificate already exists vérifier si un certificat existe déjà pour le même utilisateur et cours
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
     * Display the specified certificate. Affichage d'un certificat spécifique, avec le chargement des relations utilisateur 
     * et cours, et une réponse indiquant les détails du certificat ou une erreur si le certificat n'est pas trouvé   
     */
    public function show(Certificate $certificate)
    {
        $certificate->load(['user', 'course']);

        return response()->json($certificate);
    }

    /**
     * Update the specified certificate. mise a jour des donnees d'un certificat
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
     * Remove the specified certificate. supression d'un certificat
     */
    public function destroy(Certificate $certificate)
    {
        // Delete certificate file if it exists suppression du fichier de certificat s'il existe
        if ($certificate->certificate_url && Storage::disk('public')->exists($certificate->certificate_url)) {
            Storage::disk('public')->delete($certificate->certificate_url);
        }

        $certificate->delete();

        return response()->json(['message' => 'Certificate deleted successfully']);
    }

    /**
     * Generate a certificate for a user who completed a course. generation d'un certificat a l'utilisateur ayant terminer le cours
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

        // Check if user is enrolled in the course verifier si l'utilisateur c'est inscrit au cours
        $enrollment = Enrollment::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if (!$enrollment) {
            return response()->json(['message' => 'User is not enrolled in this course'], 400);
        }

        // Check if certificate already exists verifier si le certificat existe deja
        $existingCertificate = Certificate::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if ($existingCertificate) {
            return response()->json(['message' => 'Certificate already exists for this user and course'], 400);
        }

        // Check if course is completed (all lessons completed) verifier si un cours est terminé (toutes les leçons complétées)
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

        // Generate certificate // generer les certificat
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
     * Download certificate file. Telecharger les certificats
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
     * Verify certificate authenticity. verifier si un certificat est est authentique
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
     * Get certificates for the authenticated user. Recuperer le certificat de l'utilisateur qui est connecter
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
     * Check if a user can get a certificate for a course. verifier si un utilisateur est éligible pour obtenir un certificat pour un cours, en vérifiant son inscription au cours, l'existence d'un certificat pour le même utilisateur et cours, et la complétion du cours (toutes les leçons complétées), et en retournant une réponse indiquant si l'utilisateur est éligible ou non, avec les raisons de l'inéligibilité le cas échéant
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

        // Check if user is enrolled verifier si l'utilisateur c'est inscrit au cours   
        $enrollment = Enrollment::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if (!$enrollment) {
            return response()->json([
                'eligible' => false,
                'reason' => 'User is not enrolled in this course'
            ]);
        }

        // Check if certificate already exists verifier si le certificat existe deja
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

        // Check course completion verifier si le cours est terminer
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
