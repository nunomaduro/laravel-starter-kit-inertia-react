# Rate-limited job middleware

Queued jobs can be rate-limited using [spatie/laravel-rate-limited-job-middleware](https://github.com/spatie/laravel-rate-limited-job-middleware). The middleware limits how many jobs run per time window; jobs over the limit are released back to the queue and retried after a delay (or dropped with `dontRelease()`).

## Requirements

- **Redis** — The middleware uses Redis by default to track job execution (same as Horizon). Ensure `QUEUE_CONNECTION=redis` and Redis is configured.

## Usage

Add the `Spatie\RateLimitedMiddleware\RateLimited` middleware to a job’s `middleware()` method. Configure:

- **allow(n)** — Max jobs allowed in the time window.
- **everySeconds(s)** / **everyMinute(n)** — Time window in seconds or minutes.
- **releaseAfterSeconds(s)** — Delay before retrying when rate limit is hit (omit or use **dontRelease()** to skip retry).

Example:

```php
use Spatie\RateLimitedMiddleware\RateLimited;

public function middleware(): array
{
    return [
        (new RateLimited)
            ->allow(30)
            ->everySeconds(60)
            ->releaseAfterSeconds(90),
    ];
}
```

## Jobs using rate limiting in this app

| Job | Limit | Purpose |
|-----|--------|--------|
| `ProcessWebhookJob` | 10/sec, release 5s | Avoid flooding when processing incoming webhooks |
| `NotifyUsersOfNewTermsVersion` | 30/min, release 90s | Avoid mail/notification provider limits when notifying many users |
| `ProcessDunningReminders` | 20/min, release 60s | Throttle dunning notification bursts |
| `ProcessTrialEndingReminders` | 20/min, release 60s | Throttle trial reminder notifications |
| `VerifyOrganizationDomain` | 30/min, release 30s | Throttle DNS checks when many domains are queued |

## Optional behaviour

- **dontRelease()** — When rate limit is hit, do not re-queue the job (useful for periodic jobs where skipping is acceptable).
- **enabled(bool|Closure)** — Conditionally enable or disable the middleware (e.g. only in production).
- **key(string)** — Custom Redis key (default is the job class name).
- **connectionName(string)** — Use a different Redis connection.
- **releaseAfterBackoff($this->attempts(), $rate)** — Exponential backoff for retries (e.g. for API rate limits).

For time-based retries instead of a fixed attempt count, use `retryUntil()` on the job (see [Laravel queues](https://laravel.com/docs/queues#time-based-attempts)).

## Events

- `Spatie\RateLimitedMiddleware\Events\LimitExceeded` — Dispatched when the rate limit is exceeded (useful for monitoring or alerting).

## Related

- **Horizon** — [horizon.md](./horizon.md) for queue monitoring and Redis workers.
- **Webhooks** — [webhooks-spatie.md](./webhooks-spatie.md) for sending/receiving webhooks (ProcessWebhookJob is rate-limited).
