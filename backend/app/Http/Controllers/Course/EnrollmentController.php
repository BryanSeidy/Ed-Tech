<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Services\CourseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function __construct(private readonly CourseService $courseService)
    {
    }

    public function store(Request $request, int $courseId): JsonResponse
    {
        $enrollment = $this->courseService->enroll((int) $request->user()->id, $courseId);

        return response()->json(['data' => $enrollment], 201);
    }
}
