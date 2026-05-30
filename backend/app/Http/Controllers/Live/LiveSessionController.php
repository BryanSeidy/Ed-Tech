<?php

namespace App\Http\Controllers\Live;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LiveMessage;
use App\Models\LiveSession;
use App\Models\User;
use App\Services\VideoConferenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class LiveSessionController extends Controller
{
    public function __construct(private readonly VideoConferenceService $videoConferenceService)
    {
    }

    public function indexForCourse(Request $request, Course $course): JsonResponse
    {
        $user = $request->user();
        $this->authorizeCourseAccess($course, $user);

        $sessions = $course->liveSessions()
            ->with(['course:id,title', 'creator:id,name,email'])
            ->orderBy('start_at')
            ->get()
            ->map(fn (LiveSession $session) => $this->serializeSession($session, $user));

        return response()->json(['data' => $sessions]);
    }

    public function upcoming(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = LiveSession::query()
            ->with(['course:id,title,instructor_id', 'creator:id,name,email'])
            ->where('end_at', '>=', now()->subMinutes(10))
            ->orderBy('start_at');

        if ($user->role === 'admin') {
            // Admins can supervise every live class.
        } elseif ($user->role === 'instructor') {
            $query->whereHas('course', fn ($courseQuery) => $courseQuery->where('instructor_id', $user->id));
        } else {
            $query->whereHas('course.enrollments', fn ($enrollmentQuery) => $enrollmentQuery->where('user_id', $user->id));
        }

        $sessions = $query->get()->map(fn (LiveSession $session) => $this->serializeSession($session, $user));

        return response()->json(['data' => $sessions]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! in_array($user->role, ['instructor', 'admin'], true)) {
            throw new AccessDeniedHttpException('Seuls les formateurs et administrateurs peuvent planifier une classe virtuelle.');
        }

        $validator = Validator::make($request->all(), [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'lesson_id' => ['nullable', 'integer', 'exists:lessons,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'start_at' => ['required', 'date', 'after_or_equal:now'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:480'],
            'provider' => ['nullable', 'string', Rule::in(['jitsi'])],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 'validation_error',
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $course = Course::query()->findOrFail((int) $request->integer('course_id'));

        if ($user->role !== 'admin' && $course->instructor_id !== $user->id) {
            throw new AccessDeniedHttpException('Vous ne pouvez planifier que les classes de vos propres cours.');
        }

        if ($request->filled('lesson_id')) {
            $lessonBelongsToCourse = $course->modules()
                ->whereHas('lessons', fn ($lessonQuery) => $lessonQuery->where('lessons.id', $request->integer('lesson_id')))
                ->exists();

            if (! $lessonBelongsToCourse) {
                return response()->json([
                    'code' => 'validation_error',
                    'message' => 'Validation failed.',
                    'errors' => ['lesson_id' => ['La leçon sélectionnée ne fait pas partie de ce cours.']],
                ], 422);
            }
        }

        $startAt = Carbon::parse((string) $request->input('start_at'));
        $durationMinutes = (int) $request->integer('duration_minutes');
        $roomName = $this->uniqueRoomName($course, (string) $request->input('title'));

        $liveSession = LiveSession::create([
            'course_id' => $course->id,
            'lesson_id' => $request->filled('lesson_id') ? (int) $request->integer('lesson_id') : null,
            'title' => trim((string) $request->input('title')),
            'description' => $request->filled('description') ? trim((string) $request->input('description')) : null,
            'start_at' => $startAt,
            'end_at' => $startAt->copy()->addMinutes($durationMinutes),
            'duration_minutes' => $durationMinutes,
            'provider' => $request->input('provider', config('services.live.provider', 'jitsi')),
            'room_name' => $roomName,
            'created_by' => $user->id,
        ])->load(['course:id,title,instructor_id', 'creator:id,name,email']);

        return response()->json(['data' => $this->serializeSession($liveSession, $user)], 201);
    }

    public function join(Request $request, LiveSession $liveSession): JsonResponse
    {
        $user = $request->user();
        $this->authorizeSessionAccess($liveSession, $user);

        $window = $this->videoConferenceService->sessionWindow($liveSession);
        if (! $window['can_join']) {
            return response()->json([
                'code' => 'live_session_not_open',
                'message' => 'La classe virtuelle n’est pas encore ouverte ou est déjà terminée.',
                'data' => $this->serializeSession($liveSession->load(['course:id,title,instructor_id', 'creator:id,name,email']), $user),
            ], 403);
        }

        return response()->json([
            'data' => [
                'session' => $this->serializeSession($liveSession->load(['course:id,title,instructor_id', 'creator:id,name,email']), $user),
                'room' => $this->videoConferenceService->buildLiveSessionJoinPayload($liveSession, $user),
            ],
        ]);
    }

    public function messages(Request $request, LiveSession $liveSession): JsonResponse
    {
        $this->authorizeSessionAccess($liveSession, $request->user());

        $messages = $liveSession->messages()
            ->with('user:id,name')
            ->when($request->filled('after_id'), fn ($query) => $query->where('id', '>', $request->integer('after_id')))
            ->orderBy('id')
            ->limit(100)
            ->get()
            ->map(fn (LiveMessage $message) => $this->serializeMessage($message));

        return response()->json(['data' => $messages]);
    }

    public function storeMessage(Request $request, LiveSession $liveSession): JsonResponse
    {
        $user = $request->user();
        $this->authorizeSessionAccess($liveSession, $user);

        if (! $this->videoConferenceService->sessionWindow($liveSession)['can_join']) {
            return response()->json([
                'code' => 'live_session_not_open',
                'message' => 'Le tchat est disponible uniquement pendant la classe virtuelle.',
            ], 403);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'min:1', 'max:1000'],
        ]);

        $message = LiveMessage::create([
            'live_session_id' => $liveSession->id,
            'user_id' => $user->id,
            'message' => trim((string) $validated['message']),
        ])->load('user:id,name');

        return response()->json(['data' => $this->serializeMessage($message)], 201);
    }

    private function authorizeCourseAccess(Course $course, User $user): void
    {
        if ($user->role === 'admin' || $course->instructor_id === $user->id) {
            return;
        }

        $isEnrolled = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->exists();

        if (! $isEnrolled) {
            throw new AccessDeniedHttpException('Accès refusé aux classes virtuelles de ce cours.');
        }
    }

    private function authorizeSessionAccess(LiveSession $liveSession, User $user): void
    {
        $liveSession->loadMissing('course');
        $this->authorizeCourseAccess($liveSession->course, $user);
    }

    private function uniqueRoomName(Course $course, string $title): string
    {
        do {
            $roomName = $this->videoConferenceService->generateRoomName($course, $title);
        } while (LiveSession::query()->where('room_name', $roomName)->exists());

        return $roomName;
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeSession(LiveSession $session, User $user): array
    {
        $window = $this->videoConferenceService->sessionWindow($session);

        return [
            'id' => $session->id,
            'course_id' => $session->course_id,
            'lesson_id' => $session->lesson_id,
            'title' => $session->title,
            'description' => $session->description,
            'start_at' => $session->start_at->toIso8601String(),
            'end_at' => $session->end_at->toIso8601String(),
            'duration_minutes' => $session->duration_minutes,
            'provider' => $session->provider,
            'room_name' => $session->room_name,
            'created_by' => $session->created_by,
            'course' => $session->course ? [
                'id' => $session->course->id,
                'title' => $session->course->title,
            ] : null,
            'creator' => $session->creator ? [
                'id' => $session->creator->id,
                'name' => $session->creator->name,
                'email' => $session->creator->email,
            ] : null,
            'can_join' => $window['can_join'],
            'join_opens_at' => $window['join_opens_at'],
            'is_host' => $user->role === 'admin' || $session->created_by === $user->id || $session->course?->instructor_id === $user->id,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeMessage(LiveMessage $message): array
    {
        return [
            'id' => $message->id,
            'live_session_id' => $message->live_session_id,
            'message' => $message->message,
            'created_at' => $message->created_at?->toIso8601String(),
            'user' => $message->user ? [
                'id' => $message->user->id,
                'name' => $message->user->name,
            ] : null,
        ];
    }
}
