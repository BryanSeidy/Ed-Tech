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
     * Display a listing of the courses. liste des cours disponibles pour les utilisateurs et les instructeurs
     */
    public function index(Request $request)
    {
        $query = Course::with('instructor');

        // Filter by published status filtrer les cours en fonction de leur statut de publication
        if ($request->has('published')) {
            $query->where('is_published', $request->boolean('published'));
        }

        // Filter by instructor
        if ($request->has('instructor_id')) {
            $query->where('instructor_id', $request->instructor_id);
        }

        // Search by title or description rechercher des cours par titre ou description
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
     * Store a newly created course. ajouter un nouveau cours par un instructeur
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
     * Display the specified course. afficher les détails d'un cours spécifique, y compris les modules, les leçons et les quiz associés
     */
    public function show(Course $course)
    {
        $course->load(['instructor', 'modules.lessons']);

        return response()->json($course);
    }

    /**
     * Update the specified course. mise à jour des détails d'un cours par l'instructeur qui l'a créé
     */
    public function update(Request $request, Course $course)
    {
        // Check if user is the instructor de l'instructeur qui a créé le cours est autorisé à le mettre à jour
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
            // Delete old thumbnail suppression de l'ancienne miniature si elle existe
            if ($course->thumbnail) {
                Storage::disk('public')->delete($course->thumbnail);
            }
            $data['thumbnail'] = $request->file('thumbnail')->store('courses', 'public');
        }

        $course->update($data);

        return response()->json($course->load('instructor'));
    }

    /**
     * Remove the specified course. suppression d'un cours par l'instructeur qui l'a créé, y compris la suppression de la miniature associée et des relations avec les modules, les leçons et les quiz
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
     * Enroll a user in a course. Enregistrement d'un utilisateur dans un cours, avec vérification que le cours est publié et que l'utilisateur n'est pas déjà inscrit
     */
    public function enroll(Course $course)
    {
        if (!$course->is_published) {
            return response()->json(['message' => 'Course is not published'], 400);
        }

        $user = Auth::user();

        // Check if already enrolled si l'utilisateur est déjà inscrit dans ce cours
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
     * Unenroll a user from a course. desinscription d'un utilisateur d'un cours, avec vérification que l'utilisateur est actuellement inscrit dans le cours avant de supprimer l'inscription
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
     * Get courses for the authenticated user. Récupération des cours auxquels l'utilisateur authentifié est inscrit, avec la possibilité de filtrer par statut de publication et de rechercher par titre du cours
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
     * Get courses created by the authenticated instructor. Récupération des cours créés par l'instructeur authentifié, avec la possibilité de filtrer par statut de publication
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
     * Publish a course. Publication d'un cours par l'instructeur qui l'a créé, avec vérification que seul l'instructeur peut publier le cours et que le cours est mis à jour avec le statut de publication approprié
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
     * Unpublish a course. dépublication d'un cours par l'instructeur qui l'a créé, avec vérification que seul l'instructeur peut dépublier le cours et que le cours est mis à jour avec le statut de publication approprié
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
     * Get course statistics for instructor. recupération des statistiques d'un cours pour l'instructeur qui l'a créé, y compris le nombre total d'inscriptions, de modules, de leçons et de quiz associés au cours, avec vérification que seul l'instructeur peut accéder à ces statistiques
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
