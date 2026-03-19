<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Support\Facades\Log;
use Spatie\RateLimitedMiddleware\RateLimited;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob as SpatieProcessWebhookJob;

final class ProcessWebhookJob extends SpatieProcessWebhookJob
{
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

        // Add your webhook processing logic here. To forward using laravel-webhook-server:
        // WebhookCall::create()
        //     ->url('https://example.com/webhooks/forward')
        //     ->payload($payload)
        //     ->useSecret(env('WEBHOOK_CLIENT_SECRET'))
        //     ->dispatch();
    }
}
