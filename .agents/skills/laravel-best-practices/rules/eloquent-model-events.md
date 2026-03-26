---
title: Model Events and Observers
impact: HIGH
impactDescription: Clean lifecycle hooks and side effects
tags: eloquent, events, observers, lifecycle
---

## Model Events and Observers

**Impact: HIGH (Clean lifecycle hooks and side effects)**

Use model events and observers to react to model lifecycle changes cleanly.

## Bad Example

```php
// Logic scattered in controllers
class UserController extends Controller
{
    public function store(Request $request)
    {
        $user = User::create($request->validated());

        // Side effects in controller
        $user->profile()->create();
        Mail::to($user)->send(new WelcomeEmail($user));
        Log::info('User created', ['user_id' => $user->id]);
        Cache::forget('users-count');

        return redirect()->route('users.show', $user);
    }

    public function destroy(User $user)
    {
        // Manual cleanup
        $user->posts()->delete();
        $user->comments()->delete();
        Storage::delete($user->avatar_path);
        Cache::forget("user-{$user->id}");

        $user->delete();

        return redirect()->route('users.index');
    }
}
```

## Good Example

```php
// Using model events in the model itself
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class User extends Model
{
    protected static function booted(): void
    {
        // Before creating
        static::creating(function (User $user) {
            $user->uuid = Str::uuid();
            $user->api_token = Str::random(60);
        });

        // After creating
        static::created(function (User $user) {
            $user->profile()->create();
        });

        // Before updating
        static::updating(function (User $user) {
            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }
        });

        // Before deleting
        static::deleting(function (User $user) {
            $user->posts()->delete();
            $user->comments()->delete();
        });

        // After deleting
        static::deleted(function (User $user) {
            Storage::delete($user->avatar_path);
        });
    }
}
```

```php
// Using an Observer for more complex scenarios
namespace App\Observers;

use App\Events\UserRegistered;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserObserver
{
    public function creating(User $user): void
    {
        $user->uuid = Str::uuid();
    }

    public function created(User $user): void
    {
        $user->profile()->create();
        event(new UserRegistered($user));
    }

    public function updating(User $user): void
    {
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }
    }

    public function updated(User $user): void
    {
        Cache::forget("user-{$user->id}");
    }

    public function deleting(User $user): void
    {
        // Cascade soft deletes
        $user->posts()->delete();
    }

    public function deleted(User $user): void
    {
        Storage::delete($user->avatar_path);
        Cache::forget("user-{$user->id}");
        Log::info('User deleted', ['user_id' => $user->id]);
    }

    public function restored(User $user): void
    {
        // Restore related soft deleted records
        $user->posts()->restore();
    }

    public function forceDeleted(User $user): void
    {
        // Permanent deletion cleanup
        $user->posts()->forceDelete();
    }
}
```

```php
// Register observer in AppServiceProvider
class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        User::observe(UserObserver::class);
    }
}
```

```php
// Or use the ObservedBy attribute (Laravel 10+)
namespace App\Models;

use App\Observers\UserObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([UserObserver::class])]
class User extends Model
{
    // ...
}
```

```php
// Clean controller
class UserController extends Controller
{
    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->validated());
        // All side effects handled by observer

        return redirect()->route('users.show', $user);
    }

    public function destroy(User $user)
    {
        $user->delete();
        // Cleanup handled by observer

        return redirect()->route('users.index');
    }
}
```

```php
// Available model events
// creating, created
// updating, updated
// saving, saved (fires for both create and update)
// deleting, deleted
// restoring, restored (for soft deletes)
// forceDeleting, forceDeleted
// replicating
// retrieved
```

## Why

- **Separation of concerns**: Controllers stay focused on HTTP
- **Consistency**: Same behavior regardless of how model is created/updated
- **Single responsibility**: Observer handles all model lifecycle logic
- **Testable**: Can test observer behavior independently
- **DRY**: Side effects defined once, triggered automatically
- **Maintainability**: Easy to find all model-related logic in one place
