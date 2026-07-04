<?php

namespace App\Http\Requests\Escola;

use Illuminate\Foundation\Http\FormRequest;

class StoreEscolaRequest extends FormRequest
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
            'cidade' => ['nullable', 'string', 'max:255'],
            'estado' => ['nullable', 'string', 'size:2'],
            'status' => ['sometimes', 'string', 'in:ativa,inativa'],
        ];
    }
}
