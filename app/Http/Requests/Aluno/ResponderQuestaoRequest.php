<?php

namespace App\Http\Requests\Aluno;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResponderQuestaoRequest extends FormRequest
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
            'alternativa_id' => ['required', 'integer', Rule::exists('questao_alternativas', 'id')],
        ];
    }
}
