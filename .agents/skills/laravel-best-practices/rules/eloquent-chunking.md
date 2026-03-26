---
title: Chunking for Large Datasets
impact: CRITICAL
impactDescription: Prevents memory exhaustion on large datasets
tags: eloquent, chunking, memory, performance
---

## Chunking for Large Datasets

**Impact: CRITICAL (Prevents memory exhaustion on large datasets)**

Process large datasets in chunks to prevent memory exhaustion and timeout issues.

## Bad Example

```php
// Loading all records into memory - will crash with large datasets
class ReportController extends Controller
{
    public function export()
    {
        $users = User::all(); // 1 million users = memory exhausted

        foreach ($users as $user) {
            // Process each user
        }
    }
}

// Also bad: using get() on large datasets
$orders = Order::where('status', 'completed')->get();

// Memory-intensive collection operations
$total = User::all()->sum('balance'); // Loads all users
```

## Good Example

```php
// Chunk for processing large datasets
class UserService
{
    public function processAllUsers(): void
    {
        User::chunk(1000, function ($users) {
            foreach ($users as $user) {
                $this->processUser($user);
            }
        });
    }
}

// ChunkById for safer chunking (prevents issues with modifications)
User::chunkById(1000, function ($users) {
    foreach ($users as $user) {
        $user->update(['processed' => true]);
    }
});

// Lazy collections - memory efficient iteration
User::lazy()->each(function ($user) {
    // Process one user at a time
    // Only one model in memory at a time
});

// Lazy with chunk size
User::lazyById(500)->each(function ($user) {
    $this->sendNotification($user);
});

// Cursor for read-only operations
foreach (User::cursor() as $user) {
    // Uses PHP generator, very memory efficient
    echo $user->name;
}

// Database aggregates instead of loading data
$total = User::sum('balance'); // Single query
$average = Order::avg('total');
$count = Product::where('active', true)->count();

// Batch updates without loading models
User::where('last_login', '<', now()->subYear())
    ->update(['status' => 'inactive']);

// Batch delete
Order::where('created_at', '<', now()->subYears(5))
    ->delete();

// Export large datasets efficiently
class ExportUsersJob implements ShouldQueue
{
    public function handle()
    {
        $filename = 'users-' . now()->format('Y-m-d') . '.csv';
        $file = fopen(storage_path("exports/{$filename}"), 'w');

        // Write header
        fputcsv($file, ['ID', 'Name', 'Email', 'Created']);

        User::chunk(1000, function ($users) use ($file) {
            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->created_at,
                ]);
            }
        });

        fclose($file);
    }
}

// Query with chunk for complex operations
Order::query()
    ->where('status', 'pending')
    ->with('items')
    ->chunkById(500, function ($orders) {
        foreach ($orders as $order) {
            ProcessOrderJob::dispatch($order);
        }
    });
```

## Why

- **Memory efficiency**: Only loads a subset of records at a time
- **Prevents crashes**: Avoids memory exhaustion on large datasets
- **Prevents timeouts**: Work is done in manageable batches
- **Database friendly**: Reduces database connection time
- **Scalable**: Works regardless of dataset size
- **Production safe**: Essential for background jobs processing bulk data
