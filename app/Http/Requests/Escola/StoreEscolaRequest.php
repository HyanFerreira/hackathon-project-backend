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
            'cnpj' => ['nullable', 'string', 'size:14'],
            'cidade' => ['nullable', 'string', 'max:255'],
            'estado' => ['nullable', 'string', 'size:2'],
            'status' => ['sometimes', 'string', 'in:ativa,inativa'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('cnpj') && $this->input('cnpj') !== null) {
            $this->merge([
                'cnpj' => preg_replace('/\D/', '', (string) $this->input('cnpj')),
            ]);
        }
    }
}
