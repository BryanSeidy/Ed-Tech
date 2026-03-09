<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Http\Requests\Course\EnrollRequest;
use App\Http\Resources\EnrollmentResource;
use App\Services\CourseService;
use Illuminate\Http\JsonResponse;

class EnrollmentController extends Controller
{
    public function __construct(private readonly CourseService $courseService)
    {
    }

    public function store(EnrollRequest $request, int $courseId): JsonResponse
    {
        $enrollment = $this->courseService->enroll((int) $request->user()->id, $courseId);

        return response()->json(['data' => EnrollmentResource::make($enrollment)], 201);
    }
}
