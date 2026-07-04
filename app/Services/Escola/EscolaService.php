<?php

namespace App\Services\Escola;

use App\Models\Escola;
use Illuminate\Database\Eloquent\Collection;

class EscolaService
{
    public function all(): Collection
    {
        return Escola::query()->orderBy('nome')->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Escola
    {
        return Escola::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Escola $escola, array $data): Escola
    {
        $escola->update($data);

        return $escola;
    }

    public function delete(Escola $escola): void
    {
        $escola->delete();
    }
}
