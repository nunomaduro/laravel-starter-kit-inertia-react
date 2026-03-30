<?php

declare(strict_types=1);

namespace App\Listeners;

use Harris21\Fuse\CircuitBreaker;
use Spatie\WebhookServer\Events\WebhookCallSucceededEvent;

final class RecordWebhookSuccess
{
    public function handle(WebhookCallSucceededEvent $event): void
    {
        $endpointId = $this->extractEndpointId($event);

        if ($endpointId === null) {
            return;
        }

        $breaker = new CircuitBreaker("webhook-{$endpointId}");
        $breaker->recordSuccess();
    }

    private function extractEndpointId(WebhookCallSucceededEvent $event): ?int
    {
        $meta = $event->meta ?? [];

        return isset($meta['endpoint_id']) ? (int) $meta['endpoint_id'] : null;
    }
}
