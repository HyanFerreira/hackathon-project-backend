<?php

namespace App\Http\Requests\Gestor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGestorRequest extends FormRequest
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
            'escola_id' => ['required', 'integer', Rule::exists('escolas', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'size:11', Rule::unique('users', 'cpf')],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('cpf')) {
            $this->merge([
                'cpf' => preg_replace('/\D/', '', (string) $this->input('cpf')),
            ]);
        }
    }
}
