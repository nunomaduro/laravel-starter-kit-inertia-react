<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\WebhookEndpoint;
use Harris21\Fuse\CircuitBreaker;
use Illuminate\Support\Facades\Http;
use Spatie\WebhookServer\WebhookCall;
use Throwable;

final class WebhookDispatcher
{
    /**
     * Dispatch an event to all matching active endpoints for an organization.
     * Skips endpoints whose circuit breaker is open.
     */
    public function dispatch(string $event, array $payload, int $organizationId): void
    {
        WebhookEndpoint::withoutGlobalScopes()
            ->where('organization_id', $organizationId)
            ->where('is_active', true)
            ->whereJsonContains('events', $event)
            ->each(function (WebhookEndpoint $endpoint) use ($event, $payload): void {
                $breaker = new CircuitBreaker("webhook-{$endpoint->id}");

                if ($breaker->isOpen()) {
                    return;
                }

                WebhookCall::create()
                    ->url($endpoint->url)
                    ->payload([
                        'event' => $event,
                        'timestamp' => now()->toIso8601String(),
                        'data' => $payload,
                    ])
                    ->useSecret($endpoint->secret)
                    ->meta(['endpoint_id' => $endpoint->id])
                    ->dispatch();

                $endpoint->touchQuietly('last_called_at');
            });
    }

    /**
     * Synchronous test ping with configurable timeout.
     *
     * @return array{status?: int, error?: string, time_ms: int}
     */
    public function testPing(WebhookEndpoint $endpoint): array
    {
        $start = microtime(true);

        try {
            $response = Http::timeout((int) config('webhooks.timeout', 5))
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($endpoint->url, [
                    'event' => 'test.ping',
                    'timestamp' => now()->toIso8601String(),
                    'data' => [],
                ]);

            return [
                'status' => $response->status(),
                'time_ms' => (int) ((microtime(true) - $start) * 1000),
            ];
        } catch (Throwable $e) {
            return [
                'error' => $e->getMessage(),
                'time_ms' => (int) ((microtime(true) - $start) * 1000),
            ];
        }
    }
}
