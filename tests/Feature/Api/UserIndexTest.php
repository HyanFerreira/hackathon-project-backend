<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_users_through_the_public_api(): void
    {
        User::factory()->count(3)->create();

        $response = $this
            ->withHeader('Origin', 'http://localhost:3000')
            ->getJson('/api/users');

        $response
            ->assertOk()
            ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:3000')
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'email'],
                ],
            ])
            ->assertJsonMissingPath('data.0.password')
            ->assertJsonMissingPath('data.0.remember_token');
    }

    public function test_it_accepts_the_local_ip_frontend_origin(): void
    {
        $response = $this
            ->withHeader('Origin', 'http://127.0.0.1:3000')
            ->getJson('/api/users');

        $response
            ->assertOk()
            ->assertHeader(
                'Access-Control-Allow-Origin',
                'http://127.0.0.1:3000',
            );
    }
}
