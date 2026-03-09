<?php

namespace App\Services;

use App\Models\Attempt;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Collection;

class QuizService
{
    public function findQuizWithQuestions(int $quizId): Quiz
    {
        return Quiz::query()->with(['questions.answers'])->findOrFail($quizId);
    }

    public function submitAttempt(int $userId, int $quizId, array $answersByQuestion): Attempt
    {
        $quiz = $this->findQuizWithQuestions($quizId);

        $questions = $quiz->questions;
        $total = $questions->count();
        if ($total === 0) {
            $score = 0;
        } else {
            $correct = $questions->filter(function ($question) use ($answersByQuestion) {
                $selectedAnswerId = $answersByQuestion[$question->id] ?? null;

                return $question->answers->contains(fn ($answer) => $answer->id === $selectedAnswerId && $answer->is_correct);
            })->count();

            $score = (int) round(($correct / $total) * 100);
        }

        return Attempt::create([
            'user_id' => $userId,
            'quiz_id' => $quizId,
            'score' => $score,
        ]);
    }

    public function listAttemptsForUser(int $userId): Collection
    {
        return Attempt::query()
            ->where('user_id', $userId)
            ->with('quiz.lesson')
            ->latest('attempted_at')
            ->get();
    }
}
