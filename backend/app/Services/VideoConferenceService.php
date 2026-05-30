<?php

namespace App\Services;

use App\Models\Course;
use App\Models\LiveSession;
use App\Models\User;
use Illuminate\Support\Str;
use InvalidArgumentException;

class VideoConferenceService
{
    /**
     * @return array<string, mixed>
     */
    public function buildRoom(string $provider, string $courseTitle, int $lessonId, int $userId): array
    {
        $roomName = $this->buildLegacyRoomName($courseTitle, $lessonId);

        return $this->buildRoomPayload($provider, $roomName, [
            'user_id' => $userId,
            'lesson_id' => $lessonId,
        ]);
    }

    public function generateRoomName(Course $course, string $title): string
    {
        $courseSlug = Str::slug(Str::limit($course->title, 36, '')) ?: 'course';
        $titleSlug = Str::slug(Str::limit($title, 30, '')) ?: 'live';

        return sprintf('edtech-%s-%s-%s', $courseSlug, $titleSlug, Str::lower(Str::random(12)));
    }

    /**
     * @return array<string, mixed>
     */
    public function buildLiveSessionJoinPayload(LiveSession $liveSession, User $user): array
    {
        return $this->buildRoomPayload($liveSession->provider, $liveSession->room_name, [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'course_id' => $liveSession->course_id,
            'live_session_id' => $liveSession->id,
        ] + $this->sessionWindow($liveSession));
    }

    /**
     * @return array<string, mixed>
     */
    public function sessionWindow(LiveSession $liveSession): array
    {
        $now = now();
        $joinOpensAt = $liveSession->start_at->copy()->subMinutes(10);
        $joinClosesAt = $liveSession->end_at;

        return [
            'starts_at' => $liveSession->start_at->toIso8601String(),
            'ends_at' => $liveSession->end_at->toIso8601String(),
            'join_opens_at' => $joinOpensAt->toIso8601String(),
            'can_join' => $now->greaterThanOrEqualTo($joinOpensAt) && $now->lessThanOrEqualTo($joinClosesAt),
        ];
    }

    /**
     * @param array<string, mixed> $metadata
     * @return array<string, mixed>
     */
    private function buildRoomPayload(string $provider, string $roomName, array $metadata): array
    {
        return match ($provider) {
            'jitsi' => [
                'provider' => 'jitsi',
                'room_name' => $roomName,
                'url' => $this->buildJitsiUrl($roomName),
                'join_token' => null,
                'expires_at' => now()->addMinutes(30)->toIso8601String(),
                'metadata' => $metadata,
            ],
            default => throw new InvalidArgumentException('Provider de visioconférence non supporté.'),
        };
    }

    private function buildJitsiUrl(string $roomName): string
    {
        $domain = trim((string) config('services.live.jitsi_domain', 'meet.jit.si')) ?: 'meet.jit.si';
        $encodedRoomName = rawurlencode($roomName);

        return sprintf('https://%s/%s', $domain, $encodedRoomName);
    }

    private function buildLegacyRoomName(string $courseTitle, int $lessonId): string
    {
        $slug = Str::slug(Str::limit($courseTitle, 40, ''));

        return sprintf('edtech-%s-lesson-%d', $slug ?: 'course', $lessonId);
    }
}
