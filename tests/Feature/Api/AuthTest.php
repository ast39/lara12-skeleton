<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_jwt_token(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'password' => 'password',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        if ($response->status() !== 200) {
            $this->fail($response->getContent());
        }

        $response->assertJsonStructure([
            'data' => [
                'token',
                'expires_in',
            ],
        ]);

        $this->assertNotEmpty((string) $response->json('data.token'));
        $this->assertIsInt($response->json('data.expires_in'));
    }

    public function test_protected_endpoint_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertUnauthorized();
    }
}
