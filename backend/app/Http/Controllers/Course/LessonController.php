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
     * Display a listing of lessons for a module. Affichage de la liste des leçons d'un module donné, accessible uniquement aux utilisateurs inscrits dans le cours ou à l'instructeur du cours, avec des détails sur chaque leçon, y compris le titre, la position, la durée, et le statut de progression de l'utilisateur pour chaque leçon (complétée ou non)
     */
    public function index(Request $request, Module $module)
    {
        // Check if user is enrolled in the course or is the instructor. Verification que l'utilisateur est inscrit dans le cours ou est l'instructeur du cours
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
     * Store a newly created lesson. Ajouter une nouvelle lecon
     */
    public function store(Request $request, Module $module)
    {
        $course = $module->course;

        // Check if user is the instructor Verifier que c'est l'instructeur du cour qui est connecter
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

        // Check if position is unique within the module // Verifier que la position est unique dans le module
        if ($module->lessons()->where('position', $request->position)->exists()) {
            return response()->json(['message' => 'Position already taken'], 400);
        }

        $lesson = $module->lessons()->create($request->only([
            'title', 'content', 'video_url', 'duration', 'position'
        ]));

        return response()->json($lesson, 201);
    }

    /**
     * Display the specified lesson. Afficher les détails d'une leçon spécifique, accessible uniquement aux utilisateurs inscrits dans le cours ou à l'instructeur du cours, avec des informations telles que le titre de la leçon, le contenu, l'URL de la vidéo, la durée, et le statut de progression de l'utilisateur pour cette leçon (complétée ou non)
     */
    public function show(Lesson $lesson)
    {
        $course = $lesson->course;
        $user = Auth::user();

        // Check if user is enrolled or is the instructor // Verifier que l'utilisateur est inscrit dans le cours ou est l'instructeur du cours
        if ($course->instructor_id !== $user->id && !$course->enrollments()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $lesson->load(['module', 'progress' => function ($query) use ($user) {
            $query->where('user_id', $user->id);
        }]);

        return response()->json($lesson);
    }

    /**
     * Update the specified lesson. Mise a jour d' une lecon specifique
     */
    public function update(Request $request, Lesson $lesson)
    {
        $course = $lesson->course;

        // Check if user is the instructor // verifier que c'est l'instructeur de la lecon qui est connecter
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

        // Check position uniqueness if updating position // Verifier que la position est unique si elle est mise a jour
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
     * Remove the specified lesson. Supprimer une lecon specifique, accessible uniquement à l'instructeur du cours, avec la suppression de toutes les progressions associées à cette leçon pour les utilisateurs inscrits
     */
    public function destroy(Lesson $lesson)
    {
        $course = $lesson->course;

        // Check if user is the instructor // verifier que c'est l'instructeur de la lecon qui est connecter
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $lesson->delete();

        return response()->json(['message' => 'Lesson deleted successfully']);
    }

    /**
     * Mark lesson as completed for the authenticated user. Marquer une leçon comme complétée pour l'utilisateur authentifié, accessible uniquement aux utilisateurs inscrits dans le cours, avec la création ou la mise à jour d'une progression pour cette leçon indiquant qu'elle est complétée, et la date de complétion
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
     * Mark lesson as not completed for the authenticated user. Marquer une leçon comme non complétée pour l'utilisateur authentifié, accessible uniquement aux utilisateurs inscrits dans le cours, avec la création ou la mise à jour d'une progression pour cette leçon indiquant qu'elle n'est pas complétée, et la suppression de la date de complétion
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
     * Get user's progress for a specific lesson. Obtenir la progression de l'utilisateur pour une leçon spécifique, accessible uniquement aux utilisateurs inscrits dans le cours, avec une réponse indiquant si la leçon est complétée ou non, et la date de complétion si elle est complétée
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
     * Get next lesson in the module. Obtenir la leçon suivante dans le module, accessible uniquement aux utilisateurs inscrits dans le cours ou à l'instructeur du cours, avec une réponse contenant les détails de la leçon suivante basée sur la position de la leçon actuelle, ou un message indiquant qu'il n'y a pas de leçon suivante disponible
     */
    public function nextLesson(Lesson $lesson)
    {
        $course = $lesson->course;
        $user = Auth::user();

        // Check if user is enrolled or instructor Verifier si l'utilisateur est inscrit ou est l'instructeur du cours
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
     * Get previous lesson in the module. Obtenir la leçon précédente dans le module, accessible uniquement aux utilisateurs inscrits dans le cours ou à l'instructeur du cours, avec une réponse contenant les détails de la leçon précédente basée sur la position de la leçon actuelle, ou un message indiquant qu'il n'y a pas de leçon précédente disponible
     */
    public function previousLesson(Lesson $lesson)
    {
        $course = $lesson->course;
        $user = Auth::user();

        // Check if user is enrolled or instructor verifier si l'utilisateur est inscrit ou est l'instructeur du cours
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
     * Reorder lessons in a module. Réorganiser les leçons dans un module, accessible uniquement à l'instructeur du cours, avec la possibilité de fournir une liste d'IDs de leçons et leurs nouvelles positions, et une réponse indiquant que les leçons ont été réorganisées avec succès ou des erreurs si les positions ne sont pas valides ou si certaines leçons ne font pas partie du module
     */
    public function reorder(Request $request, Module $module)
    {
        $course = $module->course;

        // Check if user is the instructor verifier que c'est l'instructeur du cours qui est connecter
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'lessons' => 'required|array',
            'lessons.*.id' => 'required|integer|exists:lessons,id',
            'lessons.*.position' => 'required|integer|min:1',
        ]);

        // Validate that all lessons belong to the module // Valider que toutes les leçons appartiennent au module
        $lessonIds = collect($request->lessons)->pluck('id');
        $moduleLessonIds = $module->lessons()->pluck('id');

        if ($lessonIds->diff($moduleLessonIds)->isNotEmpty()) {
            return response()->json(['message' => 'Some lessons do not belong to this module'], 400);
        }

        // Update positions // Mettre à jour les positions
        foreach ($request->lessons as $lessonData) {
            $module->lessons()
                ->where('id', $lessonData['id'])
                ->update(['position' => $lessonData['position']]);
        }

        return response()->json(['message' => 'Lessons reordered successfully']);
    }
}
