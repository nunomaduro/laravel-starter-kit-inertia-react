---
name: taylor-otwell-style
description: >
  Code PHP and Laravel applications in the style of Taylor Otwell — the creator of Laravel.
  Use this skill whenever the user asks to write PHP code, Laravel applications, packages, 
  APIs, services, or any backend code and wants it to follow Laravel conventions, Taylor 
  Otwell's coding philosophy, or "elegant PHP." Trigger on: Laravel development, PHP package 
  creation, API design, service classes, Eloquent models, migrations, controllers, middleware,
  artisan commands, service providers, fluent interfaces, collection pipelines, or any 
  request mentioning "Laravel-style," "expressive syntax," "Taylor Otwell," or "code like 
  Laravel." Also trigger when the user wants to refactor messy PHP into clean, idiomatic 
  Laravel code. Even if the user just says "write this in PHP" — if you can apply Laravel 
  patterns to make it better, consult this skill.
---

# Code Like Taylor Otwell

This skill encodes the coding philosophy, architectural patterns, naming conventions, 
and stylistic choices derived from studying `laravel/laravel` and `laravel/framework` — 
the two canonical repositories authored and maintained by Taylor Otwell.

When using this skill, you are not just writing PHP. You are writing **expressive, elegant 
code** that reads like prose and feels like a joy to use. Every API surface should make 
the developer say: "Of course, that's exactly what I'd expect."

---

## Core Philosophy

Taylor Otwell's design philosophy can be distilled into these principles:

### 1. Developer Happiness Above All
The primary metric is: **Does the developer enjoy using this?** Code should feel intuitive 
before it feels clever. If a developer has to read documentation to understand a method 
name, the method name is wrong.

### 2. Expressive Over Explicit
Prefer `$user->posts()->latest()->get()` over `$userPostRepository->findAllByUserIdOrderedByCreatedAtDesc($userId)`. 
The code should read like natural language.

### 3. Convention Over Configuration
Provide sensible defaults. A model called `User` maps to the `users` table. A controller 
called `PhotoController` handles `/photos`. Make the 80% case require zero configuration.

### 4. Elegance is Not Superficial
Elegant code is not just pretty — it's **discoverable**, **composable**, and **predictable**. 
Each piece should work alone and combine naturally with other pieces.

---

## Architectural Patterns

### The Service Provider Pattern
Everything in Laravel is wired through Service Providers. When building packages or 
modular features, always use this pattern:

```php
class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PaymentGateway::class, function ($app) {
            return new StripeGateway($app['config']['services.stripe.secret']);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        $this->publishes([
            __DIR__.'/../config/payment.php' => config_path('payment.php'),
        ], 'payment-config');
    }
}
```

### The Manager Pattern (Driver Architecture)
When a component can have multiple implementations (cache, queue, mail, filesystem), 
use the Manager pattern — an abstract manager class that creates/caches driver instances:

```php
class NotificationManager extends Manager
{
    /**
     * Get the default driver name.
     */
    public function getDefaultDriver(): string
    {
        return $this->config->get('notifications.default', 'database');
    }

    /**
     * Create the database notification driver.
     */
    protected function createDatabaseDriver(): DatabaseNotifier
    {
        return new DatabaseNotifier($this->container->make('db'));
    }

    /**
     * Create the Slack notification driver.
     */
    protected function createSlackDriver(): SlackNotifier
    {
        return new SlackNotifier(
            $this->config->get('services.slack.webhook_url')
        );
    }
}
```

Key rules:
- Method naming: `create{DriverName}Driver()`
- Always provide `getDefaultDriver()`
- Use `extend()` to allow custom drivers

### The Fluent Builder Pattern
Taylor's signature. Every complex operation should be configurable via chained methods:

```php
// BAD - constructor with many arguments
new Notification('order.shipped', $user, 'mail', true, 5, 'high');

// GOOD - Taylor's way
Notification::make('order.shipped')
    ->to($user)
    ->via('mail')
    ->shouldQueue()
    ->afterCommit()
    ->withPriority('high');
```

### The Pending Object Pattern
When an action has many options, create a "Pending" object that collects configuration 
before executing:

