---
title: Single Action Controllers
impact: MEDIUM
impactDescription: Focused controllers with single responsibility
tags: controllers, invokable, single-action
---

## Single Action Controllers

**Impact: MEDIUM (Focused controllers with single responsibility)**

Use invokable controllers for actions that don't fit RESTful resource methods.

## Bad Example

```php
// Controller with unrelated methods
class UserController extends Controller
{
    public function index() { /* list users */ }
    public function show(User $user) { /* show user */ }
    public function store(Request $request) { /* create user */ }

    // Non-RESTful actions crammed into resource controller
    public function exportToCsv() { /* ... */ }
    public function importFromCsv(Request $request) { /* ... */ }
    public function sendNewsletter(User $user) { /* ... */ }
    public function generateReport() { /* ... */ }
    public function toggleStatus(User $user) { /* ... */ }
}

// Routes become messy
Route::get('/users/export', [UserController::class, 'exportToCsv']);
Route::post('/users/import', [UserController::class, 'importFromCsv']);
Route::post('/users/{user}/newsletter', [UserController::class, 'sendNewsletter']);
```

## Good Example

```php
// Single action (invokable) controller
namespace App\Http\Controllers\User;

class ExportUsersController extends Controller
{
    public function __invoke(ExportUsersRequest $request)
    {
        $users = User::query()
            ->when($request->role, fn($q, $role) => $q->where('role', $role))
            ->get();

        return Excel::download(
            new UsersExport($users),
            'users-' . now()->format('Y-m-d') . '.csv'
        );
    }
}
```

```php
// Another single action controller
namespace App\Http\Controllers\User;

class ImportUsersController extends Controller
{
    public function __construct(
        private UserImportService $importService
    ) {}

    public function __invoke(ImportUsersRequest $request)
    {
        $result = $this->importService->import(
            $request->file('csv')
        );

        return back()->with('success', "{$result->count} users imported");
    }
}
```

```php
// Newsletter sending as single action
namespace App\Http\Controllers\User;

class SendUserNewsletterController extends Controller
{
    public function __invoke(User $user, SendNewsletterRequest $request)
    {
        SendNewsletterJob::dispatch($user, $request->validated());

        return back()->with('success', 'Newsletter queued for sending');
    }
}
```

```php
// Toggle status as single action
namespace App\Http\Controllers\User;

class ToggleUserStatusController extends Controller
{
    public function __invoke(User $user)
    {
        $user->update([
            'is_active' => !$user->is_active,
        ]);

        return back()->with('success', 'User status updated');
    }
}
```

```php
// Clean routes
Route::resource('users', UserController::class);

// Single action routes (no method needed)
Route::get('users/export', ExportUsersController::class)
    ->name('users.export');

Route::post('users/import', ImportUsersController::class)
    ->name('users.import');

Route::post('users/{user}/newsletter', SendUserNewsletterController::class)
    ->name('users.send-newsletter');

Route::patch('users/{user}/toggle-status', ToggleUserStatusController::class)
    ->name('users.toggle-status');
```

```bash
# Generate single action controller
php artisan make:controller ExportUsersController --invokable
```

## Why

- **Single responsibility**: Each controller does exactly one thing
- **Focused testing**: Easy to test one action in isolation
- **Clear naming**: Controller name describes exactly what it does
- **Organization**: Actions grouped in folders by domain
- **Discoverable**: Find the action by its descriptive name
- **Smaller files**: Each controller is small and focused
- **Better routes**: Routes are cleaner without specifying method names
