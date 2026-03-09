<?php

namespace App\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;

class EnrollRequest extends FormRequest
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
