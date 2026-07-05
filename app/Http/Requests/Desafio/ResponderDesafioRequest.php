<?php

namespace App\Http\Requests\Desafio;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResponderDesafioRequest extends FormRequest
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
