<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Course */
class CourseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'thumbnail' => $this->thumbnail,
            'is_published' => (bool) $this->is_published,
            'instructor' => UserResource::make($this->whenLoaded('instructor')),
            'modules' => ModuleResource::collection($this->whenLoaded('modules')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
