---
title: Model Pruning
impact: MEDIUM
impactDescription: Automatic cleanup of old records
tags: eloquent, pruning, cleanup, maintenance
---

## Model Pruning

**Impact: MEDIUM (Automatic cleanup of old records)**

Use model pruning to automatically clean up old or obsolete database records.

## Bad Example

```php
// Manual cleanup in random places
class CleanupController extends Controller
{
    public function cleanup()
    {
        // Deleting old records manually
        ActivityLog::where('created_at', '<', now()->subMonths(6))->delete();
        PasswordReset::where('created_at', '<', now()->subDay())->delete();
        Session::where('last_activity', '<', now()->subWeek())->delete();

        return response()->json(['message' => 'Cleanup complete']);
    }
}

// Or in a poorly organized command
class CleanupOldRecords extends Command
{
    protected $signature = 'app:cleanup';

    public function handle()
    {
        // All cleanup logic mixed together
        $this->info('Cleaning activity logs...');
        ActivityLog::where('created_at', '<', now()->subMonths(6))->delete();

        $this->info('Cleaning password resets...');
        PasswordReset::where('created_at', '<', now()->subDay())->delete();

        // Easy to forget to add new models
    }
}
```

## Good Example

```php
// Model with Prunable trait
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Facades\Storage;

class ActivityLog extends Model
{
    use Prunable;

    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        // Delete activity logs older than 6 months
        return static::where('created_at', '<', now()->subMonths(6));
    }

    /**
     * Prepare the model for pruning (optional).
     */
    protected function pruning(): void
    {
        // Clean up related resources before deletion
        Storage::delete($this->attachment_path);
    }
}
```

```php
// For soft-deletable models, use MassPrunable for efficiency
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    use MassPrunable;

    public function prunable(): Builder
    {
        // Delete password reset tokens older than 24 hours
        return static::where('created_at', '<', now()->subDay());
    }
}
```

```php
// Complex pruning conditions
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use MassPrunable;

    public function prunable(): Builder
    {
        return static::where('last_activity', '<', now()->subWeek())
            ->orWhere(function ($query) {
                $query->whereNull('user_id')
                      ->where('created_at', '<', now()->subDay());
            });
    }
}
```

```php
// Prunable with related cleanup
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Order extends Model
{
    use SoftDeletes, Prunable;

    public function prunable(): Builder
    {
        // Only prune soft-deleted orders older than 1 year
        return static::onlyTrashed()
            ->where('deleted_at', '<', now()->subYear());
    }

    protected function pruning(): void
    {
        // Clean up related records
        $this->items()->forceDelete();
        $this->payments()->forceDelete();

        // Clean up files
        Storage::delete($this->invoice_path);
    }
}
```

```php
// Prunable notifications
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

class DatabaseNotification extends Model
{
    use MassPrunable;

    public function prunable(): Builder
    {
        return static::whereNotNull('read_at')
            ->where('read_at', '<', now()->subMonths(3));
    }
}
```

```php
// Schedule pruning in routes/console.php (Laravel 11+)
use Illuminate\Support\Facades\Schedule;

Schedule::command('model:prune')->daily();

// Prune specific models
Schedule::command('model:prune', [
    '--model' => [ActivityLog::class, Session::class],
])->daily();

// With chunk size for large datasets
Schedule::command('model:prune', ['--chunk' => 1000])->daily();
```

```bash
# Run manually
php artisan model:prune
php artisan model:prune --model=App\\Models\\ActivityLog
php artisan model:prune --pretend
```

## Why

- **Automatic cleanup**: Database stays clean without manual intervention
- **Self-documenting**: Pruning logic lives with the model
- **Discoverable**: Laravel automatically finds all prunable models
- **Memory efficient**: MassPrunable uses bulk deletes
- **Lifecycle hooks**: Can clean up related resources before deletion
- **Testable**: Pruning logic can be unit tested
- **Scheduled**: Built-in Artisan command for scheduling
