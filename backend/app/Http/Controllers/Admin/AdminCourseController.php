<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCourseStatusRequest;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminCourseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:160'],
            'status' => ['nullable', 'string', Rule::in(['pending', 'published', 'archived'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $courses = Course::query()
            ->with('instructor:id,name,email')
            ->withCount('modules')
            ->when($validated['search'] ?? null, function ($query, string $search): void {
                $query->where('title', 'like', "%{$search}%");
            })
            ->when($validated['status'] ?? null, function ($query, string $status): void {
                $query->where('publication_status', $status);
            })
            ->latest()
            ->paginate((int) ($validated['per_page'] ?? 10))
            ->through(fn (Course $course): array => $this->serializeCourse($course));

        return response()->json($courses);
    }

    public function updateStatus(UpdateCourseStatusRequest $request, Course $course): JsonResponse
    {
        $status = $request->validated('status');

        $course->update([
            'publication_status' => $status,
            'is_published' => $status === 'published',
        ]);

        return response()->json([
            'data' => $this->serializeCourse($course->refresh()->load('instructor:id,name,email')->loadCount('modules')),
        ]);
    }

    private function serializeCourse(Course $course): array
    {
        return [
            'id' => $course->id,
            'title' => $course->title,
            'status' => $course->publication_status,
            'is_published' => (bool) $course->is_published,
            'instructor' => $course->instructor ? [
                'id' => $course->instructor->id,
                'name' => $course->instructor->name,
                'email' => $course->instructor->email,
            ] : null,
            'modules_count' => (int) ($course->modules_count ?? 0),
            'created_at' => $course->created_at?->toIso8601String(),
        ];
    }
}
