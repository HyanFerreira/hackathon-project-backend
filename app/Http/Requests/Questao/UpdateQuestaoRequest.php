<?php

namespace App\Http\Requests\Questao;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQuestaoRequest extends FormRequest
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
            'enunciado' => ['sometimes', 'required', 'string'],
            'dificuldade' => ['sometimes', 'string', 'in:facil,media,dificil'],
            'pontos' => ['sometimes', 'integer', 'min:1'],
            'status' => ['sometimes', 'string', 'in:ativa,inativa'],

            'habilidades' => ['sometimes', 'array', 'min:1'],
            'habilidades.*' => ['integer', Rule::exists('habilidades', 'id')],

            'alternativas' => ['sometimes', 'array', 'min:2'],
            'alternativas.*.texto' => ['required_with:alternativas', 'string'],
            'alternativas.*.correta' => ['required_with:alternativas', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->has('alternativas')) {
                return;
            }

            $alternativas = (array) $this->input('alternativas', []);
            $corretas = collect($alternativas)->filter(fn ($a) => filter_var($a['correta'] ?? false, FILTER_VALIDATE_BOOLEAN))->count();

            if ($corretas !== 1) {
                $validator->errors()->add('alternativas', 'A questão deve ter exatamente uma alternativa correta.');
            }
        });
    }
}
