<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\VerifyCustomDomain;
use App\Models\OrganizationDomain;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Spatie\RateLimitedMiddleware\RateLimited;
use Throwable;

final class VerifyOrganizationDomain implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 30;

    /** @var array<int, int> */
    public array $backoff = [10, 30, 60];

    public function __construct(private readonly OrganizationDomain $domain) {}

    public function middleware(): array
    {
        return [
            (new RateLimited)
                ->allow(30)
                ->everySeconds(60)
                ->releaseAfterSeconds(30),
        ];
    }

    public function handle(VerifyCustomDomain $action): void
    {
        $this->domain->refresh();

        if ($this->domain->status === 'active' || $this->domain->status === 'error') {
            return;
        }

        $verified = $action->handle($this->domain);

        if ($verified) {
            return;
        }

        if ($this->domain->status === 'error') {
            return;
        }

        $attempts = $this->domain->dns_check_attempts;
        $delay = match (true) {
            $attempts <= 3 => 5 * 60,
            $attempts <= 8 => 30 * 60,
            $attempts <= 24 => 60 * 60,
            default => 6 * 60 * 60,
        };

        dispatch(new self($this->domain))->delay(now()->addSeconds($delay));
    }

    public function failed(Throwable $exception): void
    {
        Log::error('VerifyOrganizationDomain failed', [
            'domain_id' => $this->domain->getKey(),
            'domain' => $this->domain->domain,
            'error' => $exception->getMessage(),
        ]);
    }
}
