<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_crud_flow_for_test_resource(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'password' => 'password',
        ]);

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        if ($login->status() !== 200) {
            $this->fail($login->getContent());
        }

        $token = (string) $login->json('data.token');
        $this->assertNotEmpty($token);

        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];

        // Create
        $create = $this->postJson('/api/v1/test', [
            'title' => 'Тест 1',
            'description' => 'Описание теста',
        ], $headers);

        $create->assertCreated();
        $create->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
            ],
        ]);

        $id = (int) $create->json('data.id');
        $this->assertGreaterThan(0, $id);

        // Index
        $this->getJson('/api/v1/test', $headers)
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'description'],
                ],
            ]);

        // Show
        $this->getJson('/api/v1/test/' . $id, $headers)
            ->assertOk()
            ->assertJsonPath('data.id', $id);

        // Update
        $this->putJson('/api/v1/test/' . $id, [
            'title' => 'Тест 1 (обновлён)',
            'description' => 'Описание (обновлён)',
        ], $headers)->assertOk();

        // Delete
        $this->deleteJson('/api/v1/test/' . $id, [], $headers)
            ->assertNoContent();
    }

    public function test_test_resource_requires_authentication(): void
    {
        $this->getJson('/api/v1/test')->assertUnauthorized();
    }
}
