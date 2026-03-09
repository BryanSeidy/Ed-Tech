<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Services\CourseService;
use Illuminate\Http\JsonResponse;

class CourseController extends Controller
{
    public function __construct(private readonly CourseService $courseService)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json(['data' => CourseResource::collection($this->courseService->listPublished())]);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['data' => CourseResource::make($this->courseService->findById($id))]);
    }
}
