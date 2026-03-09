<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Http\Requests\Course\ListLessonsRequest;
use App\Http\Resources\LessonResource;
use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Http\JsonResponse;

class LessonController extends Controller
{
    public function index(ListLessonsRequest $request, int $moduleId): JsonResponse
    {
        Module::query()->findOrFail($moduleId);

        $validated = $request->validated();
        $perPage = (int) ($validated['per_page'] ?? 15);
        $sort = $validated['sort'] ?? 'position';
        $direction = $validated['direction'] ?? 'asc';

        $lessons = Lesson::query()
            ->where('module_id', $moduleId)
            ->with('quiz')
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'data' => LessonResource::collection($lessons->items()),
            'meta' => [
                'current_page' => $lessons->currentPage(),
                'per_page' => $lessons->perPage(),
                'total' => $lessons->total(),
                'last_page' => $lessons->lastPage(),
            ],
        ]);
    }

    public function show(int $lessonId): JsonResponse
    {
        $lesson = Lesson::query()->with(['quiz.questions.answers'])->findOrFail($lessonId);

        return response()->json(['data' => LessonResource::make($lesson)]);
    }
}
