<?php

namespace Database\Seeders;

use App\Models\Escola;
use Illuminate\Database\Seeder;

class EscolaSeeder extends Seeder
{
    public function run(): void
    {
        $escolas = [
            ['nome' => 'Escola Municipal Central', 'cidade' => 'São Paulo', 'estado' => 'SP'],
            ['nome' => 'Escola Estadual Norte', 'cidade' => 'Campinas', 'estado' => 'SP'],
        ];

        foreach ($escolas as $escola) {
            Escola::query()->updateOrCreate(
                ['nome' => $escola['nome']],
                [
                    'cidade' => $escola['cidade'],
                    'estado' => $escola['estado'],
                    'status' => 'ativa',
                ],
            );
        }
    }
}
