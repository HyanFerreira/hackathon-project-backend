<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Ana Souza', 'cpf' => '52998224725', 'email' => 'ana@example.com', 'role' => 'admin'],
            ['name' => 'Bruno Lima', 'cpf' => '11144477735', 'email' => 'bruno@example.com', 'role' => 'user'],
            ['name' => 'Carla Mendes', 'cpf' => '39053344705', 'email' => 'carla@example.com', 'role' => 'user'],
        ];

        foreach ($users as $user) {
            $model = User::query()->updateOrCreate(
                ['cpf' => $user['cpf']],
                [
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
            );

            $model->syncRoles([$user['role']]);
        }

        User::factory()->count(10)->withRole('user')->create();
    }
}
