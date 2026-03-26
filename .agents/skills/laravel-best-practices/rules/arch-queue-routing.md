---
title: Queue Routing
impact: MEDIUM
impactDescription: Centralized job queue/connection configuration
tags: architecture, queues, jobs, routing
---

## Queue Routing

**Impact: MEDIUM (Centralized job queue/connection configuration)**

Laravel 13 adds `Queue::route()` to define default queue and connection routing for jobs in a central place, eliminating scattered `$connection` and `$queue` properties across job classes.

## Bad Example

```php
// Queue/connection config scattered across every job class
namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessPodcast implements ShouldQueue
{
    use Queueable;

    public $connection = 'redis';
    public $queue = 'podcasts';

    public function handle(): void
    {
        // ...
    }
}

class SendNewsletter implements ShouldQueue
{
    use Queueable;

    public $connection = 'redis';
    public $queue = 'emails';

    public function handle(): void
    {
        // ...
    }
}

class GenerateReport implements ShouldQueue
{
    use Queueable;

    public $connection = 'sqs';
    public $queue = 'reports';

    public function handle(): void
    {
        // ...
    }
}

// Problem: To change where podcasts are processed, you must find
// and update every job class individually.
```

## Good Example

```php
// Centralized queue routing in a service provider (Laravel 13+)
namespace App\Providers;

use App\Jobs\GenerateReport;
use App\Jobs\ProcessPodcast;
use App\Jobs\SendNewsletter;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Define default queue/connection routing per job class
        Queue::route(ProcessPodcast::class, connection: 'redis', queue: 'podcasts');
        Queue::route(SendNewsletter::class, connection: 'redis', queue: 'emails');
        Queue::route(GenerateReport::class, connection: 'sqs', queue: 'reports');
    }
}
```

```php
// Job classes stay clean — no routing config needed
namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessPodcast implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        // Automatically dispatched to redis/podcasts
    }
}
```

```php
// Dispatch normally — routing is applied automatically
ProcessPodcast::dispatch($podcast);
SendNewsletter::dispatch($newsletter);
GenerateReport::dispatch($report);
```

## Why

- **Single source of truth**: All queue routing in one place — easy to audit and change
- **Clean job classes**: Jobs contain only business logic, not infrastructure config
- **Environment flexibility**: Override routing per environment without touching job code
- **Discoverable**: New developers see all routing decisions in the service provider

Reference: [Laravel 13 Documentation — Queues](https://laravel.com/docs/13.x/queues#queue-routing)
