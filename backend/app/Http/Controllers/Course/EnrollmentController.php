<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

class EnrollmentController extends Controller
{
    /**
     * Display a listing of enrollments for a course (instructor only). Affichage de la liste des inscriptions pour un cours donné, accessible uniquement à l'instructeur qui a créé le cours, avec la possibilité de paginer les résultats et d'inclure les informations de l'utilisateur inscrit
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
     * Display the specified enrollment. Affichage des détails d'une inscription spécifique, accessible uniquement à l'utilisateur inscrit ou à l'instructeur du cours, avec les informations de l'utilisateur et du cours associés à l'inscription
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
     * Enroll a user in a course. Enregistrement d'un utilisateur dans un cours, avec vérification que le cours est publié et que l'utilisateur n'est pas déjà inscrit, et que l'utilisateur n'est pas l'instructeur du cours (les instructeurs ne doivent pas s'inscrire à leurs propres cours)
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
     * Remove the specified enrollment (unenroll). desinscription d'un utilisateur d'un cours, avec vérification que l'utilisateur est actuellement inscrit dans le cours avant de supprimer l'inscription, et que seul l'utilisateur inscrit ou l'instructeur du cours peut effectuer cette action
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
     * Get enrollments for the authenticated user. Récupération des cours auxquels l'utilisateur authentifié est inscrit, avec la possibilité de filtrer par statut de publication du cours et de rechercher par titre du cours, et de paginer les résultats
     */
    public function myEnrollments(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = $user->enrollments()->with(['course:id,title,description,thumbnail,instructor_id']);

        // Filter by course status filtrer par statut de publication du cours
        if ($request->has('published')) {
            $query->whereHas('course', function ($q) use ($request) {
                $q->where('is_published', $request->boolean('published'));
            });
        }

        // Search by course title rechercher par titre du cours
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
     * Get enrollment statistics for a course (instructor only). recupération des statistiques d'inscription pour un cours donné, accessible uniquement à l'instructeur qui a créé le cours, avec des données telles que le nombre total d'inscriptions, les inscriptions par mois, les inscriptions par année, et les détails des inscriptions récentes avec les informations de l'utilisateur inscrit
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
     * Check if user is enrolled in a course. Vérification si l'utilisateur authentifié est inscrit dans un cours donné, avec une réponse indiquant si l'utilisateur est inscrit ou non, et si inscrit, la date d'inscription et l'ID de l'inscription
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
     * Bulk enroll users in a course (instructor only). Inscription en masse d'utilisateurs à un cours donné, accessible uniquement à l'instructeur qui a créé le cours,
     * avec la possibilité de fournir une liste d'IDs d'utilisateurs à inscrire, et une réponse indiquant le nombre d'inscriptions réussies et les utilisateurs qui ont été inscrits ou qui étaient déjà inscrits
     */
    public function bulkEnroll(Request $request, Course $course)
    {
        // Check if user is the instructor vérification que l'utilisateur est l'instructeur du cours
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        $userIds = $request->user_ids;

        // Remove users who are already enrolled or are the instructor // Retirer les utilisateurs qui sont déjà inscrits ou qui sont l'instructeur du cours
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
     * Bulk unenroll users from a course (instructor only). Désinscription en masse d'utilisateurs d'un cours donné, accessible uniquement à l'instructeur qui a créé le cours, avec la possibilité de fournir une liste d'IDs d'utilisateurs à désinscrire, et une réponse indiquant le nombre de désinscriptions réussies et les utilisateurs qui ont été désinscrits ou qui n'étaient pas inscrits
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
     * Export enrollments data for a course (instructor only). Exportation des données d'inscription pour un cours donné, accessible uniquement à l'instructeur qui a créé le cours, avec une réponse contenant les détails des inscriptions, y compris les informations de l'utilisateur inscrit et la date d'inscription, formaté pour une exportation CSV ou Excel
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
