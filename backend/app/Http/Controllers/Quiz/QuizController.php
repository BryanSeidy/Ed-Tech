<?php

namespace App\Http\Controllers\Quiz;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Answer;
use App\Models\Attempt;
use App\Models\Lesson;
use App\Models\Course;
use App\Models\Progress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class QuizController extends Controller
{
    /**
     * Get all quizzes with optional filters
     * GET /quizzes
     */
    public function index(Request $request)
    {
        $query = Quiz::with(['lesson.module.course', 'questions.answers', 'attempts']);

        // Filter by lesson
        if ($request->filled('lesson_id')) {
            $query->where('lesson_id', $request->lesson_id);
        }

        // Filter by course (through lesson -> module)
        if ($request->filled('course_id')) {
            $query->whereHas('lesson.module', function ($q) {
                $q->where('course_id', request('course_id'));
            });
        }

        // Filter by published status
        if ($request->filled('published')) {
            $query->where('is_published', $request->published === 'true' ? 1 : 0);
        }

        // Search by title
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $quizzes = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $quizzes,
            'total' => $quizzes->total(),
            'per_page' => $quizzes->perPage(),
            'current_page' => $quizzes->currentPage(),
        ], 200);
    }

    /**
     * Create a new quiz
     * POST /quizzes
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lesson_id' => 'required|exists:lessons,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'passing_score' => 'required|integer|min:0|max:100',
            'duration_minutes' => 'nullable|integer|min:1',
            'is_published' => 'nullable|boolean',
            'allow_review' => 'nullable|boolean',
            'show_answers' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if user is instructor of the course
        $lesson = Lesson::with('module.course')->findOrFail($request->lesson_id);
        if ($lesson->module->course->instructor_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only course instructor can create quizzes',
            ], 403);
        }

        $quiz = Quiz::create([
            'lesson_id' => $request->lesson_id,
            'title' => $request->title,
            'description' => $request->description,
            'passing_score' => $request->passing_score,
            'duration_minutes' => $request->duration_minutes,
            'is_published' => $request->get('is_published', false),
            'allow_review' => $request->get('allow_review', true),
            'show_answers' => $request->get('show_answers', false),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Quiz created successfully',
            'data' => $quiz,
        ], 201);
    }

    /**
     * Get a specific quiz with questions
     * GET /quizzes/{id}
     */
    public function show($id)
    {
        $quiz = Quiz::with('lesson.module.course', 'questions.answers')->findOrFail($id);

        // Randomize questions if needed (for security)
        if ($quiz->questions->isNotEmpty()) {
            $questions = $quiz->questions->shuffle();
            $quiz->questions = $questions;
        }

        return response()->json([
            'success' => true,
            'data' => $quiz,
        ], 200);
    }

    /**
     * Update a quiz
     * PUT /quizzes/{id}
     */
    public function update(Request $request, $id)
    {
        $quiz = Quiz::findOrFail($id);

        // Authorization check
        if ($quiz->lesson->module->course->instructor_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'passing_score' => 'nullable|integer|min:0|max:100',
            'duration_minutes' => 'nullable|integer|min:1',
            'is_published' => 'nullable|boolean',
            'allow_review' => 'nullable|boolean',
            'show_answers' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $quiz->update($request->only([
            'title', 'description', 'passing_score',
            'duration_minutes', 'is_published', 'allow_review', 'show_answers'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Quiz updated successfully',
            'data' => $quiz,
        ], 200);
    }

    /**
     * Delete a quiz
     * DELETE /quizzes/{id}
     */
    public function destroy($id)
    {
        $quiz = Quiz::findOrFail($id);

        // Authorization check
        if ($quiz->lesson->module->course->instructor_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Check if quiz has attempts
        if ($quiz->attempts()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete quiz with existing attempts',
            ], 400);
        }

        $quiz->delete();

        return response()->json([
            'success' => true,
            'message' => 'Quiz deleted successfully',
        ], 200);
    }

    /**
     * Publish a quiz
     * POST /quizzes/{id}/publish
     */
    public function publish($id)
    {
        $quiz = Quiz::findOrFail($id);

        // Authorization check
        if ($quiz->lesson->module->course->instructor_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Check if quiz has questions
        if ($quiz->questions()->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot publish quiz without questions',
            ], 400);
        }

        $quiz->update(['is_published' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Quiz published successfully',
            'data' => $quiz,
        ], 200);
    }

    /**
     * Unpublish a quiz
     * POST /quizzes/{id}/unpublish
     */
    public function unpublish($id)
    {
        $quiz = Quiz::findOrFail($id);

        // Authorization check
        if ($quiz->lesson->module->course->instructor_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $quiz->update(['is_published' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Quiz unpublished successfully',
            'data' => $quiz,
        ], 200);
    }

    /**
     * Get quiz questions
     * GET /quizzes/{id}/questions
     */
    public function getQuestions($id)
    {
        $quiz = Quiz::with('questions.answers')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'quiz_id' => $quiz->id,
                'title' => $quiz->title,
                'questions_count' => $quiz->questions->count(),
                'questions' => $quiz->questions,
            ],
        ], 200);
    }

    /**
     * Get quiz statistics
     * GET /quizzes/{id}/statistics
     */
    public function statistics($id)
    {
        $quiz = Quiz::with('attempts')->findOrFail($id);

        // Authorization check - only instructor can view
        if ($quiz->lesson->module->course->instructor_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $attempts = $quiz->attempts;
        $totalAttempts = $attempts->count();
        $scores = $attempts->pluck('score')->toArray();

        $stats = [
            'total_attempts' => $totalAttempts,
            'average_score' => $totalAttempts > 0 ? round(array_sum($scores) / $totalAttempts, 2) : 0,
            'highest_score' => $totalAttempts > 0 ? max($scores) : 0,
            'lowest_score' => $totalAttempts > 0 ? min($scores) : 0,
            'passing_count' => $attempts->where('score', '>=', $quiz->passing_score)->count(),
            'failing_count' => $attempts->where('score', '<', $quiz->passing_score)->count(),
            'pass_rate' => $totalAttempts > 0 ? round(($attempts->where('score', '>=', $quiz->passing_score)->count() / $totalAttempts) * 100, 2) : 0,
            'score_distribution' => $this->getScoreDistribution($scores),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ], 200);
    }

    /**
     * Get score distribution
     */
    private function getScoreDistribution($scores)
    {
        return [
            '0-20' => count(array_filter($scores, fn($s) => $s >= 0 && $s < 20)),
            '20-40' => count(array_filter($scores, fn($s) => $s >= 20 && $s < 40)),
            '40-60' => count(array_filter($scores, fn($s) => $s >= 40 && $s < 60)),
            '60-80' => count(array_filter($scores, fn($s) => $s >= 60 && $s < 80)),
            '80-100' => count(array_filter($scores, fn($s) => $s >= 80 && $s <= 100)),
        ];
    }

    /**
     * Start a quiz attempt
     * POST /quizzes/{id}/start-attempt
     */
    public function startAttempt($id)
    {
        $quiz = Quiz::with('lesson.module.course')->findOrFail($id);
        $user = Auth::user();

        // Check if user is enrolled in the course
        $isEnrolled = $quiz->lesson->module->course->enrollments()
            ->where('user_id', $user->id)
            ->exists();

        if (!$isEnrolled && $quiz->lesson->module->course->instructor_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'User not enrolled in this course',
            ], 403);
        }

        // Check if quiz is published
        if (!$quiz->is_published) {
            return response()->json([
                'success' => false,
                'message' => 'Quiz is not published yet',
            ], 403);
        }

        // Create a new attempt
        $attempt = Attempt::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'score' => 0,
            'attempted_at' => now(),
        ]);

        // Get questions with shuffled answers
        $questions = $quiz->questions()->with('answers')->get();
        $questions->each(function ($question) {
            $question->answers->shuffle();
        });

        return response()->json([
            'success' => true,
            'message' => 'Attempt started',
            'data' => [
                'attempt_id' => $attempt->id,
                'quiz_id' => $quiz->id,
                'title' => $quiz->title,
                'duration_minutes' => $quiz->duration_minutes,
                'questions_count' => $questions->count(),
                'questions' => $questions,
            ],
        ], 201);
    }

    /**
     * Submit quiz answers
     * POST /quizzes/{id}/submit
     */
    public function submitAnswers(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'attempt_id' => 'required|exists:attempts,id',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.answer_id' => 'nullable|exists:answers,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $quiz = Quiz::findOrFail($id);
        $attempt = Attempt::findOrFail($request->attempt_id);
        $user = Auth::user();

        // Verify attempt belongs to user
        if ($attempt->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Calculate score
        $score = $this->calculateScore($quiz, $request->answers);

        // Update attempt with score
        $attempt->update([
            'score' => $score,
            'attempted_at' => now(),
        ]);

        // Mark lesson as completed if student passed
        if ($score >= $quiz->passing_score) {
            Progress::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'lesson_id' => $quiz->lesson_id,
                ],
                [
                    'completed' => true,
                    'completed_at' => now(),
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Quiz submitted successfully',
            'data' => [
                'attempt_id' => $attempt->id,
                'score' => $score,
                'passing_score' => $quiz->passing_score,
                'passed' => $score >= $quiz->passing_score,
                'percentage' => round(($score / $quiz->questions()->count()) * 100, 2),
            ],
        ], 200);
    }

    /**
     * Calculate quiz score
     */
    private function calculateScore($quiz, $answers)
    {
        $correctCount = 0;
        $totalCount = 0;

        foreach ($answers as $answer) {
            $question = Question::find($answer['question_id']);
            if (!$question) continue;

            $totalCount++;

            // Check if the submitted answer is correct
            if (isset($answer['answer_id'])) {
                $submittedAnswer = Answer::where('id', $answer['answer_id'])
                    ->where('question_id', $answer['question_id'])
                    ->where('is_correct', true)
                    ->exists();

                if ($submittedAnswer) {
                    $correctCount++;
                }
            }
        }

        return $totalCount > 0 ? round(($correctCount / $totalCount) * 100, 2) : 0;
    }

    /**
     * Get user's attempt results
     * GET /quizzes/{id}/results/{attemptId}
     */
    public function getResults($id, $attemptId)
    {
        $quiz = Quiz::findOrFail($id);
        $attempt = Attempt::with('user', 'quiz.questions.answers')->findOrFail($attemptId);
        $user = Auth::user();

        // Check authorization - user can view own results or instructor can view all
        if ($user->id !== $attempt->user_id && $quiz->lesson->module->course->instructor_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // If show_answers is false, don't return correct answers for students
        $questions = $quiz->questions;
        if (!$quiz->show_answers && $user->id === $attempt->user_id) {
            $questions->each(function ($question) {
                $question->answers->each(function ($answer) {
                    $answer->is_correct = null;
                });
            });
        }

        return response()->json([
            'success' => true,
            'data' => [
                'attempt_id' => $attempt->id,
                'user' => $attempt->user->only('id', 'name', 'email'),
                'quiz_title' => $quiz->title,
                'score' => $attempt->score,
                'passing_score' => $quiz->passing_score,
                'passed' => $attempt->score >= $quiz->passing_score,
                'attempted_at' => $attempt->attempted_at,
                'questions' => $questions,
            ],
        ], 200);
    }

    /**
     * Get my quizzes (instructor)
     * GET /my-quizzes
     */
    public function myQuizzes(Request $request)
    {
        $user = Auth::user();

        $query = Quiz::whereHas('lesson.module.course', function ($q) use ($user) {
            $q->where('instructor_id', $user->id);
        })->with('lesson.module.course', 'questions', 'attempts');

        // Filter by published status
        if ($request->filled('published')) {
            $query->where('is_published', $request->published === 'true' ? 1 : 0);
        }

        $quizzes = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $quizzes,
        ], 200);
    }

    /**
     * Get enrolled quizzes (student)
     * GET /enrolled-quizzes
     */
    public function enrolledQuizzes(Request $request)
    {
        $user = Auth::user();

        $query = Quiz::whereHas('lesson.module.course.enrollments', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->where('is_published', true)
          ->with('lesson.module.course', 'questions', 'attempts');

        $quizzes = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $quizzes,
        ], 200);
    }

    /**
     * Reorder questions in a quiz
     * POST /quizzes/{id}/reorder-questions
     */
    public function reorderQuestions(Request $request, $id)
    {
        $quiz = Quiz::findOrFail($id);

        // Authorization check
        if ($quiz->lesson->module->course->instructor_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'question_ids' => 'required|array',
            'question_ids.*' => 'required|exists:questions,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Update positions
        foreach ($request->question_ids as $position => $questionId) {
            Question::where('id', $questionId)
                ->where('quiz_id', $quiz->id)
                ->update(['position' => $position + 1]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Questions reordered successfully',
        ], 200);
    }

    /**
     * Duplicate a quiz
     * POST /quizzes/{id}/duplicate
     */
    public function duplicate($id)
    {
        $originalQuiz = Quiz::with('questions.answers')->findOrFail($id);

        // Authorization check
        if ($originalQuiz->lesson->module->course->instructor_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Create new quiz
        $newQuiz = $originalQuiz->replicate();
        $newQuiz->title = $originalQuiz->title . ' (Copy)';
        $newQuiz->is_published = false;
        $newQuiz->save();

        // Copy questions and answers
        foreach ($originalQuiz->questions as $question) {
            $newQuestion = $question->replicate();
            $newQuestion->quiz_id = $newQuiz->id;
            $newQuestion->save();

            foreach ($question->answers as $answer) {
                $newAnswer = $answer->replicate();
                $newAnswer->question_id = $newQuestion->id;
                $newAnswer->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Quiz duplicated successfully',
            'data' => $newQuiz,
        ], 201);
    }

    /**
     * Get user's attempts on a quiz
     * GET /quizzes/{id}/my-attempts
     */
    public function myAttempts($id)
    {
        $quiz = Quiz::findOrFail($id);
        $user = Auth::user();

        $attempts = $quiz->attempts()
            ->where('user_id', $user->id)
            ->orderBy('attempted_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'quiz_id' => $quiz->id,
                'quiz_title' => $quiz->title,
                'attempts_count' => $attempts->count(),
                'best_score' => $attempts->max('score') ?? 0,
                'latest_score' => $attempts->first()->score ?? 0,
                'attempts' => $attempts,
            ],
        ], 200);
    }

    /**
     * Get all attempts for a quiz (instructor only)
     * GET /quizzes/{id}/attempts
     */
    public function getAllAttempts($id)
    {
        $quiz = Quiz::findOrFail($id);

        // Authorization check
        if ($quiz->lesson->module->course->instructor_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $attempts = $quiz->attempts()
            ->with('user')
            ->orderBy('attempted_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $attempts,
        ], 200);
    }
}
