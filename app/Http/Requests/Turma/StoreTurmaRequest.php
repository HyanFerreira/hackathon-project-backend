<?php

namespace App\Http\Requests\Turma;

use Illuminate\Foundation\Http\FormRequest;

class StoreTurmaRequest extends FormRequest
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
            'ano' => ['nullable', 'string', 'max:50'],
            'turno' => ['nullable', 'string', 'in:manha,tarde,noite,integral'],
            'status' => ['sometimes', 'string', 'in:ativa,inativa'],
        ];
    }
}
