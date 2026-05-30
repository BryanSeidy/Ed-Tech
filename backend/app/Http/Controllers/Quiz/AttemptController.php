<?php

namespace App\Http\Controllers\Quiz;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\Quiz;
use App\Models\Progress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttemptController extends Controller
{
    private function resolveCourseFromQuiz(Quiz $quiz)
    {
        return $quiz->lesson->module->course;
    }

    private function resolveCourseFromAttempt(Attempt $attempt)
    {
        return $attempt->quiz->lesson->module->course;
    }

    /**
     * Display a listing of attempts for a quiz (instructor only). Affichage d'une liste des tentatives pour un quiz,
     *  accessible uniquement à l'instructeur du cours, avec une vérification de l'autorisation de l'utilisateur, une 
     * récupération des tentatives avec les informations de l'utilisateur qui a tenté le quiz, et une pagination des
     *  résultats pour une meilleure gestion des données.
     */
    public function index(Quiz $quiz)
    {
        $course = $this->resolveCourseFromQuiz($quiz);

        // Check if user is the instructor
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $attempts = $quiz->attempts()
            ->with('user:id,name,email')
            ->orderBy('attempted_at', 'desc')
            ->paginate(20);

        return response()->json($attempts);
    }

    /**
     * Display the specified attempt. Affiche d'une tentative specifique
     */
    public function show(Attempt $attempt)
    {
        $user = Auth::user();
        $course = $this->resolveCourseFromAttempt($attempt);

        // Check if user is the attempt owner or the course instructor verifier que c'est un instructeur qui est connecter
        if ($attempt->user_id !== $user->id && $course->instructor_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $attempt->load(['user:id,name,email', 'quiz:id,title,duration_minutes']);

        return response()->json($attempt);
    }

    /**
     * Start a new quiz attempt. Nouvelle tentative de quizz
     */
    public function store(Request $request, Quiz $quiz)
    {
        $user = Auth::user();
        $course = $this->resolveCourseFromQuiz($quiz);

        // Check if user is enrolled in the course
        if (!$course->enrollments()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Not enrolled in this course'], 403);
        }

        // Check if quiz has questions
        if ($quiz->questions()->count() === 0) {
            return response()->json(['message' => 'Quiz has no questions'], 400);
        }

        // Check if user has already attempted this quiz (optional: allow multiple attempts)
        // For now, allow multiple attempts

        $attempt = Attempt::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'score' => 0,
            'passed' => false,
            'attempted_at' => now(),
        ]);

        $attempt->load('quiz');

        return response()->json([
            'message' => 'Quiz attempt started',
            'attempt' => $attempt
        ], 201);
    }

    /**
     * Submit quiz attempt with answers and calculate score. soumettre
     */
    public function update(Request $request, Attempt $attempt)
    {
        $user = Auth::user();

        // Check if user owns this attempt
        if ($attempt->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($attempt->submitted_at !== null) {
            return response()->json(['message' => 'Attempt already submitted'], 400);
        }

        $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|integer|exists:questions,id',
            'answers.*.answer_id' => 'required|integer|exists:answers,id',
        ]);

        $quiz = $attempt->quiz()->with('questions.answers', 'lesson')->firstOrFail();
        $totalQuestions = $quiz->questions->count();
        $answeredQuestionIds = collect($request->answers)->pluck('question_id')->unique();

        if ($answeredQuestionIds->count() !== $totalQuestions) {
            return response()->json(['message' => 'All quiz questions must be answered'], 422);
        }

        $correctAnswers = 0;
        foreach ($request->answers as $answerData) {
            $question = $quiz->questions->firstWhere('id', (int) $answerData['question_id']);

            if (! $question) {
                return response()->json(['message' => 'Answer does not belong to this quiz'], 422);
            }

            $selectedAnswer = $question->answers->firstWhere('id', (int) $answerData['answer_id']);

            if (! $selectedAnswer) {
                return response()->json(['message' => 'Selected answer does not belong to this question'], 422);
            }

            if ($selectedAnswer->is_correct) {
                $correctAnswers++;
            }
        }

        $percentage = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 2) : 0.0;
        $passed = $percentage >= $quiz->passing_score;

        $attempt->update([
            'score' => $percentage,
            'passed' => $passed,
            'submitted_at' => now(),
        ]);

        if ($passed) {
            Progress::updateOrCreate(
                ['user_id' => $user->id, 'lesson_id' => $quiz->lesson_id],
                ['completed' => true, 'completed_at' => now()]
            );
        }

        return response()->json([
            'message' => $passed
                ? 'Quiz submitted successfully'
                : 'Quiz submitted but passing score was not reached',
            'attempt' => $attempt->refresh(),
            'results' => [
                'score' => $correctAnswers,
                'total_questions' => $totalQuestions,
                'percentage' => $percentage,
                'passing_score' => $quiz->passing_score,
                'passed' => $passed,
            ]
        ]);
    }

    /**
     * Remove the specified attempt.
     */
    public function destroy(Attempt $attempt)
    {
        $user = Auth::user();
        $course = $this->resolveCourseFromAttempt($attempt);

        // Check if user is the attempt owner or the course instructor
        if ($attempt->user_id !== $user->id && $course->instructor_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $attempt->delete();

        return response()->json(['message' => 'Attempt deleted successfully']);
    }

    /**
     * Get user's attempts for a specific quiz.
     */
    public function userAttempts(Quiz $quiz)
    {
        $user = Auth::user();
        $course = $this->resolveCourseFromQuiz($quiz);

        // Check if user is enrolled
        if (!$course->enrollments()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Not enrolled in this course'], 403);
        }

        $attempts = $quiz->attempts()
            ->where('user_id', $user->id)
            ->orderBy('attempted_at', 'desc')
            ->get();

        return response()->json($attempts);
    }

    /**
     * Get all attempts for the authenticated user.
     */
    public function myAttempts(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = $user->attempts()->with(['quiz.lesson.module.course:id,title']);

        // Filter by course
        if ($request->has('course_id')) {
            $query->whereHas('quiz', function ($q) use ($request) {
                $q->where('course_id', $request->course_id);
            });
        }

        $attempts = $query->orderBy('attempted_at', 'desc')->paginate(10);

        return response()->json($attempts);
    }

    /**
     * Get quiz attempt statistics (instructor only).
     */
    public function statistics(Quiz $quiz)
    {
        $course = $this->resolveCourseFromQuiz($quiz);

        // Check if user is the instructor
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $attempts = $quiz->attempts;

        if ($attempts->isEmpty()) {
            return response()->json([
                'total_attempts' => 0,
                'average_score' => 0,
                'highest_score' => 0,
                'lowest_score' => 0,
                'pass_rate' => 0,
            ]);
        }

        $scores = $attempts->pluck('score');
        $totalAttempts = $attempts->count();
        $averageScore = round($scores->avg(), 2);
        $highestScore = $scores->max();
        $lowestScore = $scores->min();
        $passRate = round(($scores->filter(fn($score) => $score >= 60)->count() / $totalAttempts) * 100, 2);

        $stats = [
            'total_attempts' => $totalAttempts,
            'average_score' => $averageScore,
            'highest_score' => $highestScore,
            'lowest_score' => $lowestScore,
            'pass_rate' => $passRate,
            'score_distribution' => $this->calculateScoreDistribution($scores),
        ];

        return response()->json($stats);
    }

    /**
     * Get detailed results for an attempt.
     */
    public function results(Attempt $attempt)
    {
        $user = Auth::user();
        $course = $this->resolveCourseFromAttempt($attempt);

        // Check if user is the attempt owner or the course instructor
        if ($attempt->user_id !== $user->id && $course->instructor_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // For now, return basic results since we don't have user answers stored
        // In a real implementation, you'd have a user_answers table
        $results = [
            'attempt' => $attempt,
            'quiz' => $attempt->quiz,
            'score' => $attempt->score,
            'total_questions' => $attempt->quiz->questions()->count(),
            'passed' => $attempt->score >= 60, // Assuming 60% is passing
        ];

        return response()->json($results);
    }

    /**
     * Calculate score distribution for statistics.
     */
    private function calculateScoreDistribution($scores)
    {
        $distribution = [
            '0-20' => 0,
            '21-40' => 0,
            '41-60' => 0,
            '61-80' => 0,
            '81-100' => 0,
        ];

        foreach ($scores as $score) {
            if ($score <= 20) $distribution['0-20']++;
            elseif ($score <= 40) $distribution['21-40']++;
            elseif ($score <= 60) $distribution['41-60']++;
            elseif ($score <= 80) $distribution['61-80']++;
            else $distribution['81-100']++;
        }

        return $distribution;
    }
}
