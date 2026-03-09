<?php

namespace App\Http\Requests\Progress;

use Illuminate\Foundation\Http\FormRequest;

class MarkLessonCompletedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [];
    }
}
