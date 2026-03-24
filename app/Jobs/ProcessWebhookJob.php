<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Support\Facades\Log;
use Spatie\RateLimitedMiddleware\RateLimited;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob as SpatieProcessWebhookJob;
use Throwable;

final class ProcessWebhookJob extends SpatieProcessWebhookJob
{
    public int $timeout = 60;

    public function middleware(): array
    {
        return [
            (new RateLimited)
                ->allow(10)
                ->everySeconds(1)
                ->releaseAfterSeconds(5),
        ];
    }

    public function handle(): void
    {
        $payload = $this->webhookCall->payload;

        Log::info('Webhook received', [
            'name' => $this->webhookCall->name,
            'payload_keys' => is_array($payload) ? array_keys($payload) : null,
        ]);

        // TODO: Add webhook processing logic here.
        // Example: forward using laravel-webhook-server:
        // WebhookCall::create()->url('...')->payload($payload)->useSecret('...')->dispatch();
    }

    public function failed(Throwable $exception): void
    {
        Log::error('ProcessWebhookJob failed', [
            'webhook_name' => $this->webhookCall->name,
            'webhook_id' => $this->webhookCall->getKey(),
            'error' => $exception->getMessage(),
        ]);
    }
}
