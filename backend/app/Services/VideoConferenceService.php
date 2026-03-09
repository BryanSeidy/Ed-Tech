<?php

namespace App\Services;

class VideoConferenceService
{
    public function buildJitsiRoom(string $courseSlug, int $lessonId): array
    {
        $room = sprintf('edtech-%s-lesson-%d', $courseSlug, $lessonId);

        return [
            'provider' => 'jitsi',
            'room_name' => $room,
            'url' => sprintf('https://meet.jit.si/%s', $room),
        ];
    }
}
