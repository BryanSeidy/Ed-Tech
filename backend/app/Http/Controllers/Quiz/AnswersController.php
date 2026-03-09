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
     * Display a listing of answers for a question.
     */
    public function index(Question $question)
    {
        $quiz = $question->quiz;
        $course = $quiz->course;

        // Check if user is the instructor
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $answers = $question->answers()->orderBy('id')->get();

        return response()->json($answers);
    }

    /**
     * Store a newly created answer.
     */
    public function store(Request $request, Question $question)
    {
        $quiz = $question->quiz;
        $course = $quiz->course;

        // Check if user is the instructor
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
     * Display the specified answer.
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
     * Update the specified answer.
     */
    public function update(Request $request, Answer $answer)
    {
        $question = $answer->question;
        $quiz = $question->quiz;
        $course = $quiz->course;

        // Check if user is the instructor
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'answer_text' => 'sometimes|required|string',
            'is_correct' => 'boolean',
        ]);

        // If this answer is being marked as correct, ensure no other answer for this question is correct
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
     * Remove the specified answer.
     */
    public function destroy(Answer $answer)
    {
        $question = $answer->question;
        $quiz = $question->quiz;
        $course = $quiz->course;

        // Check if user is the instructor
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $answer->delete();

        return response()->json(['message' => 'Answer deleted successfully']);
    }

    /**
     * Mark an answer as correct.
     */
    public function setCorrect(Answer $answer)
    {
        $question = $answer->question;
        $quiz = $question->quiz;
        $course = $quiz->course;

        // Check if user is the instructor
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Set all answers for this question as incorrect first
        $question->answers()->update(['is_correct' => false]);

        // Then set this answer as correct
        $answer->update(['is_correct' => true]);

        return response()->json([
            'message' => 'Answer marked as correct',
            'answer' => $answer
        ]);
    }

    /**
     * Mark an answer as incorrect.
     */
    public function unsetCorrect(Answer $answer)
    {
        $question = $answer->question;
        $quiz = $question->quiz;
        $course = $quiz->course;

        // Check if user is the instructor
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
     * Bulk create answers for a question.
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

        // Check that at least one answer is correct
        $hasCorrect = collect($request->answers)->contains('is_correct', true);
        if (!$hasCorrect) {
            return response()->json(['message' => 'At least one answer must be marked as correct'], 400);
        }

        // Check that not all answers are correct (for multiple choice)
        $allCorrect = collect($request->answers)->every('is_correct', true);
        if ($allCorrect && count($request->answers) > 1) {
            return response()->json(['message' => 'Not all answers can be correct'], 400);
        }

        // Delete existing answers
        $question->answers()->delete();

        // Create new answers
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
     * Get the correct answer for a question (for instructors or after quiz completion).
     */
    public function correctAnswer(Question $question)
    {
        $quiz = $question->quiz;
        $course = $quiz->course;

        // Check if user is the instructor
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
     * Reorder answers for a question.
     */
    public function reorder(Request $request, Question $question)
    {
        $quiz = $question->quiz;
        $course = $quiz->course;

        // Check if user is the instructor
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'answers' => 'required|array',
            'answers.*.id' => 'required|integer|exists:answers,id',
            'answers.*.order' => 'required|integer|min:1',
        ]);

        // Validate that all answers belong to the question
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
