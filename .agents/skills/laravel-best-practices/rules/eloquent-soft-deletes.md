---
title: Soft Deletes
impact: MEDIUM
impactDescription: Safe deletion with recovery option
tags: eloquent, soft-deletes, safety
---

## Soft Deletes

**Impact: MEDIUM (Safe deletion with recovery option)**

Use soft deletes to retain data while hiding it from normal queries, enabling recovery and audit trails.

## Bad Example

```php
// Hard delete with no recovery option
class UserController extends Controller
{
    public function destroy(User $user)
    {
        $user->delete(); // Gone forever

        return redirect()->route('users.index');
    }
}

// Manual "soft delete" implementation
class User extends Model
{
    public function softDelete()
    {
        $this->update(['is_deleted' => true]);
    }
}

// Every query needs to filter deleted records
$users = User::where('is_deleted', false)->get();
$activeUsers = User::where('is_deleted', false)->where('status', 'active')->get();
```

## Good Example

```php
// Enable soft deletes on model
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use SoftDeletes;

    // Optionally customize the column name
    // const DELETED_AT = 'archived_at';
}
```

```php
// Migration with soft deletes
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamps();
    $table->softDeletes(); // Adds deleted_at column
});
```

```php
// Usage
$user = User::find(1);
$user->delete(); // Sets deleted_at, doesn't actually delete

// Normal queries automatically exclude soft deleted
$users = User::all(); // Only non-deleted users

// Include soft deleted records
$allUsers = User::withTrashed()->get();

// Only soft deleted records
$deletedUsers = User::onlyTrashed()->get();

// Check if model is soft deleted
if ($user->trashed()) {
    // User has been soft deleted
}

// Restore a soft deleted model
$user->restore();

// Permanently delete
$user->forceDelete();

// Restore through a query
User::withTrashed()
    ->where('deleted_at', '>', now()->subMonth())
    ->restore();
```

```php
// Soft delete cascading
class User extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            $user->posts()->delete(); // Soft delete related posts
        });

        static::restoring(function (User $user) {
            $user->posts()->restore(); // Restore related posts
        });
    }
}
```

```php
// Controller with soft delete support
class UserController extends Controller
{
    public function index()
    {
        $users = User::paginate(20);

        return view('users.index', compact('users'));
    }

    public function trash()
    {
        $users = User::onlyTrashed()->paginate(20);

        return view('users.trash', compact('users'));
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User moved to trash');
    }

    public function restore(int $id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        return redirect()->route('users.index')
            ->with('success', 'User restored');
    }

    public function forceDelete(int $id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->forceDelete();

        return redirect()->route('users.trash')
            ->with('success', 'User permanently deleted');
    }
}
```

```php
// Route model binding with trashed
Route::get('/users/{user}', [UserController::class, 'show'])
    ->withTrashed(); // Will resolve even if soft deleted

// Unique validation with soft deletes
'email' => [
    'required',
    'email',
    Rule::unique('users')->withoutTrashed(),
],
```

## Why

- **Data recovery**: Accidentally deleted records can be restored
- **Audit trail**: Keep history of deleted records
- **Referential integrity**: Related records can reference soft deleted records
- **Compliance**: Some regulations require data retention
- **User experience**: "Undo" functionality becomes possible
- **Safe defaults**: Normal queries automatically exclude deleted records
