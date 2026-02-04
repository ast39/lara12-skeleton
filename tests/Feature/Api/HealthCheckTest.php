<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_healthcheck_returns_ok_payload(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'timestamp',
            'checks' => [
                'database',
                'storage',
                'memory_heap',
                'memory_rss',
                'system',
            ],
        ]);
        $this->assertSame('ok', (string) $response->json('status'));
    }
}
