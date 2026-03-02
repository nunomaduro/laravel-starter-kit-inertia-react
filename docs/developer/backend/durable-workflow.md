# Durable Workflow & Waterline

The starter kit includes **[laravel-workflow/laravel-workflow](https://github.com/durable-workflow/workflow)** (Durable Workflow) and **[laravel-workflow/waterline](https://github.com/durable-workflow/waterline)** for defining long-running, persistent workflows and monitoring them in a dashboard.

## What It Is

- **Durable Workflow** — A durable workflow engine on top of Laravel queues. You define **workflows** (orchestrations) and **activities** (units of work). Workflows can run for minutes, hours, or days; survive restarts; wait for webhooks or human approval; and support branching, parallelism, and retries.
- **Waterline** — A UI to monitor workflow runs (similar to what Horizon is for queues). Dashboard at `/waterline` (or `WATERLINE_PATH`).

## When to Use Workflows

Consider workflows when you need:

- To restart after a crash without duplicating work or leaving inconsistent state
- To pause and resume later without keeping a process running
- To wait for a webhook or external event
- Human-in-the-loop (e.g. approval steps)
- Processes that span minutes, hours, or days (onboarding, sagas, data pipelines, agentic/AI flows)

For one-off or simple scheduled tasks, standard Laravel jobs and Horizon remain the right choice.

## Requirements

- **Queue** — Workflows run via Laravel queues. Use Redis with Horizon for production (same as existing queue setup).
- **Database** — Workflow state is stored in the database (tables are provided by the workflow package migrations when you run `migrate`).
- **Authorization** — Waterline is restricted to users who can access the Filament admin panel (`access admin panel` permission), same as Horizon.

## Configuration

- **Waterline config**: `config/waterline.php` — `path` (default `waterline`), `domain`, `middleware`.
- **Gate**: `App\Providers\WaterlineServiceProvider::gate()` defines `viewWaterline`; it allows users who `can('access admin panel')`.
- **Optional env**: `WATERLINE_PATH=waterline`, `WATERLINE_DOMAIN` (subdomain if desired).

## Creating a Workflow and Activity

**1. Create an activity** (single unit of work):

```php
use Workflow\Activity;

class MyActivity extends Activity
{
    public function execute($name)
    {
        return "Hello, {$name}!";
    }
}
```

**2. Create a workflow** (orchestration that yields activities):

```php
use function Workflow\activity;
use Workflow\Workflow;

class MyWorkflow extends Workflow
{
    public function execute($name)
    {
        $result = yield activity(MyActivity::class, $name);
        return $result;
    }
}
```

**3. Run the workflow**:

```php
use Workflow\WorkflowStub;

$workflow = WorkflowStub::make(MyWorkflow::class);
$workflow->start('world');

// Optional: wait for result (or poll / use events)
$output = $workflow->output(); // 'Hello, world!'
```

Use `php artisan make:workflow MyWorkflow` to generate a workflow class.

### Example in this app

The starter includes a minimal demo:

- **Activity**: `App\Workflows\DemoGreetingActivity` — `execute(string $name)` returns a greeting string.
- **Workflow**: `App\Workflows\DemoGreetingWorkflow` — yields the activity and returns its result.

To run it (e.g. in tinker or a command):

```php
use Workflow\WorkflowStub;

$workflow = WorkflowStub::make(\App\Workflows\DemoGreetingWorkflow::class);
$workflow->start('world');
// Optional: $workflow->output(); to wait for result
```

With the queue running (e.g. `php artisan horizon`), the run appears in the Waterline dashboard at `/waterline`.

## Waterline Dashboard

With the queue running (e.g. `php artisan horizon` or `queue:work`) and workflows in use, visit `/waterline` (or your `WATERLINE_PATH`) when logged in as a user with admin panel access. The dashboard lists workflow runs, status, duration, and activity history.

## Running Workers

Workflows are executed by the same queue workers as regular jobs. Use Horizon in development and production:

- `php artisan horizon` — run workers (requires `QUEUE_CONNECTION=redis`).
- `composer dev` — starts server, queue listener, logs, and Vite; the queue worker will process both jobs and workflow steps.

## References

- [Durable Workflow (official docs)](https://durable-workflow.com/docs/introduction/)
- [GitHub: durable-workflow/workflow](https://github.com/durable-workflow/workflow)
- [GitHub: durable-workflow/waterline](https://github.com/durable-workflow/waterline)
- [Horizon](./horizon.md) — queue monitoring; workflows run on the same queues.
