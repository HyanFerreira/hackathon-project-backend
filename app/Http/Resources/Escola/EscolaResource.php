<?php

namespace App\Http\Resources\Escola;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EscolaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'cidade' => $this->cidade,
            'estado' => $this->estado,
            'status' => $this->status,
        ];
    }
}