```php
class PendingDispatch
{
    protected $job;

    public function __construct($job)
    {
        $this->job = $job;
    }

    public function onQueue($queue)
    {
        $this->job->onQueue($queue);

        return $this;
    }

    public function delay($delay)
    {
        $this->job->delay($delay);

        return $this;
    }

    public function __destruct()
    {
        app(Dispatcher::class)->dispatch($this->job);
    }
}
```

### Contracts (Interfaces) First
Every major component has a Contract (interface) in `Illuminate\Contracts`. Always 
code to interfaces:

```php
namespace App\Contracts;

interface PaymentGateway
{
    /**
     * Charge the given amount to the payment method.
     *
     * @param  int  $amount
     * @param  string  $paymentMethod
     * @return \App\PaymentResult
     */
    public function charge(int $amount, string $paymentMethod): PaymentResult;
}
```

Contracts are:
- Minimal — only the methods consumers need
- Documented with full PHPDoc
- Stored in a dedicated `Contracts` namespace
- Named as nouns (not `Chargeable`, but `PaymentGateway`)

---

## Naming Conventions

### Classes
| Type | Convention | Example |
|------|-----------|---------|
| Model | Singular StudlyCase | `User`, `OrderItem` |
| Controller | Singular + `Controller` | `PhotoController`, `UserController` |
| Middleware | Descriptive StudlyCase | `EnsureEmailIsVerified`, `ThrottleRequests` |
| Event | Past tense / descriptive | `OrderShipped`, `UserRegistered` |
| Listener | Imperative verb phrase | `SendShipmentNotification` |
| Job | Imperative verb phrase | `ProcessPodcast`, `PruneStaleAttachments` |
| Mail | Noun phrase | `OrderConfirmation`, `WelcomeMessage` |
| Notification | Noun phrase | `InvoicePaid`, `ResetPassword` |
| Policy | Model + `Policy` | `PostPolicy`, `UserPolicy` |
| Request | Verb + Model + `Request` | `StorePostRequest`, `UpdateUserRequest` |
| Resource | Model + `Resource` | `UserResource`, `PostResource` |
| Service Provider | Feature + `ServiceProvider` | `AuthServiceProvider` |
| Trait | Adjective/capability | `HasFactory`, `Notifiable`, `SoftDeletes` |
| Facade | Short, iconic noun | `Cache`, `Route`, `DB`, `Auth` |
| Exception | Descriptive + `Exception` | `ModelNotFoundException` |

### Methods
| Pattern | Convention | Example |
|---------|-----------|---------|
| Getters | No `get` prefix, just the noun | `$user->name()`, `$request->input()` |
| Boolean getters | `is`, `has`, `can`, `should` | `$user->isAdmin()`, `$post->hasComments()` |
| Boolean negative | `doesntContain`, `isNot` | Never `isNotAdmin` — prefer `isNot('admin')` |
| Actions | Simple verbs | `create()`, `store()`, `delete()`, `send()` |
| Transformers | `to` prefix | `toArray()`, `toJson()`, `toSql()` |
| Factory methods | `make`, `from`, `of` | `Collection::make()`, `Carbon::parse()` |
| Fluent setters | Just the noun (returns `$this`) | `->onQueue('high')`, `->delay(60)` |
| Scopes | Descriptive, no `scope` in usage | `User::active()`, `Post::published()` |

### Variables & Properties
```php
// Always descriptive. Never abbreviate unless universally understood.
protected $middleware = [];          // Good
protected $mw = [];                  // Bad

// Collections are always plural
protected $items = [];
protected $drivers = [];

// Single items are singular  
protected $connection;
protected $currentRequest;

// Boolean properties use adjective/state words
public $incrementing = true;
protected $exists = false;
public $wasRecentlyCreated = false;
```

---

## Code Style Rules (from pint.json / StyleCI)

These are non-negotiable in Taylor's codebase:

### Formatting
- **4 spaces** indentation (never tabs)
- Opening braces on **same line** for control structures
- Opening braces on **next line** for class/method definitions (unless single-line signature)
- **Single quotes** for strings (unless the string contains variables/single quotes)
- **Trailing commas** in multiline arrays and argument lists
- **Blank line before `return`** statements
- **No closing `?>` tag** in PHP files
- **Alphabetically ordered imports**, one per line
- `! ` (not operator with space): `! $value` not `!$value`

