<?php

declare(strict_types=1);

namespace App\Listeners;

use Harris21\Fuse\CircuitBreaker;
use Spatie\WebhookServer\Events\WebhookCallFailedEvent;

final class RecordWebhookFailure
{
    public function handle(WebhookCallFailedEvent $event): void
    {
        $endpointId = $this->extractEndpointId($event);

        if ($endpointId === null) {
            return;
        }

        $breaker = new CircuitBreaker("webhook-{$endpointId}");
        $breaker->recordFailure();
    }

    private function extractEndpointId(WebhookCallFailedEvent $event): ?int
    {
        $meta = $event->meta ?? [];

        return isset($meta['endpoint_id']) ? (int) $meta['endpoint_id'] : null;
    }
}
