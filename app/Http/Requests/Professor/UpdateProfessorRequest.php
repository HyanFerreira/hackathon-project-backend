<?php

namespace App\Http\Requests\Professor;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfessorRequest extends FormRequest
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
        $professor = $this->route('professor');
        $professorId = $professor instanceof User ? $professor->id : $professor;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'cpf' => ['sometimes', 'required', 'string', 'size:11', Rule::unique('users', 'cpf')->ignore($professorId)],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($professorId)],
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