### PHPDoc
Every public method gets a full docblock:
```php
/**
 * Get the items in the collection that are not present in the given items.
 *
 * @param  \Illuminate\Contracts\Support\Arrayable<array-key, TValue>|iterable<array-key, TValue>  $items
 * @return static
 */
public function diff($items)
{
    return new static(array_diff($this->items, $this->getArrayableItems($items)));
}
```

Rules:
- Summary line ends with a period
- Two spaces between `@param` type and variable name
- `@return` always present on non-void methods
- Use `$this` for fluent returns, `static` for new instances
- Template annotations (`@template TKey`, `@template TValue`) on generic classes
- No `@param` for constructor property promotion (PHP 8.1+)

### Inline Comments
```php
// Taylor writes comments that explain WHY, not WHAT.
// Comments are used sparingly — the code should speak for itself.

// If the given driver has not been created before, we will create the instances
// here and cache it so we can return it next time very quickly. If there is
// already a driver created by this name, we'll just return that instance.
return $this->drivers[$driver] ??= $this->createDriver($driver);
```

---

## Signature Patterns (The "Taylor Fingerprints")

### The `tap()` Helper
Use `tap()` to perform a side effect and return the original value:
```php
return tap(new User($attributes), function ($user) {
    $user->save();
});

// Or with higher-order tap:
return tap($user)->update(['active' => true]);
```

### The `when()` / `unless()` Conditionable Pattern
Any builder or chainable class should use the `Conditionable` trait:
```php
$query->when($request->has('status'), function ($query) use ($request) {
    $query->where('status', $request->status);
});
```

### Higher-Order Messages on Collections
```php
// Instead of:
$users->map(function ($user) {
    return $user->name;
});

// Taylor's way:
$users->map->name;

// Or even:
$users->each->markAsVip();
```

### The `value()` Helper
Accept both values and closures for lazy evaluation:
```php
function value($value, ...$args)
{
    return $value instanceof Closure ? $value(...$args) : $value;
}

// Usage in your own code:
public function getDefault($default = null)
{
    return value($default);
}
```

### The `data_get()` / `data_set()` Dot Notation
Always support dot notation for nested data access:
```php
$name = data_get($payload, 'user.profile.name', 'Unknown');
```

### The `throw_if` / `throw_unless` Guards
```php
throw_if(! $user->isActive(), AuthorizationException::class);
throw_unless($order->isPending(), 'Order is not in pending state.');
```

### Static `make()` Factory Methods
```php
class Notification
{
    public static function make($type): static
    {
        return new static($type);
    }
}
```

### The Macroable Trait
Any class that might benefit from community extension should use `Macroable`:
```php
use Illuminate\Support\Traits\Macroable;

class MyService
{
    use Macroable;
    
    // ...
}

// Developers can then extend it:
MyService::macro('customMethod', function () {
    return 'extended!';
});
```

---

## Eloquent Model Conventions

```php
class Post extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'body',
        'published_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'metadata' => 'array',
            'is_featured' => 'boolean',
        ];
    }

    /**
     * Get the author of the post.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the comments for the post.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Scope a query to only include published posts.
     */
    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at')
              ->where('published_at', '<=', now());
    }

    /**
     * Determine if the post is published.
     */
    public function isPublished(): bool
    {
        return ! is_null($this->published_at) && $this->published_at->isPast();
    }
}
```

Key Eloquent rules:
- Relationship methods always have return type hints
- Scopes use `scope` prefix in definition but are called without it
- Boolean accessors use `is`, `has`, `was` prefixes
- Casts use the `casts()` method (not the `$casts` property) in Laravel 12
- `$fillable` over `$guarded` — be explicit about what's mass-assignable
- Always define `$fillable` as `list<string>` type

---

## Controller Conventions

### Resource Controllers (The Default)
```php
class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('posts.index', [
            'posts' => Post::published()->latest()->paginate(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request): RedirectResponse
    {
        $post = $request->user()->posts()->create(
            $request->validated()
        );

        return to_route('posts.show', $post);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post): View
    {
        return view('posts.show', [
            'post' => $post->load('author', 'comments'),
        ]);
    }
}
```

Rules:
- **Thin controllers** — business logic belongs in models, services, or actions
- Use **route model binding** (`Post $post`) instead of manual `find()`
- Use **Form Request** classes for validation, not inline `$request->validate()`
- Return type hints on all methods
- Prefer `to_route()` over `redirect()->route()`
- Pass data to views as arrays, not `compact()`

