<?php

namespace App\Http\Requests\Gestor;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGestorRequest extends FormRequest
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
        $gestor = $this->route('gestor');
        $gestorId = $gestor instanceof User ? $gestor->id : $gestor;

        return [
            'escola_id' => ['sometimes', 'required', 'integer', Rule::exists('escolas', 'id')],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'cpf' => ['sometimes', 'required', 'string', 'size:11', Rule::unique('users', 'cpf')->ignore($gestorId)],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($gestorId)],
            'password' => ['sometimes', 'required', 'string', 'min:8'],
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
