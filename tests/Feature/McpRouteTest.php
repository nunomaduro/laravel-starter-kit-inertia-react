<?php

declare(strict_types=1);

use App\Models\User;

test('MCP route is registered and reachable', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/mcp/api', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'initialize',
            'params' => [
                'protocolVersion' => '2025-06-18',
                'clientInfo' => [
                    'name' => 'test-client',
                    'version' => '1.0.0',
                ],
                'capabilities' => new stdClass,
            ],
        ]);

    $response->assertOk()
        ->assertJsonStructure([
            'jsonrpc',
            'id',
            'result' => [
                'protocolVersion',
                'serverInfo' => ['name', 'version'],
                'capabilities',
                'instructions',
            ],
        ])
        ->assertJsonPath('result.serverInfo.name', 'Api Server')
        ->assertJsonPath('result.serverInfo.version', '0.0.1')
        ->assertJsonPath('result.protocolVersion', '2025-06-18');
});

test('MCP endpoint requires authentication', function (): void {
    $response = $this->postJson('/mcp/api', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'initialize',
        'params' => [
            'protocolVersion' => '2025-06-18',
            'clientInfo' => [
                'name' => 'test-client',
                'version' => '1.0.0',
            ],
        ],
    ]);

    $response->assertUnauthorized();
});

test('MCP endpoint rejects GET requests', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();

    $this->actingAs($user, 'sanctum')
        ->getJson('/mcp/api')
        ->assertMethodNotAllowed();
});
