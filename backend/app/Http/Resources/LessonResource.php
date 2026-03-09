<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Lesson */
class LessonResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'module_id' => $this->module_id,
            'title' => $this->title,
            'content' => $this->content,
            'video_url' => $this->video_url,
            'duration' => $this->duration,
            'position' => $this->position,
            'quiz' => QuizResource::make($this->whenLoaded('quiz')),
        ];
    }
}
