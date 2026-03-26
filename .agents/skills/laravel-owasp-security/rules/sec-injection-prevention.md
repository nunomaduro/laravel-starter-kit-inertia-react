---
title: Prevent SQL Injection and Mass Assignment
impact: CRITICAL
impactDescription: Prevents database compromise and unauthorized field manipulation
tags: security, sql-injection, mass-assignment, eloquent, owasp-a03
---

## Prevent SQL Injection and Mass Assignment

**Impact: CRITICAL (Prevents database compromise and unauthorized field manipulation)**

## Why It Matters

- **Risk (SQL Injection)**: Attackers read, modify, or delete any data in the database, or execute OS commands, by injecting SQL into raw queries
- **Risk (Mass Assignment)**: Attackers set fields they should not have access to — `is_admin`, `role`, `user_id` — by sending extra POST parameters
- **Impact**: Full database exfiltration, privilege escalation, unauthorized data modification
- **OWASP**: A03:2021 — Injection

## Incorrect — SQL Injection

```php
<?php

// ❌ String concatenation in raw query — SQL injectable
class ClassController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->input('sort');

        // Attacker sends: sort=name; DROP TABLE users; --
        $classes = DB::select("SELECT * FROM classes ORDER BY {$sort}");
    }
}
```

```php
<?php

// ❌ User input in whereRaw without binding
$classes = Class::whereRaw("name LIKE '%" . $request->search . "%'")->get();
// Attacker sends: search=' OR '1'='1
```

```php
<?php

// ❌ orderByRaw with unvalidated user input
$results = Payment::orderByRaw($request->input('column') . ' ' . $request->input('direction'))->get();
```

**Problems:**
- Any user input concatenated into a SQL string is injectable
- `whereRaw`, `selectRaw`, `orderByRaw` all pass directly to the database engine
- Attacker can exfiltrate all data, bypass WHERE clauses, or run destructive queries

## Correct — SQL Injection Prevention

```php
<?php

declare(strict_types=1);

// ✅ Use parameterized bindings in raw queries
$classes = DB::select('SELECT * FROM classes WHERE status = ?', [$request->status]);

// ✅ Named bindings
$classes = DB::select(
    'SELECT * FROM classes WHERE status = :status AND teacher_id = :teacher',
    ['status' => $request->status, 'teacher' => auth()->id()],
);

// ✅ whereRaw with bindings
$classes = Class::whereRaw('name LIKE ?', ['%' . $request->search . '%'])->get();

// ✅ Whitelist-validate column names before use in orderByRaw
$allowedColumns = ['name', 'created_at', 'price'];
$allowedDirections = ['asc', 'desc'];

$column    = in_array($request->column, $allowedColumns) ? $request->column : 'created_at';
$direction = in_array($request->direction, $allowedDirections) ? $request->direction : 'asc';

$payments = Payment::orderByRaw("{$column} {$direction}")->get();
```

## Incorrect — Mass Assignment

```php
<?php

// ❌ $guarded = [] — all fields are mass assignable, including is_admin, role
class User extends Model
{
    protected $guarded = [];
}

// Attacker sends POST: { "name": "John", "is_admin": true }
User::create($request->all());  // is_admin is now set to true
```

```php
<?php

// ❌ $request->all() passed directly to create/update
class PostController extends Controller
{
    public function store(Request $request)
    {
        Post::create($request->all());  // Any field can be set by the attacker
    }
}
```

```php
<?php

// ❌ forceFill with unvalidated user input
$user->forceFill($request->all())->save();  // Bypasses $fillable entirely
```

**Problems:**
- `$guarded = []` allows attackers to set any field including `is_admin`, `role`, `user_id`
- `$request->all()` passes every submitted parameter directly to the model
- `forceFill` with unvalidated data completely bypasses Laravel's mass assignment protection

## Correct — Mass Assignment Prevention

```php
<?php

declare(strict_types=1);

// ✅ Define $fillable explicitly — only user-submittable fields
class Post extends Model
{
    protected $fillable = [
        'title',
        'body',
        'category_id',
    ];
    // user_id, published_at, is_featured are NOT in fillable
}
```

```php
<?php

declare(strict_types=1);

// ✅ Always use $request->validated() — only validated fields pass through
class PostController extends Controller
{
    public function store(StorePostRequest $request): RedirectResponse
    {
        $post = Post::create([
            ...$request->validated(),
            'user_id' => auth()->id(),  // Set sensitive fields explicitly
        ]);

        return redirect()->route('posts.show', $post);
    }
}

class StorePostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'body'        => ['required', 'string'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            // user_id is NOT here — cannot be submitted by attackers
        ];
    }
}
```

```php
<?php

declare(strict_types=1);

// ✅ forceFill only with hardcoded fields — never with user input
class NewPasswordController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        // SAFE — hardcoded field list, not user-controlled
        $user->forceFill([
            'password'       => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();
    }
}
```

## Recommended Patterns

| Pattern | Use Case |
|---------|----------|
| `whereRaw('col = ?', [$value])` | Raw SQL with user input |
| Whitelist array for column names | Dynamic ORDER BY / WHERE column |
| `$fillable = ['field1', 'field2']` | All models — explicit allowlist |
| `$request->validated()` | Mass operations in controllers |
| Set `user_id` explicitly | Ownership fields — never in fillable |

Reference: [OWASP Laravel Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Laravel_Cheat_Sheet.html) | [Laravel Eloquent Mass Assignment](https://laravel.com/docs/13.x/eloquent#mass-assignment)
