<?php

namespace App\Http\Requests\SessaoAoVivo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CriarSessaoAoVivoRequest extends FormRequest
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
            'turma_id' => ['required', 'integer', Rule::exists('turmas', 'id')],
            'titulo' => ['nullable', 'string', 'max:255'],
            'questoes' => ['required', 'array', 'min:1', 'max:50'],
            'questoes.*' => ['integer', 'distinct', Rule::exists('questoes', 'id')],
        ];
    }
}
