<?php

namespace App\Http\Controllers\Quiz;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuizResource;
use App\Services\QuizService;
use Illuminate\Http\JsonResponse;

class QuizController extends Controller
{
    public function __construct(private readonly QuizService $quizService)
    {
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['data' => QuizResource::make($this->quizService->findQuizWithQuestions($id))]);
    }
}
