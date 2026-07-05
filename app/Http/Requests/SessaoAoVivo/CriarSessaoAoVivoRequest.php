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
            'titulo' => ['required', 'string', 'max:255'],
        ];
    }
}
