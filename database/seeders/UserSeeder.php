<?php

namespace Database\Seeders;

use App\Models\Escola;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $principal = Escola::query()->where('nome', EscolaSeeder::PRINCIPAL)->first();

        $users = [
            ['name' => 'Ana Souza', 'cpf' => '52998224725', 'email' => 'ana@example.com', 'role' => 'admin', 'escola_id' => null],
            ['name' => 'Bruno Lima', 'cpf' => '11144477735', 'email' => 'bruno@example.com', 'role' => 'gestor', 'escola_id' => $principal?->id],
            ['name' => 'Carla Mendes', 'cpf' => '39053344705', 'email' => 'carla@example.com', 'role' => 'professor', 'escola_id' => $principal?->id],
        ];

        foreach ($users as $user) {
            $model = User::query()->updateOrCreate(
                ['cpf' => $user['cpf']],
                [
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'escola_id' => $user['escola_id'],
                    'password' => Hash::make(User::DEFAULT_PASSWORD),
                    'email_verified_at' => now(),
                ],
            );

            $model->syncRoles([$user['role']]);
        }
    }
}
