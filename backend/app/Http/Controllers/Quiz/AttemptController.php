<?php

namespace App\Http\Controllers\Quiz;

use App\Http\Controllers\Controller;
use App\Http\Requests\Quiz\SubmitAttemptRequest;
use App\Http\Resources\AttemptResource;
use App\Services\QuizService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttemptController extends Controller
{
    public function __construct(private readonly QuizService $quizService)
    {
    }

    public function store(SubmitAttemptRequest $request, int $quizId): JsonResponse
    {
        $attempt = $this->quizService->submitAttempt((int) $request->user()->id, $quizId, $request->validated('answers'));

        return response()->json(['data' => AttemptResource::make($attempt)], 201);
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json(['data' => AttemptResource::collection($this->quizService->listAttemptsForUser((int) $request->user()->id))]);
    }
}
