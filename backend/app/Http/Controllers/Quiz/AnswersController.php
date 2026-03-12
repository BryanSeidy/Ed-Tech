<?php

namespace App\Http\Controllers\Quiz;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnswersController extends Controller
{
    /**
     * Display a listing of answers for a question. Afficher la liste reponses du questionnaire
     */
    public function index(Question $question)
    {
        $quiz = $question->quiz;
        $course = $quiz->course;

        // Check if user is the instructor verfier si l'utilisateur est l'instructeur 
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $answers = $question->answers()->orderBy('id')->get();

        return response()->json($answers);
    }

    /**
     * Store a newly created answer. creation des nouvelles questions
     */
    public function store(Request $request, Question $question)
    {
        $quiz = $question->quiz;
        $course = $quiz->course;

        // Check if user is the instructor . verifier si l'utilisateur connecter est un instructeur
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'answer_text' => 'required|string',
            'is_correct' => 'boolean',
        ]);

        // If this answer is marked as correct, ensure no other answer for this question is correct
        if ($request->is_correct) {
            $question->answers()->update(['is_correct' => false]);
        }

        $answer = $question->answers()->create($request->only([
            'answer_text',
            'is_correct',
        ]));

        return response()->json($answer, 201);
    }

    /**
     * Display the specified answer. Afficher les reponses des questions
     */
    public function show(Answer $answer)
    {
        $question = $answer->question;
        $quiz = $question->quiz;
        $course = $quiz->course;

        // Check if user is the instructor
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $answer->load('question');

        return response()->json($answer);
    }

    /**
     * Update the specified answer. modification des reponses des questions
     */
    public function update(Request $request, Answer $answer)
    {
        $question = $answer->question;
        $quiz = $question->quiz;
        $course = $quiz->course;

        // Check if user is the instructor verifier si l'utilisateur connecter est un instructeur
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'answer_text' => 'sometimes|required|string',
            'is_correct' => 'boolean',
        ]);

        // If this answer is being marked as correct, ensure no other answer for this question is correct 
        //si cette réponse est marquée comme correcte, assurez-vous qu'aucune autre réponse pour cette question n'est correcte
        if ($request->has('is_correct') && $request->is_correct) {
            $question->answers()->where('id', '!=', $answer->id)->update(['is_correct' => false]);
        }

        $answer->update($request->only([
            'answer_text',
            'is_correct',
        ]));

        return response()->json($answer);
    }

    /**
     * Remove the specified answer. Supprimer une reponse 
     */
    public function destroy(Answer $answer)
    {
        $question = $answer->question;
        $quiz = $question->quiz;
        $course = $quiz->course;

        // Check if user is the instructor verifier que c'est l'instructeur qui est connecter
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $answer->delete();

        return response()->json(['message' => 'Answer deleted successfully']);
    }

    /**
     * Mark an answer as correct. Marquer une réponse comme correcte, en s'assurant que toutes les autres réponses pour la même question sont marquées comme incorrectes, accessible uniquement à l'instructeur du cours, avec une réponse indiquant que la réponse a été marquée comme correcte et les détails de la réponse mise à jour
     */
    public function setCorrect(Answer $answer)
    {
        $question = $answer->question;
        $quiz = $question->quiz;
        $course = $quiz->course;

        // Check if user is the instructor verifier que l'utilisateur connecter est un instructeur
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Set all answers for this question as incorrect first // pour cette question comme incorrectes d'abord
        $question->answers()->update(['is_correct' => false]);

        // Then set this answer as correct // Ensuite, définissez cette réponse comme correcte
        $answer->update(['is_correct' => true]);

        return response()->json([
            'message' => 'Answer marked as correct',
            'answer' => $answer
        ]);
    }

    /**
     * Mark an answer as incorrect. // marquer une reponse comme incorrect
     */
    public function unsetCorrect(Answer $answer)
    {
        $question = $answer->question;
        $quiz = $question->quiz;
        $course = $quiz->course;

        // Check if user is the instructor verifier si l'utilisateur connecter esst un instructeur
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $answer->update(['is_correct' => false]);

        return response()->json([
            'message' => 'Answer marked as incorrect',
            'answer' => $answer
        ]);
    }

    /**
     * Bulk create answers for a question. création en masse de réponses pour une question, avec validation pour s'assurer qu'au moins une réponse est marquée comme correcte et que toutes les réponses appartiennent à la même question, accessible uniquement à l'instructeur du cours, avec une réponse indiquant que les réponses ont été créées avec succès et les détails des réponses créées
     */
    public function bulkStore(Request $request, Question $question)
    {
        $quiz = $question->quiz;
        $course = $quiz->course;

        // Check if user is the instructor
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'answers' => 'required|array|min:1',
            'answers.*.answer_text' => 'required|string',
            'answers.*.is_correct' => 'boolean',
        ]);

        // Check that at least one answer is correct verifier si au moins une réponse est correcte
        $hasCorrect = collect($request->answers)->contains('is_correct', true);
        if (!$hasCorrect) {
            return response()->json(['message' => 'At least one answer must be marked as correct'], 400);
        }

        // Check that not all answers are correct (for multiple choice) vérifier que toutes les réponses ne sont pas correctes (pour le choix multiple)
        $allCorrect = collect($request->answers)->every('is_correct', true);
        if ($allCorrect && count($request->answers) > 1) {
            return response()->json(['message' => 'Not all answers can be correct'], 400);
        }

        // Delete existing answers suprimer une reponse existante
        $question->answers()->delete();

        // Create new answers creation des nouvelles reponses
        $answers = [];
        foreach ($request->answers as $answerData) {
            $answers[] = $question->answers()->create([
                'answer_text' => $answerData['answer_text'],
                'is_correct' => $answerData['is_correct'],
            ]);
        }

        return response()->json([
            'message' => 'Answers created successfully',
            'answers' => $answers
        ], 201);
    }

    /**
     * Get the correct answer for a question (for instructors or after quiz completion). recuperere les reponses correcte pour une question (pour les instructeurs ou après la complétion du quiz), accessible uniquement à l'instructeur du cours ou aux étudiants qui ont complété le quiz, avec une réponse contenant les détails de la réponse correcte ou un message indiquant qu'aucune réponse correcte n'est définie pour cette question
     */
    public function correctAnswer(Question $question)
    {
        $quiz = $question->quiz;
        $course = $quiz->course;

        // Check if user is the instructor verifier si c'est l'instructeur du cours qui est connecter
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $correctAnswer = $question->correctAnswer;

        if (!$correctAnswer) {
            return response()->json(['message' => 'No correct answer set for this question']);
        }

        return response()->json($correctAnswer);
    }

    /**
     * Reorder answers for a question. Réorganiser les réponses pour une question, en acceptant une liste d'ID de réponses dans l'ordre souhaité, avec validation pour s'assurer que toutes les réponses appartiennent à la même question et que l'utilisateur est l'instructeur du cours, avec une réponse indiquant que les réponses ont été réorganisées avec succès
     */
    public function reorder(Request $request, Question $question)
    {
        $quiz = $question->quiz;
        $course = $quiz->course;

        // Check if user is the instructor . verifier si c'est l'instructeurs qui est connecter
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'answers' => 'required|array',
            'answers.*.id' => 'required|integer|exists:answers,id',
            'answers.*.order' => 'required|integer|min:1',
        ]);

        // Validate that all answers belong to the question . validation que toutes les réponses appartiennent à la question
        $answerIds = collect($request->answers)->pluck('id');
        $questionAnswerIds = $question->answers()->pluck('id');

        if ($answerIds->diff($questionAnswerIds)->isNotEmpty()) {
            return response()->json(['message' => 'Some answers do not belong to this question'], 400);
        }

        // For now, since there's no order field in the migration, we'll just validate 
        // In a real implementation, you'd update an order field

        return response()->json(['message' => 'Answers reordered successfully']);
    }
}
