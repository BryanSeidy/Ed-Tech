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
     * Display a listing of modules for a course. Affichage des cours avec leurs modules
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
     * Store a newly created module. Creation des nouveaux modules
     */
    public function store(Request $request, Module $course)
    {
        // Check if user is the instructor verifier que c'est l'instructeur du cours qui est connecter
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'course_id' => 'required|integer|exists:courses,id',
            'title' => 'nullable|string|max:255',
            'position' => 'required|integer|min:1',
        ]);

        // Check if position is unique within the course valider que la position est unique dans le cours
        if ($course->modules()->where('position', $request->position)->exists()) {
            return response()->json(['message' => 'Position already taken'], 400);
        }

        $module = $course->modules()->create($request->only([
            'course_id', 'title', 'position'
        ]));

        return response()->json($module, 201);
    }

    /**
     * Display the specified module. afficher les modules specifique
     */
    public function show(Module $module)
    {
        $course = $module->course;
        $user = Auth::user();

        // Check if user is enrolled or is the instructor verifier si l'utilisateur est inscrit ou est l'instructeur du module
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
     * Update the specified module. mise à jour des modules, accessible uniquement à l'instructeur du cours, avec la possibilité de mettre à jour le titre, la description et la position du module, et une validation pour s'assurer que la nouvelle position n'entre pas en conflit avec les autres modules du même cours
     */
    public function update(Request $request, Module $module)
    {
        $course = $module->course;

        // Check if user is the instructor verifier que c'est l'instructeur du cours qui est connecter
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'course_id' => 'sometimes|required|string|max:255',
            'title' => 'nullable|string',
            'position' => 'sometimes|required|integer|min:1',
        ]);

        // Check position uniqueness if updating position valider que la position est unique si elle est mise à jour
        if ($request->has('position') && $request->position !== $module->position) {
            if ($course->modules()->where('position', $request->position)->where('id', '!=', $module->id)->exists()) {
                return response()->json(['message' => 'Position already taken'], 400);
            }
        }

        $module->update($request->only([
            'course_id', 'title', 'position'
        ]));

        return response()->json($module);
    }

    /**
     * Remove the specified module. Suppression d'un module, accessible uniquement à l'instructeur du cours, avec la suppression en cascade de toutes les leçons associées au module, et une réponse indiquant que le module a été supprimé avec succès ou une erreur si l'utilisateur n'est pas autorisé à supprimer le module
     */
    public function destroy(Module $module)
    {
        $course = $module->course;

        // Check if user is the instructor verifier que c'est l'instructeur du cours qui est connecter
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $module->delete();

        return response()->json(['message' => 'Module deleted successfully']);
    }

    /**
     * Reorder modules in a course. Réorganiser les modules dans un cours, accessible uniquement à l'instructeur du cours, avec la possibilité de fournir une liste d'IDs de modules et leurs nouvelles positions, et une réponse indiquant que les modules ont été réorganisés avec succès ou des erreurs si les positions ne sont pas valides ou si certains modules ne font pas partie du cours
     */
    public function reorder(Request $request, Course $course)
    {
        // Check if user is the instructor verifier que c'est l'instructeur du cours qui est connecter
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'modules' => 'required|array',
            'modules.*.id' => 'required|integer|exists:modules,id',
            'modules.*.position' => 'required|integer|min:1',
        ]);

        // Validate that all modules belong to the course Valider que tous les modules appartiennent au cours
        $moduleIds = collect($request->modules)->pluck('id');
        $courseModuleIds = $course->modules()->pluck('id');

        if ($moduleIds->diff($courseModuleIds)->isNotEmpty()) {
            return response()->json(['message' => 'Some modules do not belong to this course'], 400);
        }

        // Update positions     Mettre à jour les positions
        foreach ($request->modules as $moduleData) {
            $course->modules()
                ->where('id', $moduleData['id'])
                ->update(['position' => $moduleData['position']]);
        }

        return response()->json(['message' => 'Modules reordered successfully']);
    }

    /**
     * Get module statistics for instructor. recuperer les statistiques d'un module pour l'instructeur, avec des données telles que le nombre total de leçons, la durée totale du module, le nombre de leçons complétées par les étudiants, et le taux de complétion moyen pour le module, accessible uniquement à l'instructeur du cours
     */
    public function statistics(Module $module)
    {
        $course = $module->course;

        // Check if user is the instructor verifier que c'est l'instructeur du cours qui est connecter
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
     * Get next module in the course. Obtenir le module suivant dans le cours, accessible uniquement aux utilisateurs inscrits dans le cours ou à l'instructeur du cours, avec une réponse contenant les détails du module suivant basée sur la position du module actuel, ou un message indiquant qu'il n'y a pas de module suivant disponible
     */
    public function nextModule(Module $module)
    {
        $course = $module->course;
        $user = Auth::user();

        // Check if user is enrolled or instructor Verifier si l'utilisateur est inscrit ou est l'instructeur du cours
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
     * Get previous module in the course. Obtenir le module précédent dans le cours, accessible uniquement aux utilisateurs inscrits dans le cours ou à l'instructeur du cours, avec une réponse contenant les détails du module précédent basée sur la position du module actuel, ou un message indiquant qu'il n'y a pas de module précédent disponible
     */
    public function previousModule(Module $module)
    {
        $course = $module->course;
        $user = Auth::user();

        // Check if user is enrolled or instructor Verifier si l'utilisateur est inscrit ou est l'instructeur du cours
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
     * Get module progress for authenticated user. Obtenir la progression du module pour l'utilisateur authentifié, avec des données telles que le nombre
     * total de leçons, le nombre de leçons complétées, le pourcentage de complétion, et si le module est considéré comme complété ou non, accessible uniquement aux utilisateurs inscrits dans le cours
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
     * Calculate average completion rate for a module. Calculer le taux de complétion moyen pour un module, en fonction du nombre total de leçons dans le module, du nombre de leçons complétées par tous les étudiants inscrits dans le cours, et du nombre total d'inscriptions au cours, avec une formule pour calculer le pourcentage de complétion moyen et une gestion des cas où il n'y a pas d'inscriptions ou de leçons pour éviter la division par zéro
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
