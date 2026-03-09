<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Http\Requests\Course\ListModulesRequest;
use App\Http\Resources\ModuleResource;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Http\JsonResponse;

class ModuleController extends Controller
{
    public function index(ListModulesRequest $request, int $courseId): JsonResponse
    {
        Course::query()->findOrFail($courseId);

        $validated = $request->validated();
        $perPage = (int) ($validated['per_page'] ?? 15);
        $sort = $validated['sort'] ?? 'position';
        $direction = $validated['direction'] ?? 'asc';

        $modules = Module::query()
            ->where('course_id', $courseId)
            ->with(['lessons.quiz'])
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'data' => ModuleResource::collection($modules->items()),
            'meta' => [
                'current_page' => $modules->currentPage(),
                'per_page' => $modules->perPage(),
                'total' => $modules->total(),
                'last_page' => $modules->lastPage(),
            ],
        ]);
    }
}
