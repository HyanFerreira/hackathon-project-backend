<?php

namespace App\Http\Requests\Desafio;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CriarDesafioRequest extends FormRequest
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
            'desafiado_id' => ['required', 'integer', Rule::exists('alunos', 'id')],
            'disciplina_id' => ['nullable', 'integer', Rule::exists('disciplinas', 'id')],
            'tipo' => ['sometimes', 'string', 'in:amistoso,valendo'],
            'quantidade_questoes' => ['sometimes', 'integer', 'min:1', 'max:10'],
        ];
    }
}
