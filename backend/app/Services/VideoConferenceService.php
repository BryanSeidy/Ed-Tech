<?php

namespace App\Services;

use Illuminate\Support\Str;
use InvalidArgumentException;

class VideoConferenceService
{
    /**
     * @return array<string, mixed>
     */
    public function buildRoom(string $provider, string $courseTitle, int $lessonId, int $userId): array
    {
        $roomName = $this->buildRoomName($courseTitle, $lessonId);

        return match ($provider) {
            'jitsi' => [
                'provider' => 'jitsi',
                'room_name' => $roomName,
                'url' => sprintf('https://meet.jit.si/%s', $roomName),
                'join_token' => null,
                'expires_at' => now()->addHours(2)->toIso8601String(),
                'metadata' => [
                    'user_id' => $userId,
                    'lesson_id' => $lessonId,
                ],
            ],
            'daily' => [
                'provider' => 'daily',
                'room_name' => $roomName,
                'url' => sprintf('https://%s.daily.co/%s', env('DAILY_SUBDOMAIN', 'edtech-live'), $roomName),
                'join_token' => null,
                'expires_at' => now()->addHours(2)->toIso8601String(),
                'metadata' => [
                    'user_id' => $userId,
                    'lesson_id' => $lessonId,
                ],
            ],
            default => throw new InvalidArgumentException('Provider de visioconférence non supporté.'),
        };
    }

    private function buildRoomName(string $courseTitle, int $lessonId): string
    {
        $slug = Str::slug(Str::limit($courseTitle, 40, ''));

        return sprintf('edtech-%s-lesson-%d', $slug ?: 'course', $lessonId);
    }
}