### Single Action Controllers
When a controller only does one thing, use `__invoke`:
```php
class ArchivePostController extends Controller
{
    /**
     * Archive the given post.
     */
    public function __invoke(Post $post): RedirectResponse
    {
        $post->archive();

        return to_route('posts.index');
    }
}
```

---

## Migration Conventions

```php
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->string('slug')->unique();
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
```

Rules:
- Anonymous classes (Laravel 9+)
- `foreignId` + `constrained()` instead of manual foreign keys
- Boolean columns prefixed with `is_` or `has_`
- Timestamp columns end with `_at`
- Always include `down()` method

---

## Config File Conventions

```php
return [

    /*
    |--------------------------------------------------------------------------
    | Section Title
    |--------------------------------------------------------------------------
    |
    | A description of what this section configures, written in complete
    | sentences that wrap at roughly 74 characters per line. The decorative
    | banner uses pipe characters and dashes.
    |
    */

    'key' => env('ENV_VARIABLE', 'default_value'),

];
```

The config banner style is a Taylor trademark — always use it for config sections.

---

## Testing Conventions

```php
class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_post(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/posts', [
            'title' => 'My First Post',
            'body' => 'Hello world.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('posts', [
            'title' => 'My First Post',
            'user_id' => $user->id,
        ]);
    }

    public function test_guests_cannot_create_posts(): void
    {
        $response = $this->post('/posts', [
            'title' => 'My First Post',
            'body' => 'Hello world.',
        ]);

        $response->assertRedirect('/login');
    }
}
```

Rules:
- Test method names use `snake_case` with `test_` prefix (Taylor's preference)
- Test names read as sentences: `test_user_can_create_a_post`
- Use factories for test data
- Test behavior, not implementation
- One assertion concept per test (multiple assertions are fine if testing one behavior)
- Use `RefreshDatabase` trait
- Void return types on test methods

---

## Anti-Patterns (What Taylor Would Never Do)

1. **Repository pattern over Eloquent** — Eloquent IS the repository. Don't wrap it.
2. **Hungarian notation** — No `$strName`, `$arrItems`, `$boolActive`.
3. **God services** — If a class has 20+ methods, break it up.
4. **Getter/setter boilerplate** — Use `__get`/`__set` magic, attribute casting, or public properties.
5. **Over-abstraction** — Don't create an interface if there's only ever one implementation.
6. **`else` after `return`** — Use early returns and guard clauses.
7. **Nested callbacks** — Refactor into named methods or use pipeline pattern.
8. **Raw SQL in controllers** — Use Eloquent or Query Builder.
9. **Manual JSON responses** — Use Resources and `response()->json()`.
10. **Env calls outside config** — Always `config('app.name')`, never `env('APP_NAME')` in app code.

---

## The "Would Taylor Approve?" Checklist

Before shipping any code, ask:

- [ ] Can I understand what this code does by reading the method name alone?
- [ ] Does the public API feel like natural English?
- [ ] Are there sensible defaults so the common case needs zero config?
- [ ] Would a developer new to this codebase understand the flow in 5 minutes?
- [ ] Is there a fluent interface where appropriate?
- [ ] Am I using Laravel's built-in features instead of reinventing them?
- [ ] Are my PHPDoc blocks complete and accurate?
- [ ] Does the code follow PSR-12 + Laravel's Pint config?
- [ ] Are my tests testing behavior, not implementation details?
- [ ] Would this spark joy? ✨

---

## Quick Reference: Essential Traits to Use

| Trait | Purpose | Use When |
|-------|---------|----------|
| `Macroable` | Allow runtime extension | Building any service/utility class |
| `Conditionable` | `when()` / `unless()` chaining | Any builder or chainable class |
| `Tappable` | `tap()` support | Any class that benefits from inspection |
| `ForwardsCalls` | Delegate method calls | Decorator/wrapper classes |
| `HasFactory` | Model factories | Every Eloquent model |
| `SoftDeletes` | Soft deletion | When records shouldn't be permanently deleted |
| `Notifiable` | Notification routing | User model and notifiable entities |
| `InteractsWithTime` | `secondsUntil()`, `availableAt()` | Time-based operations |
| `EnumeratesValues` | Collection operations | Custom collection-like classes |

For the full reference on patterns, helpers, and advanced techniques, see `references/advanced-patterns.md`.
