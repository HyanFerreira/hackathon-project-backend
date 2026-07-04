<?php

namespace App\Http\Requests\Aluno;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAlunoRequest extends FormRequest
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
            'nome' => ['required', 'string', 'max:255'],
        ];
    }
}
