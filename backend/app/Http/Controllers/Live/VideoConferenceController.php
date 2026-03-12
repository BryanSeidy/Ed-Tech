<?php

namespace App\Http\Controllers\Live;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Services\VideoConferenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class VideoConferenceController extends Controller
{
    public function __construct(private readonly VideoConferenceService $videoConferenceService)
    {
    }

    public function createRoom(Request $request, int $lessonId): JsonResponse
    {
        $lesson = Lesson::query()->with('module.course')->findOrFail($lessonId);
        $userId = (int) $request->user()->id;

        $course = $lesson->module->course;
        $isEnrolled = Enrollment::query()
            ->where('user_id', $userId)
            ->where('course_id', $course->id)
            ->exists();

        if (! $isEnrolled && $course->instructor_id !== $userId) {
            throw new AccessDeniedHttpException('Accès refusé à la classe virtuelle de ce cours.');
        }

        $provider = (string) config('services.live.provider', 'jitsi');
        $roomData = $this->videoConferenceService->buildRoom($provider, $course->title, $lessonId, $userId);

        return response()->json(['data' => $roomData]);
    }
}
