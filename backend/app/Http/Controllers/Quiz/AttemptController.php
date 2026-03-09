<?php

namespace App\Http\Controllers\Quiz;

use App\Http\Controllers\Controller;
use App\Services\QuizService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttemptController extends Controller
{
    public function __construct(private readonly QuizService $quizService)
    {
    }

    public function store(Request $request, int $quizId): JsonResponse
    {
        $validated = $request->validate([
            'answers' => ['required', 'array'],
        ]);

        $attempt = $this->quizService->submitAttempt((int) $request->user()->id, $quizId, $validated['answers']);

        return response()->json(['data' => $attempt], 201);
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->quizService->listAttemptsForUser((int) $request->user()->id)]);
    }
}
