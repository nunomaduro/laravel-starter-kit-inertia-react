# Advanced Taylor Otwell Patterns Reference

## Table of Contents
1. [Collection Pipeline Mastery](#collection-pipeline-mastery)
2. [Custom Casts & Value Objects](#custom-casts--value-objects)
3. [Action Classes](#action-classes)
4. [Pipeline Pattern](#pipeline-pattern)
5. [Event-Driven Architecture](#event-driven-architecture)
6. [Package Development](#package-development)
7. [Artisan Command Style](#artisan-command-style)
8. [Middleware Design](#middleware-design)
9. [Exception Handling](#exception-handling)
10. [Helper Functions](#helper-functions)

---

## Collection Pipeline Mastery

Taylor treats Collections as the primary way to transform data. Every data 
transformation should be a pipeline, not a loop:

```php
// BAD - imperative loop
$activeUserEmails = [];
foreach ($users as $user) {
    if ($user->is_active && $user->email_verified_at) {
        $activeUserEmails[] = strtolower($user->email);
    }
}
sort($activeUserEmails);

// GOOD - collection pipeline
$activeUserEmails = $users
    ->filter(fn ($user) => $user->is_active && $user->email_verified_at)
    ->map(fn ($user) => strtolower($user->email))
    ->sort()
    ->values()
    ->all();
```

### Key Collection Methods Taylor Uses Constantly
```php
// Pluck a key (replaces array_column)
$names = $users->pluck('name');

// Pluck keyed by another column
$emailsByName = $users->pluck('email', 'name');

// Group and transform
$byDepartment = $users->groupBy('department')
    ->map(fn ($group) => $group->sortBy('name')->values());

// Reduce to single value with pipe
$total = $orders->pipe(fn ($orders) => [
    'count' => $orders->count(),
    'total' => $orders->sum('amount'),
    'average' => $orders->avg('amount'),
]);

// MapWithKeys for reshaping
$lookup = $users->mapWithKeys(fn ($user) => [
    $user->email => $user->name,
]);

// Lazy collections for huge datasets
LazyCollection::make(function () {
    $page = 1;
    do {
        $results = Http::get("/api/users?page={$page}")->json('data');
        foreach ($results as $item) {
            yield $item;
        }
        $page++;
    } while (! empty($results));
})->each(function ($user) {
    // Process without loading everything in memory
});
```

---

## Custom Casts & Value Objects

Taylor promotes value objects through Eloquent casts:

```php
class Money implements CastsAttributes
{
    public function __construct(
        protected string $currency = 'USD',
    ) {}

    /**
     * Cast the given value.
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): Money
    {
        return new MoneyValue(
            amount: $value,
            currency: $this->currency,
        );
    }

    /**
     * Prepare the given value for storage.
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): int
    {
        return $value instanceof MoneyValue
            ? $value->amount
            : (int) $value;
    }
}

// Usage on model:
protected function casts(): array
{
    return [
        'price' => Money::class.':USD',
        'discount' => Money::class.':EUR',
    ];
}
```

---

## Action Classes

For complex business logic that doesn't belong in a controller or model, 
Taylor uses single-purpose Action classes:

```php
class CreateOrder
{
    /**
     * Create a new order for the given user.
     */
    public function handle(User $user, Collection $items, ?string $couponCode = null): Order
    {
        return DB::transaction(function () use ($user, $items, $couponCode) {
            $order = $user->orders()->create([
                'total' => $this->calculateTotal($items, $couponCode),
                'status' => OrderStatus::Pending,
            ]);

            $order->items()->createMany(
                $items->map(fn ($item) => [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ])->all()
            );

            event(new OrderCreated($order));

            return $order->load('items.product');
        });
    }

    /**
     * Calculate the order total with optional coupon.
     */
    protected function calculateTotal(Collection $items, ?string $couponCode): int
    {
        $subtotal = $items->sum(fn ($item) => $item['price'] * $item['quantity']);

        if ($couponCode) {
            $discount = Coupon::where('code', $couponCode)->firstOrFail();

            return $discount->apply($subtotal);
        }

        return $subtotal;
    }
}
```

Key rules for actions:
- One public method: `handle()` or `__invoke()`
- Constructor injection for dependencies, method injection for runtime data
- Wrap multi-step operations in `DB::transaction()`
- Fire events for side effects, don't perform them directly

---

## Pipeline Pattern

Taylor uses the Pipeline for sequential transformations:

```php
use Illuminate\Pipeline\Pipeline;

$result = app(Pipeline::class)
    ->send($content)
    ->through([
        RemoveScriptTags::class,
        ConvertMarkdownToHtml::class,
        SanitizeHtml::class,
        AddTableOfContents::class,
    ])
    ->thenReturn();

// Each stage:
class ConvertMarkdownToHtml
{
    public function handle(string $content, Closure $next): mixed
    {
        $html = Str::markdown($content);

        return $next($html);
    }
}
```

---

## Event-Driven Architecture

```php
// Event: simple data object
class OrderShipped
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Order $order,
    ) {}
}

// Listener: single responsibility
class SendShipmentNotification implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(OrderShipped $event): void
    {
        $event->order->user->notify(
            new ShipmentConfirmation($event->order)
        );
    }
}

// Dispatch - clean, one-liner
OrderShipped::dispatch($order);
```

Rules:
- Events are data objects (minimal logic)
- Listeners do one thing
- Queue listeners by default (`ShouldQueue`)
- Use `SerializesModels` for Eloquent models in events
- Constructor property promotion for event properties

---

## Package Development

When building a Laravel package, follow Taylor's structure:

```
my-package/
├── config/
│   └── my-package.php
├── database/
│   └── migrations/
├── resources/
│   └── views/
├── routes/
├── src/
│   ├── Contracts/
│   ├── Events/
│   ├── Exceptions/
│   ├── Facades/
│   │   └── MyPackage.php
│   ├── MyPackageServiceProvider.php
│   └── MyPackage.php
├── tests/
├── composer.json
├── LICENSE.md
└── README.md
```

### The Facade

```php
namespace MyVendor\MyPackage\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \MyVendor\MyPackage\PendingAction doSomething(string $input)
 * @method static void macro(string $name, object|callable $macro)
 *
 * @see \MyVendor\MyPackage\MyPackage
 */
class MyPackage extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return \MyVendor\MyPackage\MyPackage::class;
    }
}
```

---

## Artisan Command Style

```php
class PruneExpiredTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokens:prune
                            {--hours=24 : Number of hours after which tokens expire}
                            {--pretend : Display the number of tokens that would be pruned}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune expired API tokens from the database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hours = (int) $this->option('hours');

        $query = Token::where('created_at', '<', now()->subHours($hours));

        if ($this->option('pretend')) {
            $this->components->info(
                sprintf('%d tokens would be pruned.', $query->count())
            );

            return self::SUCCESS;
        }

        $pruned = $query->delete();

        $this->components->info(
            sprintf('Successfully pruned %d expired tokens.', $pruned)
        );

        return self::SUCCESS;
    }
}
```

Rules:
- Use `$this->components->info()` (not `$this->info()`) for styled output
- Return `self::SUCCESS` or `self::FAILURE` (int constants)
- `--pretend` option is a Taylor pattern for destructive commands
- Descriptive signature with inline documentation

---

## Middleware Design

```php
class EnsureUserIsSubscribed
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$plans): Response
    {
        if (! $request->user()?->subscribedTo(...$plans)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Subscription required.'], 403);
            }

            return redirect()->route('billing');
        }

        return $next($request);
    }
}

// Registration:
Route::middleware('subscribed:pro,enterprise')->group(function () {
    // ...
});
```

---

## Exception Handling

```php
class InsufficientFundsException extends RuntimeException
{
    /**
     * Create a new exception instance.
     */
    public function __construct(
        public readonly int $currentBalance,
        public readonly int $requestedAmount,
    ) {
        parent::__construct(
            "Insufficient funds: requested {$requestedAmount}, available {$currentBalance}."
        );
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(Request $request): Response|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->getMessage(),
                'current_balance' => $this->currentBalance,
                'requested_amount' => $this->requestedAmount,
            ], 422);
        }

        return back()->withErrors([
            'payment' => $this->getMessage(),
        ]);
    }

    /**
     * Get the exception's context information.
     */
    public function context(): array
    {
        return [
            'current_balance' => $this->currentBalance,
            'requested_amount' => $this->requestedAmount,
        ];
    }
}
```

Rules:
- Exceptions can self-render via `render()` method
- Provide `context()` for structured logging
- Constructor property promotion for data
- Handle both JSON and HTML responses

---

## Helper Functions

When creating global helpers (sparingly), follow Taylor's pattern:

```php
if (! function_exists('money')) {
    /**
     * Format a value as money.
     *
     * @param  int  $amount  Amount in cents.
     * @param  string  $currency
     * @return string
     */
    function money(int $amount, string $currency = 'USD'): string
    {
        return Number::currency($amount / 100, $currency);
    }
}
```

Rules:
- Always wrap in `function_exists()` check
- Full PHPDoc with types
- Return type declarations
- Keep it simple — if it needs more than 5 lines, make it a class method

---

## The Stringable Fluent API

Taylor's `Str` class and `Stringable` are models of fluent API design:

```php
// Static utility approach
$slug = Str::slug('Hello World');  // "hello-world"

// Fluent stringable approach
$result = str('Hello World')
    ->slug()
    ->prepend('blog/')
    ->append('.html')
    ->toString();  // "blog/hello-world.html"

// The key: every method returns a Stringable, enabling infinite chaining
$formatted = str($input)
    ->trim()
    ->lower()
    ->replace(' ', '-')
    ->when(
        str($input)->contains('draft'),
        fn ($str) => $str->prepend('[DRAFT] ')
    )
    ->toString();
```

This pattern — static utility class + fluent wrapper — is reusable for 
any domain. Taylor did it with `Str`/`Stringable`, `Number`, `Uri`, and more.
