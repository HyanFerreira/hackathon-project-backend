<?php

namespace App\Http\Requests\Vinculo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VincularProfessorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'professor_id' => ['required', 'integer', Rule::exists('users', 'id')],
        ];
    }
}
