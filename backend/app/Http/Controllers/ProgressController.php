<?php

namespace App\Http\Controllers;

use App\Http\Requests\Progress\MarkLessonCompletedRequest;
use App\Http\Resources\ProgressResource;
use App\Services\ProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function __construct(private readonly ProgressService $progressService)
    {
    }

    public function markLessonCompleted(MarkLessonCompletedRequest $request, int $lessonId): JsonResponse
    {
        $progress = $this->progressService->markCompleted((int) $request->user()->id, $lessonId);

        return response()->json(['data' => ProgressResource::make($progress)]);
    }

    public function showCourseProgress(Request $request, int $courseId): JsonResponse
    {
        return response()->json(['data' => $this->progressService->getCourseProgress((int) $request->user()->id, $courseId)]);
    }
}
