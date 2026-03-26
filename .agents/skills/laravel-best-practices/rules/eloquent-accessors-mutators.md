---
title: Eloquent Accessors and Mutators
impact: MEDIUM
impactDescription: Clean data transformation at model layer
tags: eloquent, accessors, mutators, attributes
---

## Eloquent Accessors and Mutators

**Impact: MEDIUM (Clean data transformation at model layer)**

Use accessors and mutators to transform attribute values when getting or setting them.

## Bad Example

```php
// Manual transformations scattered in code
class UserController extends Controller
{
    public function show(User $user)
    {
        // Manual formatting everywhere
        $fullName = $user->first_name . ' ' . $user->last_name;
        $formattedPhone = preg_replace('/(\d{3})(\d{3})(\d{4})/', '($1) $2-$3', $user->phone);

        return view('users.show', [
            'fullName' => $fullName,
            'formattedPhone' => $formattedPhone,
        ]);
    }

    public function store(Request $request)
    {
        // Manual normalization
        User::create([
            'email' => strtolower(trim($request->email)),
            'phone' => preg_replace('/[^0-9]/', '', $request->phone),
            'name' => ucwords(strtolower($request->name)),
        ]);
    }
}
```

In views, more manual formatting:

```blade
<p>{{ strtoupper($user->first_name) }} {{ strtoupper($user->last_name) }}</p>
```

## Good Example

```php
// Modern accessors and mutators (Laravel 9+)
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    // Accessor - computed attribute
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->first_name} {$this->last_name}",
        );
    }

    // Mutator - transform on set
    protected function email(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => $value,
            set: fn (string $value) => strtolower(trim($value)),
        );
    }

    // Accessor with formatting
    protected function phone(): Attribute
    {
        return Attribute::make(
            get: function (string $value) {
                return preg_replace('/(\d{3})(\d{3})(\d{4})/', '($1) $2-$3', $value);
            },
            set: function (string $value) {
                return preg_replace('/[^0-9]/', '', $value);
            },
        );
    }

    // Accessor for formatted dates
    protected function birthDate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::parse($value)->format('F j, Y'),
        );
    }

    // Accessor with caching for expensive operations
    protected function profileCompleteness(): Attribute
    {
        return Attribute::make(
            get: function () {
                $fields = ['name', 'email', 'phone', 'avatar', 'bio'];
                $filled = collect($fields)->filter(fn ($field) => !empty($this->$field))->count();
                return ($filled / count($fields)) * 100;
            },
        )->shouldCache();
    }

    // Accessor that depends on relationships
    protected function totalOrders(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->orders()->count(),
        );
    }

    // Make accessors available in JSON/arrays
    protected $appends = ['full_name', 'profile_completeness'];
}

// Usage is clean and consistent
$user = User::find(1);

echo $user->full_name;           // "John Doe"
echo $user->phone;               // "(555) 123-4567"
echo $user->profile_completeness; // 80

// Mutators work automatically on assignment
$user->email = '  JOHN@EXAMPLE.COM  ';
$user->save();
// Stored as: john@example.com

```

In views — just use attributes directly:

```blade
<p>{{ $user->full_name }}</p>
<p>{{ $user->phone }}</p>
```

```php
// Legacy syntax (still works but not recommended for new code)
class User extends Model
{
    // Legacy accessor
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // Legacy mutator
    public function setEmailAttribute(string $value): void
    {
        $this->attributes['email'] = strtolower(trim($value));
    }
}
```

## Why

- **Consistency**: Data transformation happens in one place
- **Clean code**: No repeated formatting logic in views or controllers
- **Encapsulation**: Models handle their own data presentation
- **Automatic**: Works on assignment and retrieval without extra code
- **Computed attributes**: Create virtual attributes from existing data
- **Serialization**: Appended accessors included in JSON automatically
