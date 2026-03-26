---
title: Protect Against Mass Assignment
impact: HIGH
impactDescription: Prevents security vulnerabilities
tags: security, mass-assignment, eloquent, protection
---

## Protect Against Mass Assignment

**Impact: HIGH (Prevents security vulnerabilities)**

## Why It Matters

Mass assignment vulnerabilities allow attackers to modify database fields they shouldn't have access to. A malicious user could set `is_admin=1` or `role=admin` if those fields aren't protected.

## Bad Example

```php
// No protection - allows any field to be mass assigned
class User extends Model
{
    protected $guarded = [];  // DANGEROUS!
}

// Attacker can POST: { "email": "test@test.com", "is_admin": true }
User::create($request->all());  // is_admin is set!
```

```php
// Using $request->all() with fillable
class User extends Model
{
    protected $fillable = ['name', 'email', 'password'];
}

// Still dangerous if you accidentally expand fillable
User::create($request->all());
```

```php
// Overly permissive fillable
class Post extends Model
{
    protected $fillable = [
        'title',
        'body',
        'user_id',      // Dangerous! User could change author
        'published_at', // Dangerous! User could bypass moderation
    ];
}
```

## Good Example

### Use $fillable Restrictively

```php
// Only include user-submittable fields
class Post extends Model
{
    protected $fillable = [
        'title',
        'body',
        'category_id',
    ];
}

// Set sensitive fields explicitly
$post = new Post($request->validated());
$post->user_id = auth()->id();
$post->save();
```

### Use Form Request validated()

```php
// Only use validated data
class PostController extends Controller
{
    public function store(StorePostRequest $request)
    {
        // Only fields from rules() are included
        $post = Post::create($request->validated());
    }
}

// Form Request controls what's allowed
class StorePostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            // user_id is NOT here - can't be submitted
        ];
    }
}
```

### Set Sensitive Fields Manually

```php
// Set sensitive fields explicitly
public function store(StorePostRequest $request)
{
    $post = Post::create([
        ...$request->validated(),
        'user_id' => auth()->id(),
        'status' => 'draft',
    ]);
}

// Or use tap
public function store(StorePostRequest $request)
{
    $post = tap(new Post($request->validated()), function ($post) {
        $post->user_id = auth()->id();
        $post->published_at = null;
        $post->save();
    });
}
```

### Different Fillable for Different Actions

```php
class Post extends Model
{
    protected $fillable = [
        'title',
        'body',
        'category_id',
    ];

    // Admin can fill more fields
    public function fillableByAdmin(): array
    {
        return [
            'title',
            'body',
            'category_id',
            'user_id',
            'published_at',
            'featured',
        ];
    }
}

// In admin controller
public function store(AdminStorePostRequest $request)
{
    $post = new Post();
    $post->forceFill($request->validated())->save();
}
```

### Use $guarded for Simple Models

```php
// Guard only the sensitive fields
class Category extends Model
{
    // These fields cannot be mass assigned
    protected $guarded = ['id', 'created_at', 'updated_at'];

    // Everything else is fillable
}
```

### Never Use in Production

```php
// NEVER do this in production
protected $guarded = [];

// NEVER do this
Model::unguard();
Post::create($request->all());
Model::reguard();
```

## Recommended Patterns

| Pattern | Use Case |
|---------|----------|
| `$fillable` + `validated()` | Most models |
| `$guarded` for sensitive fields | Simple models with few sensitive fields |
| Manual assignment | Sensitive fields like user_id, role |
| `forceFill()` | Admin operations with extra validation |

## Testing for Vulnerabilities

```php
// Test that mass assignment is protected
public function test_cannot_mass_assign_user_id()
{
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $this->actingAs($user)
        ->post('/posts', [
            'title' => 'Test',
            'body' => 'Content',
            'user_id' => $otherUser->id,  // Attempting to assign to other user
        ]);

    $post = Post::first();
    $this->assertEquals($user->id, $post->user_id);  // Should be current user
}
```
