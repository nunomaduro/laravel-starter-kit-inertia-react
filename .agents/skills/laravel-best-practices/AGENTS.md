# Laravel 13 Best Practices - Complete Guide

**Version:** 2.1.0
**Laravel Version:** 13.x
**PHP Version:** 8.3+
**Organization:** Laravel Community
**Date:** March 2026

## Overview

Comprehensive best practices guide for Laravel 13 applications, designed for AI agents and LLMs. Contains 31 rules across 7 categories, prioritized by impact from critical (architecture and database patterns) to incremental (performance optimization). Each rule includes detailed explanations, real-world examples comparing incorrect vs. correct implementations using PHP 8.3 and Laravel 13 features, and specific impact metrics to guide automated refactoring and code generation.

### Key Features

- Service classes for business logic separation
- Eager loading to prevent N+1 queries
- Form request classes for validation
- Resource controllers following REST conventions
- Eloquent relationships and query scopes
- Mass assignment protection
- API resources for response transformation
- Modern PHP 8.3 syntax (readonly properties, constructor promotion)
- Laravel 13 patterns and conventions

## Categories

This guide is organized into 7 categories, prioritized by their impact on application quality:

1. **Architecture & Structure (CRITICAL)** - Foundational patterns for organizing Laravel applications
2. **Eloquent & Database (CRITICAL)** - Efficient database operations and ORM usage
3. **Controllers & Routing (HIGH)** - RESTful conventions and proper request handling
4. **Validation & Requests (HIGH)** - Form request classes and validation patterns
5. **Security (HIGH)** - Protection against common vulnerabilities
6. **Performance (MEDIUM)** - Caching strategies and optimization techniques
7. **API Design (MEDIUM)** - RESTful API patterns and resource transformers

### References

- [Laravel 13 Documentation](https://laravel.com/docs/13.x)
- [Laravel Eloquent](https://laravel.com/docs/13.x/eloquent)
- [Laravel Controllers](https://laravel.com/docs/13.x/controllers)
- [Laravel Validation](https://laravel.com/docs/13.x/validation)
- [PHP Type Declarations](https://php.net/manual/en/language.types.declarations.php)

---

## 1. Architecture & Structure (CRITICAL)

**Impact:** CRITICAL  
**Description:** Foundational patterns for organizing Laravel applications. Service classes, action classes, DTOs, and proper separation of concerns are essential for maintainable, scalable codebases. These patterns determine long-term code quality and team productivity.

**Rules in this category:** 7

---

## Value Objects

**Impact: MEDIUM (Enforces business rules and improves type safety)**

Encapsulate domain concepts with value objects to enforce business rules and improve type safety.

## Bad Example

```php
// Primitive obsession - using strings/numbers for domain concepts
class User extends Model
{
    public function setEmailAttribute(string $value): void
    {
        // Validation scattered across the codebase
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email');
        }
        $this->attributes['email'] = strtolower($value);
    }
}

class Order extends Model
{
    public function calculateTotal(): float
    {
        // Money as float - precision issues
        return $this->subtotal + $this->tax - $this->discount;
    }

    public function applyDiscount(float $amount): void
    {
        // No validation of negative values
        $this->discount = $amount;
    }
}

// Phone number without structure
$user->phone = '+1-555-123-4567';
// Later...
$cleanPhone = preg_replace('/[^0-9]/', '', $user->phone); // Manual parsing
```

## Good Example

```php
// Email value object
namespace App\ValueObjects;

use InvalidArgumentException;

readonly class Email
{
    public function __construct(
        private string $value
    ) {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email address: {$value}");
        }
    }

    public function value(): string
    {
        return strtolower($this->value);
    }

    public function domain(): string
    {
        return substr($this->value, strpos($this->value, '@') + 1);
    }

    public function equals(Email $other): bool
    {
        return $this->value() === $other->value();
    }

    public function __toString(): string
    {
        return $this->value();
    }
}

// Money value object with proper precision
namespace App\ValueObjects;

use InvalidArgumentException;

readonly class Money
{
    public function __construct(
        private int $cents,
        private string $currency = 'USD'
    ) {
        if ($cents < 0) {
            throw new InvalidArgumentException('Money cannot be negative');
        }
    }

    public static function fromDollars(float $dollars, string $currency = 'USD'): self
    {
        return new self((int) round($dollars * 100), $currency);
    }

    public function cents(): int
    {
        return $this->cents;
    }

    public function dollars(): float
    {
        return $this->cents / 100;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function add(Money $other): self
    {
        $this->ensureSameCurrency($other);
        return new self($this->cents + $other->cents, $this->currency);
    }

    public function subtract(Money $other): self
    {
        $this->ensureSameCurrency($other);
        return new self($this->cents - $other->cents, $this->currency);
    }

    public function multiply(float $factor): self
    {
        return new self((int) round($this->cents * $factor), $this->currency);
    }

    public function format(): string
    {
        return number_format($this->dollars(), 2) . ' ' . $this->currency;
    }

    private function ensureSameCurrency(Money $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Cannot operate on different currencies');
        }
    }
}

// Phone number value object
namespace App\ValueObjects;

readonly class PhoneNumber
{
    public function __construct(
        private string $countryCode,
        private string $number
    ) {}

    public static function fromString(string $phone): self
    {
        $cleaned = preg_replace('/[^0-9+]/', '', $phone);

        if (str_starts_with($cleaned, '+')) {
            $countryCode = substr($cleaned, 0, 2);
            $number = substr($cleaned, 2);
        } else {
            $countryCode = '+1';
            $number = $cleaned;
        }

        return new self($countryCode, $number);
    }

    public function countryCode(): string
    {
        return $this->countryCode;
    }

    public function number(): string
    {
        return $this->number;
    }

    public function format(): string
    {
        return sprintf('%s-%s-%s',
            $this->countryCode,
            substr($this->number, 0, 3),
            substr($this->number, 3)
        );
    }

    public function __toString(): string
    {
        return $this->countryCode . $this->number;
    }
}

// Using value objects with Eloquent casts
namespace App\Casts;

use App\ValueObjects\Email;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class EmailCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): ?Email
    {
        return $value ? new Email($value) : null;
    }

    public function set($model, string $key, $value, array $attributes): ?string
    {
        if ($value instanceof Email) {
            return $value->value();
        }

        return $value ? (new Email($value))->value() : null;
    }
}

// Model using value objects
class User extends Model
{
    protected $casts = [
        'email' => EmailCast::class,
    ];
}

// Usage
$user = new User();
$user->email = new Email('John@Example.com');
echo $user->email->domain(); // example.com

$price = Money::fromDollars(99.99);
$tax = $price->multiply(0.1);
$total = $price->add($tax);
echo $total->format(); // 109.99 USD
```

## Why

- **Encapsulation**: Business rules live with the data they validate
- **Type safety**: Cannot pass wrong type accidentally
- **Immutability**: Value objects are safer to pass around
- **Self-validating**: Invalid states cannot exist
- **Domain clarity**: Code speaks the language of the business
- **Reusability**: Same value object used consistently everywhere

---

## Single-Purpose Action Classes

**Impact: HIGH (Improves reusability and testability)**

Use single-purpose action classes for discrete operations to achieve maximum reusability and testability.

## Bad Example

```php
// Service class doing too many things
class UserService
{
    public function register(array $data): User
    {
        // Registration logic...
    }

    public function updateProfile(User $user, array $data): User
    {
        // Profile update logic...
    }

    public function deactivate(User $user): void
    {
        // Deactivation logic...
    }

    public function sendWelcomeEmail(User $user): void
    {
        // Email logic...
    }

    public function calculateStats(User $user): array
    {
        // Stats calculation...
    }
}
```

## Good Example

```php
// Single-purpose action class
namespace App\Actions\User;

use App\Models\User;
use App\Events\UserRegistered;
use Illuminate\Support\Facades\Hash;

class RegisterUserAction
{
    public function __construct(
        private SendWelcomeEmailAction $sendWelcomeEmail,
    ) {}

    public function execute(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        event(new UserRegistered($user));
        $this->sendWelcomeEmail->execute($user);

        return $user;
    }
}

// Another focused action
namespace App\Actions\User;

use App\Models\User;
use App\Notifications\WelcomeNotification;

class SendWelcomeEmailAction
{
    public function execute(User $user): void
    {
        $user->notify(new WelcomeNotification());
    }
}

// Action for profile updates
namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

class UpdateUserProfileAction
{
    public function execute(User $user, array $data): User
    {
        if (isset($data['avatar'])) {
            $data['avatar_path'] = $this->storeAvatar($data['avatar']);
            unset($data['avatar']);
        }

        $user->update($data);

        return $user->fresh();
    }

    private function storeAvatar($avatar): string
    {
        return Storage::disk('public')->put('avatars', $avatar);
    }
}

// Controller using actions
class UserController extends Controller
{
    public function store(
        RegisterUserRequest $request,
        RegisterUserAction $action
    ) {
        $user = $action->execute($request->validated());

        return new UserResource($user);
    }

    public function update(
        UpdateUserProfileRequest $request,
        UpdateUserProfileAction $action
    ) {
        $user = $action->execute(
            auth()->user(),
            $request->validated()
        );

        return new UserResource($user);
    }
}
```

## Why

- **Single responsibility**: Each action does exactly one thing
- **Highly testable**: Small, focused units are easy to test
- **Reusable**: Actions can be called from controllers, jobs, commands, or other actions
- **Self-documenting**: Action names clearly describe what they do
- **Easy to find**: Organized by domain in the Actions folder
- **Composable**: Actions can be combined to build complex workflows

---

## Repository Pattern

**Impact: MEDIUM (Abstracts data access from business logic)**

Abstract database queries into repository classes to decouple business logic from data access.

## Bad Example

```php
// Eloquent queries scattered in controllers
class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query()
            ->when($request->category, fn($q, $cat) => $q->where('category_id', $cat))
            ->when($request->min_price, fn($q, $price) => $q->where('price', '>=', $price))
            ->when($request->max_price, fn($q, $price) => $q->where('price', '<=', $price))
            ->when($request->search, fn($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->with(['category', 'reviews'])
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('products.index', compact('products'));
    }

    public function featured()
    {
        // Same complex query duplicated
        $products = Product::query()
            ->where('is_featured', true)
            ->where('is_active', true)
            ->with(['category', 'reviews'])
            ->orderBy('featured_at', 'desc')
            ->take(10)
            ->get();

        return view('products.featured', compact('products'));
    }
}
```

## Good Example

```php
// Repository interface
namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ProductRepositoryInterface
{
    public function find(int $id): ?Product;
    public function findOrFail(int $id): Product;
    public function all(): Collection;
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function search(array $filters): LengthAwarePaginator;
    public function featured(int $limit = 10): Collection;
    public function create(array $data): Product;
    public function update(Product $product, array $data): Product;
    public function delete(Product $product): bool;
}

// Repository implementation
namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        private Product $model
    ) {}

    public function find(int $id): ?Product
    {
        return $this->model->find($id);
    }

    public function findOrFail(int $id): Product
    {
        return $this->model->findOrFail($id);
    }

    public function all(): Collection
    {
        return $this->model->active()->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->active()
            ->with(['category', 'reviews'])
            ->latest()
            ->paginate($perPage);
    }

    public function search(array $filters): LengthAwarePaginator
    {
        return $this->model
            ->active()
            ->when(
                $filters['category'] ?? null,
                fn($q, $cat) => $q->where('category_id', $cat)
            )
            ->when(
                $filters['min_price'] ?? null,
                fn($q, $price) => $q->where('price', '>=', $price)
            )
            ->when(
                $filters['max_price'] ?? null,
                fn($q, $price) => $q->where('price', '<=', $price)
            )
            ->when(
                $filters['search'] ?? null,
                fn($q, $search) => $q->where('name', 'like', "%{$search}%")
            )
            ->with(['category', 'reviews'])
            ->latest()
            ->paginate($filters['per_page'] ?? 20);
    }

    public function featured(int $limit = 10): Collection
    {
        return $this->model
            ->active()
            ->featured()
            ->with(['category', 'reviews'])
            ->orderBy('featured_at', 'desc')
            ->take($limit)
            ->get();
    }

    public function create(array $data): Product
    {
        return $this->model->create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        return $product->fresh();
    }

    public function delete(Product $product): bool
    {
        return $product->delete();
    }
}

// Bind in service provider
namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\Contracts\ProductRepositoryInterface::class,
            \App\Repositories\ProductRepository::class
        );
    }
}

// Clean controller
class ProductController extends Controller
{
    public function __construct(
        private ProductRepositoryInterface $products
    ) {}

    public function index(Request $request)
    {
        $products = $this->products->search($request->all());

        return view('products.index', compact('products'));
    }

    public function featured()
    {
        $products = $this->products->featured();

        return view('products.featured', compact('products'));
    }
}
```

## Why

- **Testability**: Easy to mock the repository interface in tests
- **Reusability**: Same queries used consistently across the application
- **Maintainability**: Query logic changes in one place
- **Abstraction**: Business logic doesn't depend on Eloquent specifics
- **Swappable**: Can swap implementations (e.g., cache decorator, different database)
- **Clean controllers**: Controllers only handle HTTP concerns

---

## Feature Folders (Domain-Driven Structure)

**Impact: MEDIUM (Better cohesion and discoverability)**

Organize code by feature/domain rather than by type for better cohesion and discoverability.

## Bad Example

```
app/
├── Http/
│   └── Controllers/
│       ├── OrderController.php
│       ├── ProductController.php
│       ├── UserController.php
│       ├── CartController.php
│       ├── PaymentController.php
│       └── ShippingController.php
├── Models/
│   ├── Order.php
│   ├── OrderItem.php
│   ├── Product.php
│   ├── User.php
│   ├── Cart.php
│   └── Payment.php
├── Services/
│   ├── OrderService.php
│   ├── ProductService.php
│   ├── CartService.php
│   ├── PaymentService.php
│   └── ShippingService.php
├── Repositories/
│   ├── OrderRepository.php
│   ├── ProductRepository.php
│   └── UserRepository.php
├── Events/
│   ├── OrderPlaced.php
│   ├── OrderShipped.php
│   ├── ProductCreated.php
│   └── PaymentProcessed.php
├── Listeners/
│   ├── SendOrderConfirmation.php
│   ├── UpdateInventory.php
│   └── NotifyShipping.php
└── Requests/
    ├── StoreOrderRequest.php
    ├── UpdateOrderRequest.php
    ├── StoreProductRequest.php
    └── UpdateProductRequest.php
```

## Good Example

```
app/
├── Domain/
│   ├── Order/
│   │   ├── Actions/
│   │   │   ├── CreateOrderAction.php
│   │   │   ├── CancelOrderAction.php
│   │   │   └── RefundOrderAction.php
│   │   ├── DTOs/
│   │   │   ├── CreateOrderDTO.php
│   │   │   └── OrderItemDTO.php
│   │   ├── Events/
│   │   │   ├── OrderPlaced.php
│   │   │   ├── OrderCancelled.php
│   │   │   └── OrderShipped.php
│   │   ├── Listeners/
│   │   │   ├── SendOrderConfirmation.php
│   │   │   └── UpdateInventory.php
│   │   ├── Models/
│   │   │   ├── Order.php
│   │   │   └── OrderItem.php
│   │   ├── Policies/
│   │   │   └── OrderPolicy.php
│   │   ├── Repositories/
│   │   │   ├── OrderRepositoryInterface.php
│   │   │   └── OrderRepository.php
│   │   └── Services/
│   │       └── OrderService.php
│   │
│   ├── Product/
│   │   ├── Actions/
│   │   │   ├── CreateProductAction.php
│   │   │   └── UpdateStockAction.php
│   │   ├── Models/
│   │   │   ├── Product.php
│   │   │   └── Category.php
│   │   ├── Repositories/
│   │   │   └── ProductRepository.php
│   │   └── Services/
│   │       └── ProductService.php
│   │
│   ├── Payment/
│   │   ├── Actions/
│   │   │   ├── ProcessPaymentAction.php
│   │   │   └── RefundPaymentAction.php
│   │   ├── Contracts/
│   │   │   └── PaymentGatewayInterface.php
│   │   ├── Gateways/
│   │   │   ├── StripeGateway.php
│   │   │   └── PayPalGateway.php
│   │   └── Models/
│   │       └── Payment.php
│   │
│   └── User/
│       ├── Actions/
│       │   ├── RegisterUserAction.php
│       │   └── UpdateProfileAction.php
│       ├── Models/
│       │   └── User.php
│       └── Services/
│           └── UserService.php
│
├── Http/
│   ├── Controllers/
│   │   ├── Order/
│   │   │   └── OrderController.php
│   │   ├── Product/
│   │   │   └── ProductController.php
│   │   └── User/
│   │       └── UserController.php
│   └── Requests/
│       ├── Order/
│       │   ├── StoreOrderRequest.php
│       │   └── UpdateOrderRequest.php
│       └── Product/
│           └── StoreProductRequest.php
│
└── Infrastructure/
    ├── Providers/
    │   ├── OrderServiceProvider.php
    │   └── PaymentServiceProvider.php
    └── Caching/
        └── CacheManager.php
```

```php
// Domain service provider for registering domain bindings
namespace App\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Order\Repositories\OrderRepository;

class OrderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            OrderRepositoryInterface::class,
            OrderRepository::class
        );
    }

    public function boot(): void
    {
        // Register order-related event listeners
        Event::listen(
            OrderPlaced::class,
            [SendOrderConfirmation::class, UpdateInventory::class]
        );
    }
}

// Autoload in composer.json
{
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Domain\\": "app/Domain/"
        }
    }
}
```

## Why

- **Discoverability**: All related code in one place
- **Cohesion**: High cohesion within feature, low coupling between features
- **Team scalability**: Teams can own entire features
- **Bounded contexts**: Clear boundaries between domains
- **Refactoring**: Easy to extract features into packages/microservices
- **Navigation**: Quickly find all code related to a feature
- **Independence**: Features can evolve independently

---

## Event-Driven Architecture

**Impact: HIGH (Decouples components and enables async processing)**

Use events and listeners to decouple components and handle side effects asynchronously.

## Bad Example

```php
// Tightly coupled code with mixed concerns
class OrderController extends Controller
{
    public function store(StoreOrderRequest $request)
    {
        $order = Order::create($request->validated());

        // Side effects directly in controller
        Mail::to($order->user)->send(new OrderConfirmation($order));

        // Inventory update
        foreach ($order->items as $item) {
            $item->product->decrement('stock', $item->quantity);
        }

        // Analytics tracking
        Analytics::track('order_placed', [
            'order_id' => $order->id,
            'total' => $order->total,
        ]);

        // Notify admin
        $admin = User::where('role', 'admin')->first();
        Notification::send($admin, new NewOrderNotification($order));

        // Update customer loyalty points
        $order->user->increment('loyalty_points', $order->total / 10);

        // Sync to external systems
        Http::post('https://crm.example.com/orders', $order->toArray());
        Http::post('https://shipping.example.com/orders', $order->toArray());

        return redirect()->route('orders.show', $order);
    }
}
```

## Good Example

```php
// Event class
namespace App\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;

class OrderPlaced
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Order $order
    ) {}
}

// Listeners for different concerns
namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Mail\OrderConfirmation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOrderConfirmation implements ShouldQueue
{
    public function handle(OrderPlaced $event): void
    {
        Mail::to($event->order->user)->send(
            new OrderConfirmation($event->order)
        );
    }
}

class UpdateInventory implements ShouldQueue
{
    public function handle(OrderPlaced $event): void
    {
        foreach ($event->order->items as $item) {
            $item->product->decrement('stock', $item->quantity);
        }
    }
}

class TrackOrderAnalytics implements ShouldQueue
{
    public function handle(OrderPlaced $event): void
    {
        Analytics::track('order_placed', [
            'order_id' => $event->order->id,
            'total' => $event->order->total,
            'items_count' => $event->order->items->count(),
        ]);
    }
}

class NotifyAdminOfNewOrder implements ShouldQueue
{
    public function handle(OrderPlaced $event): void
    {
        $admins = User::where('role', 'admin')->get();
        Notification::send($admins, new NewOrderNotification($event->order));
    }
}

class UpdateLoyaltyPoints implements ShouldQueue
{
    public function handle(OrderPlaced $event): void
    {
        $points = (int) ($event->order->total / 10);
        $event->order->user->increment('loyalty_points', $points);
    }
}

class SyncOrderToExternalSystems implements ShouldQueue
{
    public $tries = 3;
    public $backoff = [10, 60, 300];

    public function handle(OrderPlaced $event): void
    {
        $this->syncToCRM($event->order);
        $this->syncToShipping($event->order);
    }

    private function syncToCRM(Order $order): void
    {
        Http::post('https://crm.example.com/orders', $order->toArray());
    }

    private function syncToShipping(Order $order): void
    {
        Http::post('https://shipping.example.com/orders', $order->toArray());
    }
}

// Register in EventServiceProvider
namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        \App\Events\OrderPlaced::class => [
            \App\Listeners\SendOrderConfirmation::class,
            \App\Listeners\UpdateInventory::class,
            \App\Listeners\TrackOrderAnalytics::class,
            \App\Listeners\NotifyAdminOfNewOrder::class,
            \App\Listeners\UpdateLoyaltyPoints::class,
            \App\Listeners\SyncOrderToExternalSystems::class,
        ],
    ];
}

// Clean controller
class OrderController extends Controller
{
    public function store(StoreOrderRequest $request)
    {
        $order = Order::create($request->validated());

        event(new OrderPlaced($order));

        return redirect()->route('orders.show', $order);
    }
}

// Or dispatch from model using events property
class Order extends Model
{
    protected $dispatchesEvents = [
        'created' => OrderPlaced::class,
    ];
}
```

## Why

- **Decoupling**: Components don't know about each other
- **Single responsibility**: Each listener handles one concern
- **Async processing**: Heavy tasks run in background via queues
- **Scalability**: Easy to add new listeners without modifying existing code
- **Testability**: Test each listener in isolation
- **Failure isolation**: One listener failing doesn't affect others
- **Open/closed principle**: Open for extension, closed for modification

---

## Data Transfer Objects (DTOs)

**Impact: MEDIUM (Type safety and validation between layers)**

Use DTOs to transfer data between layers with type safety and validation.

## Bad Example

```php
// Passing arrays everywhere
class UserService
{
    public function createUser(array $data): User
    {
        // No type safety, uncertain what keys exist
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null, // May or may not exist
            'address' => $data['address'] ?? null,
        ]);
    }
}

// Controller passing raw array
class UserController extends Controller
{
    public function store(Request $request, UserService $service)
    {
        $user = $service->createUser($request->all());

        return new UserResource($user);
    }
}
```

## Good Example

```php
// DTO class with type safety
namespace App\DTOs;

use App\Http\Requests\CreateUserRequest;

readonly class CreateUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public ?string $phone = null,
        public ?string $address = null,
    ) {}

    public static function fromRequest(CreateUserRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            email: $request->validated('email'),
            password: $request->validated('password'),
            phone: $request->validated('phone'),
            address: $request->validated('address'),
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            phone: $data['phone'] ?? null,
            address: $data['address'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'phone' => $this->phone,
            'address' => $this->address,
        ];
    }
}

// Service using DTO
class UserService
{
    public function createUser(CreateUserDTO $dto): User
    {
        return User::create($dto->toArray());
    }
}

// Controller creating DTO from request
class UserController extends Controller
{
    public function store(CreateUserRequest $request, UserService $service)
    {
        $dto = CreateUserDTO::fromRequest($request);
        $user = $service->createUser($dto);

        return new UserResource($user);
    }
}

// Complex DTO with nested objects
namespace App\DTOs;

readonly class OrderDTO
{
    public function __construct(
        public int $userId,
        public AddressDTO $shippingAddress,
        public AddressDTO $billingAddress,
        /** @var OrderItemDTO[] */
        public array $items,
        public ?string $couponCode = null,
    ) {}

    public static function fromRequest(CreateOrderRequest $request): self
    {
        return new self(
            userId: auth()->id(),
            shippingAddress: AddressDTO::fromArray($request->validated('shipping_address')),
            billingAddress: AddressDTO::fromArray($request->validated('billing_address')),
            items: array_map(
                fn($item) => OrderItemDTO::fromArray($item),
                $request->validated('items')
            ),
            couponCode: $request->validated('coupon_code'),
        );
    }
}

readonly class AddressDTO
{
    public function __construct(
        public string $street,
        public string $city,
        public string $state,
        public string $zipCode,
        public string $country,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            street: $data['street'],
            city: $data['city'],
            state: $data['state'],
            zipCode: $data['zip_code'],
            country: $data['country'],
        );
    }
}

readonly class OrderItemDTO
{
    public function __construct(
        public int $productId,
        public int $quantity,
        public ?string $notes = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            productId: $data['product_id'],
            quantity: $data['quantity'],
            notes: $data['notes'] ?? null,
        );
    }
}
```

## Why

- **Type safety**: IDE autocompletion and static analysis support
- **Self-documenting**: DTO properties clearly define expected data
- **Immutable**: Using `readonly` prevents accidental modifications
- **Validation**: Data is validated before DTO creation
- **Refactoring**: Easier to track data usage across the codebase
- **Testing**: Easy to create DTOs with known values for tests

---

## Service Classes for Business Logic

**Impact: CRITICAL (Improves maintainability, testability, and code reusability)**

## Why It Matters

Controllers should be thin - they handle HTTP requests and delegate business logic. Service classes encapsulate complex business operations, making code reusable, testable, and maintainable.

## Bad Example

```php
// Fat controller with business logic
class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            // Calculate totals
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
                $subtotal += $product->price * $item['quantity'];

                // Check stock
                if ($product->stock < $item['quantity']) {
                    throw new Exception("Insufficient stock for {$product->name}");
                }
            }

            $tax = $subtotal * 0.1;
            $total = $subtotal + $tax;

            // Create order
            $order = Order::create([
                'user_id' => auth()->id(),
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
            ]);

            // Create order items and update stock
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ]);

                $product->decrement('stock', $item['quantity']);
            }

            // Send notifications
            Mail::to($order->user)->send(new OrderConfirmation($order));
            event(new OrderPlaced($order));

            DB::commit();

            return redirect()->route('orders.show', $order);

        } catch (Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
```

## Good Example

```php
// Service class with business logic
namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Events\OrderPlaced;
use App\Mail\OrderConfirmation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Exceptions\InsufficientStockException;

class OrderService
{
    public function createOrder(array $items, int $userId): Order
    {
        return DB::transaction(function () use ($items, $userId) {
            $this->validateStock($items);

            $orderData = $this->calculateTotals($items);
            $orderData['user_id'] = $userId;

            $order = Order::create($orderData);

            $this->createOrderItems($order, $items);
            $this->updateProductStock($items);
            $this->sendNotifications($order);

            return $order;
        });
    }

    private function validateStock(array $items): void
    {
        foreach ($items as $item) {
            $product = Product::findOrFail($item['product_id']);

            if ($product->stock < $item['quantity']) {
                throw new InsufficientStockException(
                    "Insufficient stock for {$product->name}"
                );
            }
        }
    }

    private function calculateTotals(array $items): array
    {
        $subtotal = collect($items)->sum(function ($item) {
            $product = Product::find($item['product_id']);
            return $product->price * $item['quantity'];
        });

        $tax = $subtotal * 0.1;

        return [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $subtotal + $tax,
        ];
    }

    private function createOrderItems(Order $order, array $items): void
    {
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);

            $order->items()->create([
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => $product->price,
            ]);
        }
    }

    private function updateProductStock(array $items): void
    {
        foreach ($items as $item) {
            Product::where('id', $item['product_id'])
                ->decrement('stock', $item['quantity']);
        }
    }

    private function sendNotifications(Order $order): void
    {
        Mail::to($order->user)->queue(new OrderConfirmation($order));
        event(new OrderPlaced($order));
    }
}
```

```php
// Thin controller
namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderService;
use App\Exceptions\InsufficientStockException;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {}

    public function store(StoreOrderRequest $request)
    {
        try {
            $order = $this->orderService->createOrder(
                $request->validated('items'),
                auth()->id()
            );

            return redirect()
                ->route('orders.show', $order)
                ->with('success', 'Order placed successfully!');

        } catch (InsufficientStockException $e) {
            return back()->withErrors(['stock' => $e->getMessage()]);
        }
    }
}
```

## Service Class Guidelines

```php
// Constructor injection
class UserService
{
    public function __construct(
        private readonly NotificationService $notifications,
        private readonly PaymentGateway $payments,
    ) {}
}

// Return types
public function findOrFail(int $id): User
{
    return User::findOrFail($id);
}

// Handle exceptions properly
public function processPayment(Order $order): PaymentResult
{
    try {
        return $this->payments->charge($order->total);
    } catch (PaymentFailedException $e) {
        Log::error('Payment failed', ['order' => $order->id, 'error' => $e->getMessage()]);
        throw $e;
    }
}
```

## When to Create a Service

- Complex business logic
- Operations involving multiple models
- Reusable operations (used in multiple controllers)
- Operations with side effects (email, events)
- Logic that needs unit testing

## Benefits

- Testable in isolation (mock dependencies)
- Reusable across controllers, commands, jobs
- Single responsibility
- Easier to understand and maintain

---

## 2. Eloquent & Database (CRITICAL)

**Impact:** CRITICAL  
**Description:** Efficient database operations and ORM usage. Preventing N+1 queries through eager loading, using chunking for large datasets, and proper relationship management are critical for performance. Poor database patterns can cripple application performance at scale.

**Rules in this category:** 9

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

---

## Eloquent Relationships

**Impact: CRITICAL (Expressive and efficient relationship management)**

Define relationships properly and use them effectively for clean, efficient database queries.

## Bad Example

```php
// Manual joins instead of relationships
$posts = DB::table('posts')
    ->join('users', 'posts.user_id', '=', 'users.id')
    ->select('posts.*', 'users.name as author_name')
    ->get();

// Raw queries for related data
$user = User::find(1);
$posts = DB::table('posts')->where('user_id', $user->id)->get();

// Inefficient nested queries
$users = User::all();
foreach ($users as $user) {
    $user->posts_count = DB::table('posts')
        ->where('user_id', $user->id)
        ->count();
}
```

## Good Example

```php
// Model with well-defined relationships
class User extends Model
{
    // One to Many
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    // One to Many with default ordering
    public function latestPosts(): HasMany
    {
        return $this->hasMany(Post::class)->latest()->limit(5);
    }

    // One to One
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    // Many to Many
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)
            ->withTimestamps()
            ->withPivot('assigned_by');
    }

    // Has Many Through
    public function postComments(): HasManyThrough
    {
        return $this->hasManyThrough(Comment::class, Post::class);
    }

    // Polymorphic
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    // One of Many (get single record from hasMany)
    public function latestOrder(): HasOne
    {
        return $this->hasOne(Order::class)->latestOfMany();
    }

    public function oldestOrder(): HasOne
    {
        return $this->hasOne(Order::class)->oldestOfMany();
    }

    public function largestOrder(): HasOne
    {
        return $this->hasOne(Order::class)->ofMany('total', 'max');
    }
}

class Post extends Model
{
    // Inverse relationship
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // With default model (prevents null checks)
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->withDefault([
            'name' => 'Uncategorized',
        ]);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}

// Using relationships effectively
$user = User::with(['posts', 'profile'])->find(1);

// Access related models
$user->posts;           // Collection of posts
$user->posts->count();  // Count without extra query
$user->profile;         // Single profile or null

// Query relationships
$publishedPosts = $user->posts()->published()->get();

// Create through relationship
$user->posts()->create([
    'title' => 'New Post',
    'body' => 'Content...',
]);

// Attach/detach many-to-many
$user->roles()->attach($roleId);
$user->roles()->attach([1, 2, 3]);
$user->roles()->detach($roleId);
$user->roles()->sync([1, 2, 3]); // Sync exactly these

// With pivot data
$user->roles()->attach($roleId, ['assigned_by' => auth()->id()]);

// Query by relationship existence
$usersWithPosts = User::has('posts')->get();
$usersWithManyPosts = User::has('posts', '>=', 5)->get();

// WhereHas for conditional relationship queries
$users = User::whereHas('posts', function ($query) {
    $query->where('published', true);
})->get();

// Load count
$users = User::withCount('posts')->get();
// Access: $user->posts_count

// Conditional count
$users = User::withCount([
    'posts',
    'posts as published_posts_count' => fn($q) => $q->published(),
])->get();
```

## Why

- **Readability**: Relationships are self-documenting
- **Efficiency**: Eager loading prevents N+1 queries
- **Consistency**: Standard API for accessing related data
- **Integrity**: Framework handles foreign keys and cascades
- **Flexibility**: Easy to add constraints to relationship queries
- **Maintainability**: Changes to relationships in one place

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

// Migration with soft deletes
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamps();
    $table->softDeletes(); // Adds deleted_at column
});

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

---

## Eloquent Attribute Casting

**Impact: HIGH (Automatic type conversion and data handling)**

Use Eloquent casts to automatically convert attributes to appropriate types.

## Bad Example

```php
// Manual type handling
class Order extends Model
{
    public function getMetadataAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setMetadataAttribute($value)
    {
        $this->attributes['metadata'] = json_encode($value);
    }

    public function getIsPaidAttribute($value)
    {
        return (bool) $value;
    }

    public function getTotalAttribute($value)
    {
        return (float) $value;
    }
}

// In controller
$order = Order::find(1);
$isPaid = (bool) $order->is_paid;
$total = (float) $order->total;
$createdAt = Carbon::parse($order->created_at);
```

## Good Example

```php
// Built-in casts
class Order extends Model
{
    protected $casts = [
        // Primitives
        'is_paid' => 'boolean',
        'total' => 'decimal:2',
        'quantity' => 'integer',
        'rating' => 'float',

        // Arrays and JSON
        'metadata' => 'array',
        'settings' => 'json',
        'tags' => 'collection',

        // Dates
        'paid_at' => 'datetime',
        'shipped_date' => 'date',
        'created_at' => 'immutable_datetime',

        // Encrypted (automatically encrypts/decrypts)
        'secret_token' => 'encrypted',
        'api_keys' => 'encrypted:array',

        // Enums (PHP 8.1+)
        'status' => OrderStatus::class,
        'payment_method' => PaymentMethod::class,

        // As object
        'address' => AsStringable::class,
    ];
}

// Usage - types are automatically converted
$order = Order::find(1);
$order->is_paid;        // bool
$order->total;          // "99.99" (decimal string)
$order->metadata;       // array
$order->paid_at;        // Carbon instance
$order->status;         // OrderStatus enum

// Enums
enum OrderStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Pending => 'Pending',
            self::Processing => 'Processing',
            self::Shipped => 'Shipped',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Pending => 'yellow',
            self::Processing => 'blue',
            self::Shipped => 'purple',
            self::Delivered => 'green',
            self::Cancelled => 'red',
        };
    }
}

// Custom cast class
namespace App\Casts;

use App\ValueObjects\Money;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class MoneyCast implements CastsAttributes
{
    public function __construct(
        private string $currency = 'USD'
    ) {}

    public function get($model, string $key, $value, array $attributes): ?Money
    {
        if (is_null($value)) {
            return null;
        }

        return new Money((int) $value, $this->currency);
    }

    public function set($model, string $key, $value, array $attributes): ?int
    {
        if (is_null($value)) {
            return null;
        }

        if ($value instanceof Money) {
            return $value->cents();
        }

        return (int) ($value * 100);
    }
}

// Use custom cast
class Product extends Model
{
    protected $casts = [
        'price' => MoneyCast::class,
        'cost' => MoneyCast::class . ':EUR',
    ];
}

// Cast with parameters via method
protected function casts(): array
{
    return [
        'price' => MoneyCast::class,
        'options' => AsCollection::class,
        'address' => AddressCast::class,
    ];
}

// Inbound-only casting (only on set)
class HashCast implements CastsInboundAttributes
{
    public function set($model, string $key, $value, array $attributes): string
    {
        return Hash::make($value);
    }
}
```

## Why

- **Type safety**: Attributes are always the expected type
- **Less boilerplate**: No manual type conversions
- **Automatic serialization**: JSON encoding/decoding handled
- **Consistency**: Same behavior everywhere the attribute is used
- **Enum support**: Type-safe status fields with IDE support
- **Encryption**: Sensitive data encrypted at rest automatically
- **Custom logic**: Complex types handled via custom casts

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

// In views, more manual formatting
<p>{{ strtoupper($user->first_name) }} {{ strtoupper($user->last_name) }}</p>
```

## Good Example

```php
// Modern accessors and mutators (Laravel 9+)
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

// In views - just use attributes directly
<p>{{ $user->full_name }}</p>
<p>{{ $user->phone }}</p>

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

---

## Query Scopes for Reusable Queries

**Impact: HIGH (DRY principle for database queries)**

Encapsulate reusable query constraints in model scopes for cleaner, more maintainable code.

## Bad Example

```php
// Repeated query logic scattered across controllers
class PostController extends Controller
{
    public function index()
    {
        $posts = Post::where('published', true)
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->get();

        return view('posts.index', compact('posts'));
    }

    public function featured()
    {
        // Same logic duplicated
        $posts = Post::where('published', true)
            ->where('published_at', '<=', now())
            ->where('is_featured', true)
            ->orderBy('published_at', 'desc')
            ->get();

        return view('posts.featured', compact('posts'));
    }
}

class ApiPostController extends Controller
{
    public function index()
    {
        // Again, duplicated logic
        $posts = Post::where('published', true)
            ->where('published_at', '<=', now())
            ->paginate(20);

        return PostResource::collection($posts);
    }
}
```

## Good Example

```php
// Model with reusable scopes
class Post extends Model
{
    // Local scope - called as scopePublished, used as published()
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true)
            ->where('published_at', '<=', now());
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderBy('published_at', 'desc');
    }

    public function scopeByAuthor(Builder $query, User $author): Builder
    {
        return $query->where('author_id', $author->id);
    }

    public function scopeInCategory(Builder $query, int|Category $category): Builder
    {
        $categoryId = $category instanceof Category ? $category->id : $category;
        return $query->where('category_id', $categoryId);
    }

    // Dynamic scope with parameters
    public function scopeCreatedBetween(Builder $query, $start, $end): Builder
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    // Scope with optional parameter
    public function scopePopular(Builder $query, int $minViews = 1000): Builder
    {
        return $query->where('views', '>=', $minViews);
    }
}

// Clean controller using scopes
class PostController extends Controller
{
    public function index()
    {
        $posts = Post::published()->recent()->get();

        return view('posts.index', compact('posts'));
    }

    public function featured()
    {
        $posts = Post::published()
            ->featured()
            ->recent()
            ->get();

        return view('posts.featured', compact('posts'));
    }

    public function byAuthor(User $author)
    {
        $posts = Post::published()
            ->byAuthor($author)
            ->recent()
            ->paginate(20);

        return view('posts.by-author', compact('posts', 'author'));
    }
}

// Global scope - automatically applied to all queries
class PublishedScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('published', true);
    }
}

// Apply global scope in model
class Post extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope(new PublishedScope);

        // Or inline
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('deleted_at', null);
        });
    }
}

// Remove global scope when needed
Post::withoutGlobalScope(PublishedScope::class)->get();
Post::withoutGlobalScope('active')->get();
Post::withoutGlobalScopes()->get(); // Remove all
```

## Why

- **DRY**: Query logic defined once, used everywhere
- **Readable**: Expressive, chainable method names
- **Maintainable**: Changes to query logic happen in one place
- **Testable**: Scopes can be unit tested
- **Encapsulation**: Query details hidden in the model
- **Chainable**: Scopes can be combined fluently

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
use Illuminate\Database\Eloquent\Prunable;

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

// For soft-deletable models, use MassPrunable for efficiency
use Illuminate\Database\Eloquent\MassPrunable;

class PasswordReset extends Model
{
    use MassPrunable;

    public function prunable(): Builder
    {
        // Delete password reset tokens older than 24 hours
        return static::where('created_at', '<', now()->subDay());
    }
}

// Complex pruning conditions
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

// Prunable with related cleanup
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

// Prunable notifications
class DatabaseNotification extends Model
{
    use MassPrunable;

    public function prunable(): Builder
    {
        return static::whereNotNull('read_at')
            ->where('read_at', '<', now()->subMonths(3));
    }
}

// Schedule pruning in Console Kernel
class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Run pruning daily
        $schedule->command('model:prune')->daily();

        // Or prune specific models
        $schedule->command('model:prune', [
            '--model' => [ActivityLog::class, Session::class],
        ])->daily();

        // With chunk size for large datasets
        $schedule->command('model:prune', ['--chunk' => 1000])->daily();
    }
}

// Run manually
php artisan model:prune
php artisan model:prune --model=App\\Models\\ActivityLog
php artisan model:prune --pretend // See what would be deleted
```

## Why

- **Automatic cleanup**: Database stays clean without manual intervention
- **Self-documenting**: Pruning logic lives with the model
- **Discoverable**: Laravel automatically finds all prunable models
- **Memory efficient**: MassPrunable uses bulk deletes
- **Lifecycle hooks**: Can clean up related resources before deletion
- **Testable**: Pruning logic can be unit tested
- **Scheduled**: Built-in Artisan command for scheduling

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

// Using an Observer for more complex scenarios
namespace App\Observers;

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

// Register observer in AppServiceProvider
class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        User::observe(UserObserver::class);
    }
}

// Or use the ObservedBy attribute (Laravel 10+)
#[ObservedBy(UserObserver::class)]
class User extends Model
{
    // ...
}

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

---

## Eager Loading Relationships

**Impact: CRITICAL (10-100× query performance improvement)**

Always eager load relationships to prevent N+1 query problems and improve performance.

## Bad Example

```php
// N+1 query problem - executes 101 queries for 100 posts
class PostController extends Controller
{
    public function index()
    {
        $posts = Post::all(); // 1 query

        return view('posts.index', compact('posts'));
    }
}

// In the view - N additional queries
@foreach ($posts as $post)
    <p>{{ $post->author->name }}</p>  <!-- 1 query per post -->
    <p>{{ $post->category->name }}</p> <!-- 1 query per post -->
@endforeach

// Also bad: loading in loop
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->comments->count(); // Query for each post
}
```

## Good Example

```php
// Eager load with 'with' - only 3 queries total
class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with(['author', 'category'])->get();

        return view('posts.index', compact('posts'));
    }
}

// Nested eager loading
$posts = Post::with([
    'author',
    'category',
    'comments.user', // Nested relationship
])->get();

// Eager loading with constraints
$posts = Post::with([
    'comments' => function ($query) {
        $query->where('approved', true)
              ->orderBy('created_at', 'desc')
              ->limit(5);
    },
    'author:id,name,avatar', // Select specific columns
])->get();

// Conditional eager loading
$posts = Post::query()
    ->when($includeComments, fn($q) => $q->with('comments'))
    ->get();

// Eager load count without loading the relationship
$posts = Post::withCount('comments')->get();
// Access: $post->comments_count

// Multiple counts
$posts = Post::withCount(['comments', 'likes'])
    ->withSum('orderItems', 'quantity')
    ->get();

// Default eager loading in model
class Post extends Model
{
    protected $with = ['author']; // Always eager loaded
}

// Lazy eager loading when you already have models
$posts = Post::all();
$posts->load(['author', 'category']); // Load after the fact

// Prevent lazy loading in development (add to AppServiceProvider)
public function boot(): void
{
    Model::preventLazyLoading(!app()->isProduction());
}
```

## Why

- **Performance**: Reduces database queries from N+1 to just 2-3
- **Predictability**: Know exactly how many queries will run
- **Scalability**: Application performs consistently regardless of data size
- **Resource efficiency**: Less database connections and memory usage
- **Debugging**: Easier to identify and fix query issues
- **Prevention**: `preventLazyLoading()` catches N+1 issues during development

---

## 3. Controllers & Routing (HIGH)

**Impact:** HIGH  
**Description:** RESTful conventions, resource controllers, and proper request handling. Well-structured controllers following Laravel conventions improve code predictability, maintainability, and team collaboration. Thin controllers delegate to services for business logic.

**Rules in this category:** 7

---

## Form Requests in Controllers

**Impact: HIGH (Separates validation logic from controllers)**

Move validation and authorization logic from controllers to dedicated Form Request classes.

## Bad Example

```php
// Validation logic cluttering the controller
class ArticleController extends Controller
{
    public function store(Request $request)
    {
        // Validation in controller
        $validated = $request->validate([
            'title' => 'required|string|max:255|unique:articles,title',
            'slug' => 'required|string|max:255|unique:articles,slug',
            'body' => 'required|string|min:100',
            'category_id' => 'required|exists:categories,id',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
            'published_at' => 'nullable|date|after:now',
            'featured_image' => 'nullable|image|max:2048',
        ]);

        // Authorization mixed in
        if (!auth()->user()->can('create', Article::class)) {
            abort(403);
        }

        $article = Article::create($validated);

        return redirect()->route('articles.show', $article);
    }

    public function update(Request $request, Article $article)
    {
        // Same validation duplicated
        $validated = $request->validate([
            'title' => 'required|string|max:255|unique:articles,title,' . $article->id,
            'slug' => 'required|string|max:255|unique:articles,slug,' . $article->id,
            'body' => 'required|string|min:100',
            'category_id' => 'required|exists:categories,id',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
            'published_at' => 'nullable|date',
            'featured_image' => 'nullable|image|max:2048',
        ]);

        $article->update($validated);

        return redirect()->route('articles.show', $article);
    }
}
```

## Good Example

```php
// Store request
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreArticleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Article::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255', 'unique:articles,title'],
            'slug' => ['required', 'string', 'max:255', 'unique:articles,slug'],
            'body' => ['required', 'string', 'min:100'],
            'category_id' => ['required', 'exists:categories,id'],
            'tags' => ['array'],
            'tags.*' => ['exists:tags,id'],
            'published_at' => ['nullable', 'date', 'after:now'],
            'featured_image' => ['nullable', 'image', 'max:2048'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'category',
            'published_at' => 'publication date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.unique' => 'An article with this title already exists.',
            'body.min' => 'The article body must be at least :min characters.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->slug ?? Str::slug($this->title),
        ]);
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        // Modify validated data after validation passes
        $this->replace([
            ...$this->validated(),
            'user_id' => $this->user()->id,
        ]);
    }
}

// Update request extending store
namespace App\Http\Requests;

class UpdateArticleRequest extends StoreArticleRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('article'));
    }

    public function rules(): array
    {
        $article = $this->route('article');

        return [
            ...parent::rules(),
            'title' => ['required', 'string', 'max:255', "unique:articles,title,{$article->id}"],
            'slug' => ['required', 'string', 'max:255', "unique:articles,slug,{$article->id}"],
            'published_at' => ['nullable', 'date'], // Remove 'after:now' for updates
        ];
    }
}

// Clean controller
class ArticleController extends Controller
{
    public function store(StoreArticleRequest $request)
    {
        $article = Article::create($request->validated());

        return redirect()->route('articles.show', $article)
            ->with('success', 'Article created successfully');
    }

    public function update(UpdateArticleRequest $request, Article $article)
    {
        $article->update($request->validated());

        return redirect()->route('articles.show', $article)
            ->with('success', 'Article updated successfully');
    }
}

// Generate form request
php artisan make:request StoreArticleRequest
```

## Why

- **Separation of concerns**: Controllers handle HTTP, requests handle validation
- **Reusability**: Same validation rules in multiple places
- **Testability**: Validation logic tested independently
- **Authorization**: Access control in one place
- **Custom messages**: User-friendly error messages
- **Data preparation**: Transform input before validation
- **Thin controllers**: Controllers stay focused on their job

---

## Dependency Injection in Controllers

**Impact: HIGH (Testable and loosely coupled controllers)**

Use constructor and method injection to provide dependencies instead of using facades or creating instances.

## Bad Example

```php
// Using facades and manual instantiation
class OrderController extends Controller
{
    public function store(Request $request)
    {
        // Tight coupling to concrete classes
        $paymentGateway = new StripePaymentGateway();
        $result = $paymentGateway->charge($request->total);

        // Static facade calls - hard to mock
        $order = Order::create($request->validated());
        Mail::to($order->user)->send(new OrderConfirmation($order));
        Log::info('Order created', ['order_id' => $order->id]);
        Cache::forget('user-orders-' . auth()->id());

        return redirect()->route('orders.show', $order);
    }

    public function export()
    {
        // Creating services manually
        $exporter = new CsvExporter();
        $orders = Order::all();

        return $exporter->export($orders);
    }
}
```

## Good Example

```php
// Constructor injection for shared dependencies
class OrderController extends Controller
{
    public function __construct(
        private readonly PaymentGatewayInterface $paymentGateway,
        private readonly OrderService $orderService,
    ) {}

    // Method injection for action-specific dependencies
    public function store(
        StoreOrderRequest $request,
        NotificationService $notifications
    ) {
        $order = $this->orderService->create(
            $request->validated(),
            auth()->user()
        );

        $notifications->sendOrderConfirmation($order);

        return redirect()->route('orders.show', $order);
    }

    public function export(CsvExporter $exporter)
    {
        $orders = Order::with('items')->get();

        return $exporter->export($orders);
    }
}

// Interface binding in service provider
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            PaymentGatewayInterface::class,
            StripePaymentGateway::class
        );

        // Conditional binding
        $this->app->bind(PaymentGatewayInterface::class, function ($app) {
            return match (config('payments.default')) {
                'stripe' => new StripePaymentGateway(config('payments.stripe')),
                'paypal' => new PayPalPaymentGateway(config('payments.paypal')),
                default => throw new InvalidArgumentException('Invalid payment gateway'),
            };
        });
    }
}

// Service class with injected dependencies
class OrderService
{
    public function __construct(
        private readonly PaymentGatewayInterface $payment,
        private readonly InventoryService $inventory,
        private readonly LoggerInterface $logger,
    ) {}

    public function create(array $data, User $user): Order
    {
        return DB::transaction(function () use ($data, $user) {
            $order = Order::create([
                'user_id' => $user->id,
                ...$data,
            ]);

            $this->inventory->reserve($order->items);
            $this->logger->info('Order created', ['order_id' => $order->id]);

            return $order;
        });
    }
}

// Testing with injected dependencies
class OrderControllerTest extends TestCase
{
    public function test_store_creates_order_and_charges_payment()
    {
        // Mock the payment gateway
        $mockGateway = $this->mock(PaymentGatewayInterface::class);
        $mockGateway->expects('charge')
            ->once()
            ->with(100.00)
            ->andReturn(new PaymentResult(success: true));

        $response = $this->actingAs($user)
            ->post('/orders', [
                'items' => [...],
                'total' => 100.00,
            ]);

        $response->assertRedirect();
    }
}

// Route model binding is also dependency injection
class PostController extends Controller
{
    // Laravel automatically resolves {post} from the route
    public function show(Post $post)
    {
        return view('posts.show', compact('post'));
    }

    // Custom resolution
    public function showBySlug(Post $post)
    {
        return view('posts.show', compact('post'));
    }
}

// In RouteServiceProvider or model
public function resolveRouteBinding($value, $field = null)
{
    return $this->where($field ?? 'slug', $value)->firstOrFail();
}
```

## Why

- **Testability**: Dependencies easily mocked in tests
- **Loose coupling**: Code depends on interfaces, not implementations
- **Flexibility**: Swap implementations via configuration
- **Explicit dependencies**: Clear what a class needs to function
- **Single responsibility**: Controller doesn't create its dependencies
- **IDE support**: Type hints enable autocompletion and refactoring

---

## Controller Middleware

**Impact: MEDIUM (Reusable request filtering and modification)**

Use middleware to handle cross-cutting concerns like authentication, rate limiting, and request modification.

## Bad Example

```php
// Authentication and authorization checks in every method
class AdminController extends Controller
{
    public function index()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        return view('admin.dashboard');
    }

    public function users()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        return view('admin.users');
    }

    public function settings()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        return view('admin.settings');
    }
}
```

## Good Example

```php
// Middleware in controller constructor
class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');

        // Apply to specific methods
        $this->middleware('verified')->only(['store', 'update']);
        $this->middleware('throttle:10,1')->only('store');
    }

    public function index()
    {
        return view('admin.dashboard');
    }

    public function users()
    {
        return view('admin.users');
    }
}

// Using middleware in routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index']);
    Route::resource('admin/users', AdminUserController::class);
});

// Using controller middleware attribute (Laravel 10+)
#[Middleware(['auth', 'admin'])]
class AdminController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }
}

// Custom middleware
namespace App\Http\Middleware;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()?->isAdmin()) {
            abort(403, 'Unauthorized. Admin access required.');
        }

        return $next($request);
    }
}

// Register middleware alias in bootstrap/app.php (Laravel 11+)
return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'subscribed' => EnsureUserIsSubscribed::class,
        ]);
    })
    ->create();

// Or in Kernel.php (Laravel 10 and earlier)
protected $middlewareAliases = [
    'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
];

// Middleware with parameters
namespace App\Http\Middleware;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()?->hasAnyRole($roles)) {
            abort(403);
        }

        return $next($request);
    }
}

// Usage with parameters
Route::get('/reports', [ReportController::class, 'index'])
    ->middleware('role:admin,manager');

// Middleware for API rate limiting
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::apiResource('posts', PostController::class);
});

// Conditional middleware
class ApiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');

        $this->middleware(function ($request, $next) {
            if ($request->user()->needsTwoFactor()) {
                return response()->json([
                    'message' => '2FA required',
                    'requires_2fa' => true,
                ], 403);
            }

            return $next($request);
        });
    }
}

// Middleware groups for common patterns
// bootstrap/app.php
$middleware->group('api', [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
]);
```

## Why

- **DRY**: Authentication/authorization logic in one place
- **Reusability**: Same middleware used across multiple controllers
- **Separation of concerns**: Controllers focus on business logic
- **Composability**: Stack multiple middleware for complex requirements
- **Testability**: Middleware tested independently
- **Declarative**: Clear what protection is applied just by looking at the route/controller

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

// Generate single action controller
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

---

## RESTful Resource Methods

**Impact: HIGH (Standard CRUD operations following REST conventions)**

Use resource controllers with standard RESTful methods for CRUD operations.

## Bad Example

```php
// Non-standard method names
class ArticleController extends Controller
{
    public function list() { /* ... */ }
    public function view($id) { /* ... */ }
    public function add() { /* ... */ }
    public function save(Request $request) { /* ... */ }
    public function modify($id) { /* ... */ }
    public function change(Request $request, $id) { /* ... */ }
    public function remove($id) { /* ... */ }
}

// Inconsistent routes
Route::get('/articles', [ArticleController::class, 'list']);
Route::get('/articles/{id}', [ArticleController::class, 'view']);
Route::get('/articles/add', [ArticleController::class, 'add']);
Route::post('/articles/save', [ArticleController::class, 'save']);
Route::get('/articles/{id}/modify', [ArticleController::class, 'modify']);
Route::post('/articles/{id}/change', [ArticleController::class, 'change']);
Route::post('/articles/{id}/remove', [ArticleController::class, 'remove']);
```

## Good Example

```php
// Standard resource controller
namespace App\Http\Controllers;

class ArticleController extends Controller
{
    /**
     * Display a listing of articles.
     * GET /articles
     */
    public function index()
    {
        $articles = Article::with('author')
            ->published()
            ->latest()
            ->paginate(20);

        return view('articles.index', compact('articles'));
    }

    /**
     * Show the form for creating a new article.
     * GET /articles/create
     */
    public function create()
    {
        $categories = Category::all();

        return view('articles.create', compact('categories'));
    }

    /**
     * Store a newly created article.
     * POST /articles
     */
    public function store(StoreArticleRequest $request)
    {
        $article = auth()->user()->articles()->create(
            $request->validated()
        );

        return redirect()
            ->route('articles.show', $article)
            ->with('success', 'Article created successfully');
    }

    /**
     * Display the specified article.
     * GET /articles/{article}
     */
    public function show(Article $article)
    {
        $article->load(['author', 'comments.user']);

        return view('articles.show', compact('article'));
    }

    /**
     * Show the form for editing the specified article.
     * GET /articles/{article}/edit
     */
    public function edit(Article $article)
    {
        $this->authorize('update', $article);
        $categories = Category::all();

        return view('articles.edit', compact('article', 'categories'));
    }

    /**
     * Update the specified article.
     * PUT/PATCH /articles/{article}
     */
    public function update(UpdateArticleRequest $request, Article $article)
    {
        $article->update($request->validated());

        return redirect()
            ->route('articles.show', $article)
            ->with('success', 'Article updated successfully');
    }

    /**
     * Remove the specified article.
     * DELETE /articles/{article}
     */
    public function destroy(Article $article)
    {
        $this->authorize('delete', $article);

        $article->delete();

        return redirect()
            ->route('articles.index')
            ->with('success', 'Article deleted successfully');
    }
}

// Simple resource route
Route::resource('articles', ArticleController::class);

// Partial resource
Route::resource('articles', ArticleController::class)
    ->only(['index', 'show']);

Route::resource('articles', ArticleController::class)
    ->except(['create', 'edit']);

// API resource (without create/edit)
Route::apiResource('articles', ArticleController::class);

// Nested resources
Route::resource('articles.comments', CommentController::class);
// Creates: articles/{article}/comments

// Shallow nesting
Route::resource('articles.comments', CommentController::class)->shallow();
// Nests only index, create, store
// Uses /comments/{comment} for show, update, destroy

// Named routes are automatic:
// articles.index, articles.create, articles.store
// articles.show, articles.edit, articles.update
// articles.destroy
```

## Why

- **Convention over configuration**: Standard names everyone understands
- **Automatic routing**: Single route declaration handles all methods
- **Named routes**: Automatic, predictable route names
- **HTTP verbs**: Proper use of GET, POST, PUT, DELETE
- **Framework support**: Form method spoofing, CSRF protection built-in
- **Team consistency**: All developers use the same patterns
- **Documentation**: Self-documenting API following REST conventions

---

## Use Resource Controllers

**Impact: HIGH (RESTful conventions and consistent routing)**

## Why It Matters

Resource controllers provide a consistent, RESTful structure for CRUD operations. They follow Laravel conventions, making code predictable and easier for other developers to understand.

## Bad Example

```php
// Inconsistent naming and structure
Route::get('/posts', [PostController::class, 'getAllPosts']);
Route::get('/posts/{id}', [PostController::class, 'getPost']);
Route::post('/posts/new', [PostController::class, 'createPost']);
Route::put('/posts/{id}/edit', [PostController::class, 'updatePost']);
Route::delete('/posts/{id}/delete', [PostController::class, 'removePost']);

// Non-standard controller methods
class PostController extends Controller
{
    public function getAllPosts() { }
    public function getPost($id) { }
    public function createPost() { }
    public function updatePost($id) { }
    public function removePost($id) { }
}
```

## Good Example

### Resource Route

```php
// Single line defines all 7 RESTful routes
Route::resource('posts', PostController::class);

// Generated routes:
// GET    /posts              index    posts.index
// GET    /posts/create       create   posts.create
// POST   /posts              store    posts.store
// GET    /posts/{post}       show     posts.show
// GET    /posts/{post}/edit  edit     posts.edit
// PUT    /posts/{post}       update   posts.update
// DELETE /posts/{post}       destroy  posts.destroy
```

### Resource Controller

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $posts = Post::with('author')
            ->latest()
            ->paginate(15);

        return view('posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $categories = Category::all();

        return view('posts.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request): RedirectResponse
    {
        $post = Post::create([
            ...$request->validated(),
            'user_id' => auth()->id(),
        ]);

        return redirect()
            ->route('posts.show', $post)
            ->with('success', 'Post created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post): View
    {
        $post->load(['author', 'comments.user', 'tags']);

        return view('posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post): View
    {
        $this->authorize('update', $post);

        $categories = Category::all();

        return view('posts.edit', compact('post', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $post->update($request->validated());

        return redirect()
            ->route('posts.show', $post)
            ->with('success', 'Post updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);

        $post->delete();

        return redirect()
            ->route('posts.index')
            ->with('success', 'Post deleted successfully.');
    }
}
```

### Partial Resource Routes

```php
// Only specific actions
Route::resource('posts', PostController::class)
    ->only(['index', 'show']);

// All except specific actions
Route::resource('posts', PostController::class)
    ->except(['destroy']);
```

### API Resource Controller

```php
// API routes (no create/edit - those are for forms)
Route::apiResource('posts', Api\PostController::class);

// Generated routes:
// GET    /posts          index
// POST   /posts          store
// GET    /posts/{post}   show
// PUT    /posts/{post}   update
// DELETE /posts/{post}   destroy
```

### Nested Resources

```php
// Nested resource routes
Route::resource('posts.comments', CommentController::class);

// Generated: /posts/{post}/comments/{comment}

// Shallow nesting (recommended)
Route::resource('posts.comments', CommentController::class)->shallow();

// Generated:
// /posts/{post}/comments       (index, store)
// /comments/{comment}          (show, update, destroy)
```

### Generate Resource Controller

```bash
# Generate with all methods
php artisan make:controller PostController --resource

# Generate with model binding
php artisan make:controller PostController --resource --model=Post

# Generate API controller
php artisan make:controller Api/PostController --api --model=Post
```

## Route Model Binding

```php
// Automatic model binding - Laravel resolves Post from {post}
public function show(Post $post): View
{
    // $post is automatically fetched or 404
    return view('posts.show', compact('post'));
}

// Custom binding key
// Route: /posts/{post:slug}
public function show(Post $post): View
{
    // Resolved by slug instead of id
}

// In model
class Post extends Model
{
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
```

## Benefits

- Consistent URL structure
- Predictable controller methods
- Automatic route model binding
- Easy to generate views (posts.index, posts.show, etc.)
- Clear conventions for team collaboration

---

## API Resources for Response Transformation

**Impact: HIGH (Consistent API responses and data transformation)**

Use API Resources to transform models into consistent JSON responses.

## Bad Example

```php
// Manual array transformation in controller
class UserController extends Controller
{
    public function show(User $user)
    {
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar_url' => $user->avatar ? asset('storage/' . $user->avatar) : null,
            'created_at' => $user->created_at->toISOString(),
            // Forgot to include some fields? Inconsistent across endpoints?
        ]);
    }

    public function index()
    {
        $users = User::paginate(20);

        // Different transformation logic duplicated
        return response()->json([
            'data' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    // Missing avatar_url? Inconsistent!
                ];
            }),
            'meta' => [
                'total' => $users->total(),
                'page' => $users->currentPage(),
            ],
        ]);
    }
}
```

## Good Example

```php
// API Resource for single model
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar_url' => $this->avatar
                ? asset('storage/' . $this->avatar)
                : null,
            'email_verified' => !is_null($this->email_verified_at),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // Conditional attributes
            'is_admin' => $this->when($request->user()?->isAdmin(), $this->is_admin),

            // Include relationships when loaded
            'posts' => PostResource::collection($this->whenLoaded('posts')),
            'profile' => new ProfileResource($this->whenLoaded('profile')),

            // Counts
            'posts_count' => $this->whenCounted('posts'),

            // Pivot data
            'role' => $this->whenPivotLoaded('role_user', function () {
                return $this->pivot->role;
            }),
        ];
    }

    /**
     * Add additional data to the response.
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'api_version' => '1.0',
            ],
        ];
    }
}

// Resource collection for customizing collection behavior
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total_users' => $this->collection->count(),
                'has_admins' => $this->collection->contains('is_admin', true),
            ],
        ];
    }
}

// Nested resource
class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => Str::limit($this->body, 150),
            'body' => $this->when(
                $request->routeIs('posts.show'),
                $this->body
            ),
            'published_at' => $this->published_at?->toISOString(),
            'is_published' => $this->isPublished(),

            // Always include author summary
            'author' => [
                'id' => $this->author->id,
                'name' => $this->author->name,
            ],

            // Full author resource when loaded
            'author_full' => new UserResource($this->whenLoaded('author')),

            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),

            'links' => [
                'self' => route('posts.show', $this->resource),
            ],
        ];
    }
}

// Clean API controller
class UserController extends Controller
{
    public function index()
    {
        $users = User::with('profile')
            ->withCount('posts')
            ->paginate(20);

        return UserResource::collection($users);
    }

    public function show(User $user)
    {
        $user->load(['posts' => fn($q) => $q->latest()->limit(5), 'profile']);

        return new UserResource($user);
    }

    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->validated());

        return new UserResource($user);
    }
}

// Response structure is consistent:
{
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "avatar_url": "https://...",
        "created_at": "2024-01-15T10:30:00.000000Z"
    },
    "meta": {
        "api_version": "1.0"
    }
}
```

## Why

- **Consistency**: Same model always produces same JSON structure
- **Reusability**: Resource used across multiple endpoints
- **Conditional data**: Include data based on context
- **Relationships**: Handle nested resources elegantly
- **Pagination**: Automatic pagination metadata
- **API versioning**: Easy to create v2 resources
- **Documentation**: Resource classes document your API structure

---

## 4. Validation & Requests (HIGH)

**Impact:** HIGH  
**Description:** Form request classes, custom validation rules, and authorization patterns. Proper validation ensures data integrity, security, and separation of concerns. Centralized validation logic in form requests keeps controllers clean and validation rules reusable.

**Rules in this category:** 6

---

## Form Request Classes for Validation

**Impact: HIGH (Separation of concerns and reusable validation)**

## Why It Matters

Form Request classes separate validation from controllers, making code cleaner, reusable, and easier to test. They also provide a dedicated place for authorization logic.

## Bad Example

```php
// Validation in controller - cluttered and not reusable
class PostController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|min:100',
            'category_id' => 'required|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'published_at' => 'nullable|date|after:now',
        ]);

        // Check authorization manually
        if (!auth()->user()->can('create', Post::class)) {
            abort(403);
        }

        // ... create post
    }

    public function update(Request $request, Post $post)
    {
        // Same validation repeated
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|min:100',
            // ...
        ]);

        // ...
    }
}
```

## Good Example

### Create Form Request

```bash
php artisan make:request StorePostRequest
php artisan make:request UpdatePostRequest
```

### Form Request Class

```php
<?php

namespace App\Http\Requests;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Post::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:posts,slug'],
            'body' => ['required', 'string', 'min:100'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'category_id' => ['required', 'exists:categories,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:tags,id'],
            'published_at' => ['nullable', 'date', 'after:now'],
            'featured_image' => ['nullable', 'image', 'max:2048'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'body.min' => 'The post content must be at least 100 characters.',
            'category_id.exists' => 'The selected category does not exist.',
            'featured_image.max' => 'The image must not exceed 2MB.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'category',
            'published_at' => 'publication date',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->slug ?? Str::slug($this->title),
        ]);
    }
}
```

### Update Request with Different Rules

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('post'));
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                // Ignore current post when checking uniqueness
                Rule::unique('posts', 'slug')->ignore($this->route('post')),
            ],
            'body' => ['required', 'string', 'min:100'],
            'category_id' => ['required', 'exists:categories,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:tags,id'],
        ];
    }
}
```

### Clean Controller

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;

class PostController extends Controller
{
    public function store(StorePostRequest $request)
    {
        // Validation and authorization happen automatically

        $post = Post::create($request->validated());

        if ($request->hasFile('featured_image')) {
            $post->addMediaFromRequest('featured_image')
                 ->toMediaCollection('featured');
        }

        return redirect()
            ->route('posts.show', $post)
            ->with('success', 'Post created successfully.');
    }

    public function update(UpdatePostRequest $request, Post $post)
    {
        $post->update($request->validated());

        return redirect()
            ->route('posts.show', $post)
            ->with('success', 'Post updated successfully.');
    }
}
```

### Conditional Validation

```php
public function rules(): array
{
    return [
        'payment_method' => ['required', 'in:credit_card,bank_transfer'],

        // Only required if payment method is credit card
        'card_number' => [
            Rule::requiredIf($this->payment_method === 'credit_card'),
            'nullable',
            'string',
            'size:16',
        ],

        // Required sometimes
        'coupon_code' => [
            'sometimes',
            'nullable',
            'exists:coupons,code',
        ],
    ];
}
```

### After Validation Hook

```php
public function after(): array
{
    return [
        function (Validator $validator) {
            if ($this->somethingElseIsInvalid()) {
                $validator->errors()->add(
                    'field',
                    'Something is wrong with this field!'
                );
            }
        }
    ];
}
```

## Benefits

- Separation of concerns
- Reusable validation rules
- Authorization in one place
- Testable in isolation
- Cleaner controllers

---

## Conditional Validation Rules

**Impact: HIGH (Dynamic validation based on input)**

Apply validation rules conditionally based on input values or other conditions.

## Bad Example

```php
// Manual conditional logic in controller
class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $rules = [
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:card,bank,paypal',
        ];

        // Messy conditional rules
        if ($request->payment_method === 'card') {
            $rules['card_number'] = 'required|string|size:16';
            $rules['card_expiry'] = 'required|date_format:m/y';
            $rules['card_cvv'] = 'required|string|size:3';
        }

        if ($request->payment_method === 'bank') {
            $rules['bank_account'] = 'required|string';
            $rules['routing_number'] = 'required|string|size:9';
        }

        if ($request->payment_method === 'paypal') {
            $rules['paypal_email'] = 'required|email';
        }

        if ($request->save_for_later) {
            $rules['nickname'] = 'required|string|max:50';
        }

        $validated = $request->validate($rules);

        // ...
    }
}
```

## Good Example

```php
// Form Request with conditional rules
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProcessPaymentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', Rule::in(['card', 'bank', 'paypal'])],

            // Conditional with required_if
            'card_number' => ['required_if:payment_method,card', 'nullable', 'digits:16'],
            'card_expiry' => ['required_if:payment_method,card', 'nullable', 'date_format:m/y'],
            'card_cvv' => ['required_if:payment_method,card', 'nullable', 'digits:3'],

            'bank_account' => ['required_if:payment_method,bank', 'nullable', 'string'],
            'routing_number' => ['required_if:payment_method,bank', 'nullable', 'digits:9'],

            'paypal_email' => ['required_if:payment_method,paypal', 'nullable', 'email'],

            // Required when another field is truthy
            'save_for_later' => ['boolean'],
            'nickname' => ['required_if:save_for_later,true', 'nullable', 'string', 'max:50'],
        ];
    }
}

// Using Rule::when() for complex conditions
class StoreOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['standard', 'express', 'scheduled'])],
            'items' => ['required', 'array', 'min:1'],

            // When type is 'scheduled', require delivery date
            'delivery_date' => [
                Rule::when(
                    $this->type === 'scheduled',
                    ['required', 'date', 'after:today'],
                    ['nullable', 'date']
                ),
            ],

            // Complex condition with closure
            'express_fee_accepted' => [
                Rule::when(
                    fn () => $this->type === 'express' && $this->total > 100,
                    ['required', 'accepted'],
                ),
            ],

            // Exclude when condition is true
            'coupon_code' => [
                Rule::excludeIf($this->type === 'express'),
                'nullable',
                'string',
                'exists:coupons,code',
            ],
        ];
    }
}

// Sometimes rules (deprecated but still works)
class ProfileRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'newsletter' => ['boolean'],
        ];

        // Add rules conditionally
        $this->sometimes('phone', 'required|string', function ($input) {
            return $input->newsletter === true;
        });

        return $rules;
    }
}

// Conditional rules in controller (when needed)
class RegistrationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_type' => ['required', Rule::in(['personal', 'business'])],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],

            // Business account requires additional fields
            'company_name' => ['required_if:account_type,business', 'nullable', 'string'],
            'tax_id' => ['required_if:account_type,business', 'nullable', 'string'],

            // Prohibit certain fields for personal accounts
            'company_name' => [
                Rule::prohibitedIf($request->account_type === 'personal'),
            ],
        ]);
    }
}

// Multiple conditions
class ComplexFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'has_children' => ['boolean'],
            'children_count' => [
                'required_if:has_children,true',
                'nullable',
                'integer',
                'min:1',
                'max:20',
            ],

            // Required unless another field has a value
            'emergency_contact' => ['required_unless:has_spouse,true'],

            // Required without another field
            'mobile_phone' => ['required_without:home_phone'],
            'home_phone' => ['required_without:mobile_phone'],

            // Required with all of these fields
            'delivery_notes' => ['required_with_all:street,city,zip'],

            // Exclude if null or missing
            'metadata' => ['exclude_if:type,basic', 'array'],
        ];
    }
}
```

## Why

- **Clean code**: No manual if/else for building rules
- **Declarative**: Rules clearly state their conditions
- **Built-in support**: Laravel provides many conditional rules
- **Flexible**: Complex conditions supported with closures
- **Maintainable**: Conditions live with their rules
- **Testable**: Conditional logic tested through form requests

---

## Custom Validation Rules

**Impact: HIGH (Reusable domain-specific validation)**

Create reusable custom validation rules for complex or domain-specific validation logic.

## Bad Example

```php
// Complex validation logic in controller
class UserController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'password' => [
                'required',
                'string',
                'min:8',
                function ($attribute, $value, $fail) {
                    // Complex password rules inline
                    if (!preg_match('/[A-Z]/', $value)) {
                        $fail('Password must contain uppercase letter.');
                    }
                    if (!preg_match('/[a-z]/', $value)) {
                        $fail('Password must contain lowercase letter.');
                    }
                    if (!preg_match('/[0-9]/', $value)) {
                        $fail('Password must contain a number.');
                    }
                    if (!preg_match('/[@$!%*#?&]/', $value)) {
                        $fail('Password must contain a special character.');
                    }
                    if (preg_match('/(.)\1{2,}/', $value)) {
                        $fail('Password cannot contain 3+ repeated characters.');
                    }
                },
            ],
            'phone' => [
                'required',
                function ($attribute, $value, $fail) {
                    // Phone validation duplicated everywhere
                    $cleaned = preg_replace('/[^0-9]/', '', $value);
                    if (strlen($cleaned) < 10 || strlen($cleaned) > 15) {
                        $fail('Invalid phone number.');
                    }
                },
            ],
        ]);
    }
}
```

## Good Example

```php
// Custom rule class for strong password
namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    public function __construct(
        private bool $requireUppercase = true,
        private bool $requireNumber = true,
        private bool $requireSpecialChar = true,
        private int $minLength = 8,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (strlen($value) < $this->minLength) {
            $fail("The :attribute must be at least {$this->minLength} characters.");
            return;
        }

        if ($this->requireUppercase && !preg_match('/[A-Z]/', $value)) {
            $fail('The :attribute must contain at least one uppercase letter.');
        }

        if (!preg_match('/[a-z]/', $value)) {
            $fail('The :attribute must contain at least one lowercase letter.');
        }

        if ($this->requireNumber && !preg_match('/[0-9]/', $value)) {
            $fail('The :attribute must contain at least one number.');
        }

        if ($this->requireSpecialChar && !preg_match('/[@$!%*#?&]/', $value)) {
            $fail('The :attribute must contain at least one special character.');
        }

        if (preg_match('/(.)\1{2,}/', $value)) {
            $fail('The :attribute cannot contain 3 or more repeated characters.');
        }
    }
}

// Phone number validation rule
namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidPhoneNumber implements ValidationRule
{
    public function __construct(
        private ?string $countryCode = null
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cleaned = preg_replace('/[^0-9+]/', '', $value);

        if (strlen($cleaned) < 10 || strlen($cleaned) > 15) {
            $fail('The :attribute must be a valid phone number.');
            return;
        }

        if ($this->countryCode === 'US' && !preg_match('/^(\+1)?[0-9]{10}$/', $cleaned)) {
            $fail('The :attribute must be a valid US phone number.');
        }
    }
}

// Rule with database check
namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueForUser implements ValidationRule
{
    public function __construct(
        private string $table,
        private string $column,
        private ?int $ignoreId = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = DB::table($this->table)
            ->where($this->column, $value)
            ->where('user_id', auth()->id());

        if ($this->ignoreId) {
            $query->where('id', '!=', $this->ignoreId);
        }

        if ($query->exists()) {
            $fail("You already have a record with this :attribute.");
        }
    }
}

// Invokable rule for simple cases
namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Uppercase implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (strtoupper($value) !== $value) {
            $fail('The :attribute must be uppercase.');
        }
    }
}

// Using custom rules in Form Request
class StoreUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                new StrongPassword(
                    requireSpecialChar: true,
                    minLength: 12
                ),
            ],
            'phone' => ['required', new ValidPhoneNumber('US')],
            'nickname' => [
                'nullable',
                'string',
                new UniqueForUser('profiles', 'nickname'),
            ],
        ];
    }
}

// Register rules globally via macro (optional)
// In AppServiceProvider
Validator::extend('strong_password', function ($attribute, $value, $parameters) {
    return (new StrongPassword())->passes($attribute, $value);
});

// Or use Rule::macro for fluent syntax
Rule::macro('strongPassword', function () {
    return new StrongPassword();
});

// Usage: 'password' => ['required', Rule::strongPassword()]
```

## Why

- **Reusability**: Same validation logic used across the application
- **Testability**: Custom rules can be unit tested
- **Encapsulation**: Complex logic hidden behind simple interface
- **Readability**: Clean, descriptive rule names
- **Maintainability**: Change validation logic in one place
- **Documentation**: Rule classes document validation requirements

---

## Form Request Validation

**Impact: HIGH (Centralized validation and authorization)**

Encapsulate validation logic in Form Request classes for cleaner controllers and reusability.

## Bad Example

```php
// Validation cluttering the controller
class ProductController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'sku' => 'required|string|unique:products,sku',
            'category_id' => 'required|exists:categories,id',
            'images' => 'array|max:5',
            'images.*' => 'image|max:2048',
            'attributes' => 'array',
            'attributes.*.name' => 'required|string',
            'attributes.*.value' => 'required|string',
        ], [
            'name.required' => 'Product name is required.',
            'sku.unique' => 'This SKU is already in use.',
        ]);

        // Authorization check also in controller
        if (!$request->user()->can('create', Product::class)) {
            abort(403);
        }

        $product = Product::create($validated);

        return redirect()->route('products.show', $product);
    }
}
```

## Good Example

```php
// Dedicated Form Request class
namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Product::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku'],
            'category_id' => ['required', 'exists:categories,id'],
            'images' => ['array', 'max:5'],
            'images.*' => ['image', 'max:2048', 'mimes:jpg,png,webp'],
            'attributes' => ['array'],
            'attributes.*.name' => ['required', 'string', 'max:50'],
            'attributes.*.value' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'published_at' => ['nullable', 'date'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'sku' => 'SKU',
            'category_id' => 'category',
            'images.*' => 'image',
            'attributes.*.name' => 'attribute name',
            'attributes.*.value' => 'attribute value',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Product name is required.',
            'sku.unique' => 'This SKU is already in use.',
            'images.max' => 'You can upload a maximum of :max images.',
            'price.max' => 'Price cannot exceed $999,999.99.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->name),
            'price' => $this->price ? (float) $this->price : null,
        ]);
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        // Add computed values after validation
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization(): void
    {
        throw new AuthorizationException(
            'You are not authorized to create products.'
        );
    }
}

// Update request with different rules
class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('product'));
    }

    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'sku' => [
                'required',
                'string',
                Rule::unique('products', 'sku')->ignore($product->id),
            ],
            'category_id' => ['required', 'exists:categories,id'],
        ];
    }
}

// Clean controller
class ProductController extends Controller
{
    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated());

        return redirect()->route('products.show', $product)
            ->with('success', 'Product created successfully');
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        return redirect()->route('products.show', $product)
            ->with('success', 'Product updated successfully');
    }
}
```

## Why

- **Separation of concerns**: Validation logic separate from controller
- **Reusability**: Same request class used in multiple controllers
- **Authorization**: Single place for both validation and authorization
- **Custom messages**: User-friendly error messages
- **Testability**: Form requests can be tested independently
- **Clean controllers**: Controllers focus on business logic
- **Self-documenting**: Request class shows expected input structure

---

## Validation After Hooks

**Impact: MEDIUM (Complex cross-field validation)**

Use after validation hooks for complex validation that depends on multiple fields or external data.

## Bad Example

```php
// Complex validation logic mixed in controller
class BookingController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date|after:today',
            'check_out' => 'required|date|after:check_in',
            'guests' => 'required|integer|min:1',
        ]);

        // Post-validation checks scattered in controller
        $room = Room::find($validated['room_id']);

        if ($validated['guests'] > $room->max_guests) {
            return back()->withErrors([
                'guests' => 'This room has a maximum capacity of ' . $room->max_guests,
            ]);
        }

        $existingBooking = Booking::where('room_id', $validated['room_id'])
            ->where(function ($query) use ($validated) {
                $query->whereBetween('check_in', [$validated['check_in'], $validated['check_out']])
                    ->orWhereBetween('check_out', [$validated['check_in'], $validated['check_out']]);
            })
            ->exists();

        if ($existingBooking) {
            return back()->withErrors([
                'room_id' => 'This room is not available for the selected dates.',
            ]);
        }

        // ... create booking
    }
}
```

## Good Example

```php
// Form Request with after validation hook
namespace App\Http\Requests;

use App\Models\Room;
use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'room_id' => ['required', 'exists:rooms,id'],
            'check_in' => ['required', 'date', 'after:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'guests' => ['required', 'integer', 'min:1'],
            'special_requests' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // Only run if basic validation passed
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $this->validateRoomCapacity($validator);
            $this->validateRoomAvailability($validator);
            $this->validateMinimumStay($validator);
        });
    }

    private function validateRoomCapacity(Validator $validator): void
    {
        $room = Room::find($this->room_id);

        if ($room && $this->guests > $room->max_guests) {
            $validator->errors()->add(
                'guests',
                "This room has a maximum capacity of {$room->max_guests} guests."
            );
        }
    }

    private function validateRoomAvailability(Validator $validator): void
    {
        $hasConflict = Booking::where('room_id', $this->room_id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('check_in', '<=', $this->check_in)
                      ->where('check_out', '>', $this->check_in);
                })->orWhere(function ($q) {
                    $q->where('check_in', '<', $this->check_out)
                      ->where('check_out', '>=', $this->check_out);
                })->orWhere(function ($q) {
                    $q->where('check_in', '>=', $this->check_in)
                      ->where('check_out', '<=', $this->check_out);
                });
            })
            ->exists();

        if ($hasConflict) {
            $validator->errors()->add(
                'room_id',
                'This room is not available for the selected dates.'
            );
        }
    }

    private function validateMinimumStay(Validator $validator): void
    {
        $checkIn = Carbon::parse($this->check_in);
        $checkOut = Carbon::parse($this->check_out);
        $nights = $checkIn->diffInDays($checkOut);

        $room = Room::find($this->room_id);

        if ($room && $nights < $room->minimum_nights) {
            $validator->errors()->add(
                'check_out',
                "This room requires a minimum stay of {$room->minimum_nights} nights."
            );
        }
    }
}

// After hook for cross-field validation
class UpdatePasswordRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (!Hash::check($this->current_password, $this->user()->password)) {
                $validator->errors()->add(
                    'current_password',
                    'The current password is incorrect.'
                );
            }

            if ($this->current_password === $this->password) {
                $validator->errors()->add(
                    'password',
                    'New password must be different from current password.'
                );
            }
        });
    }
}

// After hook with external API validation
class VerifyAddressRequest extends FormRequest
{
    public function __construct(
        private AddressVerificationService $addressService
    ) {
        parent::__construct();
    }

    public function rules(): array
    {
        return [
            'street' => ['required', 'string'],
            'city' => ['required', 'string'],
            'state' => ['required', 'string', 'size:2'],
            'zip' => ['required', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $result = $this->addressService->verify([
                'street' => $this->street,
                'city' => $this->city,
                'state' => $this->state,
                'zip' => $this->zip,
            ]);

            if (!$result->isValid()) {
                $validator->errors()->add('street', 'Unable to verify address.');

                if ($result->hasSuggestion()) {
                    $validator->errors()->add(
                        'suggestion',
                        'Did you mean: ' . $result->getSuggestion()
                    );
                }
            }
        });
    }
}
```

## Why

- **Complex validation**: Handle multi-field dependencies
- **External checks**: Validate against APIs or services
- **Database queries**: Check uniqueness across related records
- **Clean separation**: Keep complex logic out of controllers
- **Conditional execution**: Only run when basic validation passes
- **Rich error messages**: Add context-specific error details

---

## Array and Nested Validation

**Impact: HIGH (Validates complex nested data structures)**

Validate arrays and nested data structures with Laravel's powerful array validation syntax.

## Bad Example

```php
// Manual array validation
class OrderController extends Controller
{
    public function store(Request $request)
    {
        // No proper array validation
        $items = $request->items;

        if (!is_array($items) || empty($items)) {
            return back()->withErrors(['items' => 'Items required']);
        }

        foreach ($items as $index => $item) {
            if (empty($item['product_id'])) {
                return back()->withErrors(["items.{$index}.product_id" => 'Product required']);
            }
            if (!is_numeric($item['quantity']) || $item['quantity'] < 1) {
                return back()->withErrors(["items.{$index}.quantity" => 'Invalid quantity']);
            }
        }

        // Prone to errors, hard to maintain
    }
}
```

## Good Example

```php
// Proper array validation
class StoreOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // Array validation
            'items' => ['required', 'array', 'min:1', 'max:50'],

            // Validate each item in the array
            'items.*' => ['required', 'array'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],

            // Nested arrays
            'items.*.options' => ['nullable', 'array'],
            'items.*.options.*' => ['string', 'max:100'],

            // Shipping address as object
            'shipping' => ['required', 'array'],
            'shipping.street' => ['required', 'string', 'max:255'],
            'shipping.city' => ['required', 'string', 'max:100'],
            'shipping.state' => ['required', 'string', 'size:2'],
            'shipping.zip' => ['required', 'string', 'regex:/^\d{5}(-\d{4})?$/'],
            'shipping.country' => ['required', 'string', 'size:2'],

            // Optional billing address
            'billing' => ['nullable', 'array'],
            'billing.street' => ['required_with:billing', 'string', 'max:255'],
            'billing.city' => ['required_with:billing', 'string', 'max:100'],

            // Tags array
            'tags' => ['nullable', 'array', 'max:10'],
            'tags.*' => ['string', 'max:50', 'distinct'],

            // Key-value metadata
            'metadata' => ['nullable', 'array'],
            'metadata.*.key' => ['required', 'string', 'max:50'],
            'metadata.*.value' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Please add at least one item to your order.',
            'items.min' => 'Your order must contain at least one item.',
            'items.*.product_id.required' => 'Each item must have a product selected.',
            'items.*.product_id.exists' => 'Item #:position has an invalid product.',
            'items.*.quantity.min' => 'Item #:position quantity must be at least 1.',
            'tags.*.distinct' => 'Duplicate tags are not allowed.',
        ];
    }
}

// Validating array keys
class ConfigRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // Only allow specific keys
            'settings' => ['required', 'array'],
            'settings.theme' => ['required', Rule::in(['light', 'dark', 'auto'])],
            'settings.language' => ['required', 'string', 'size:2'],
            'settings.notifications' => ['required', 'array'],
            'settings.notifications.email' => ['required', 'boolean'],
            'settings.notifications.push' => ['required', 'boolean'],
            'settings.notifications.sms' => ['required', 'boolean'],
        ];
    }
}

// Dynamic array keys validation
class DynamicFormRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'fields' => ['required', 'array'],
        ];

        // Add rules for each field dynamically
        foreach ($this->input('fields', []) as $key => $value) {
            if (str_starts_with($key, 'email_')) {
                $rules["fields.{$key}"] = ['required', 'email'];
            } elseif (str_starts_with($key, 'phone_')) {
                $rules["fields.{$key}"] = ['required', 'string', 'min:10'];
            }
        }

        return $rules;
    }
}

// Validating file arrays
class GalleryUploadRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'images' => ['required', 'array', 'min:1', 'max:10'],
            'images.*' => ['required', 'image', 'mimes:jpg,png,webp', 'max:5120'],

            'documents' => ['nullable', 'array'],
            'documents.*' => ['file', 'mimes:pdf,doc,docx', 'max:10240'],

            // With metadata for each file
            'files' => ['required', 'array'],
            'files.*.file' => ['required', 'file', 'max:10240'],
            'files.*.title' => ['required', 'string', 'max:100'],
            'files.*.description' => ['nullable', 'string', 'max:500'],
        ];
    }
}

// Custom array validation rule
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueArrayValues implements ValidationRule
{
    public function __construct(
        private string $key
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_array($value)) {
            return;
        }

        $values = collect($value)->pluck($this->key);

        if ($values->count() !== $values->unique()->count()) {
            $fail("The {$attribute} contains duplicate {$this->key} values.");
        }
    }
}

// Usage
'items' => ['required', 'array', new UniqueArrayValues('product_id')],
```

## Why

- **Type safety**: Ensures arrays contain expected data types
- **Nested validation**: Deep validation of complex structures
- **Custom messages**: Clear error messages with array positions
- **Consistency**: Same validation syntax for all array types
- **Security**: Prevents unexpected data from entering the system
- **Documentation**: Rules document expected data structure

---

## 5. Security (HIGH)

**Impact:** HIGH  
**Description:** Protection against common vulnerabilities including mass assignment, SQL injection, XSS, and CSRF attacks. Laravel provides excellent security features, but developers must use them correctly. Security issues can have catastrophic consequences.

**Rules in this category:** 1

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

---

## 6. Performance (MEDIUM)

**Impact:** MEDIUM  
**Description:** Caching strategies, queue usage, and optimization techniques for growing applications. While not critical initially, performance patterns become essential as applications scale. Proper caching and queue usage can provide 2-10× improvements.

*Additional rules can be added to this category as needed.*

---

## 7. API Design (MEDIUM)

**Impact:** MEDIUM  
**Description:** RESTful API patterns, resource transformers, versioning, and consistent response formatting. Well-designed APIs are essential for frontend-backend communication, third-party integrations, and mobile applications.

*Additional rules can be added to this category as needed.*

---


## How to Use This Guide

1. **For AI Agents**: Reference specific rules by category and rule name when generating or reviewing code
2. **For Developers**: Use as a comprehensive reference for Laravel 13 best practices
3. **For Code Review**: Check implementations against these patterns
4. **For Refactoring**: Identify patterns to improve existing code

## Contributing

To add new rules:
1. Create a new `.md` file in the `rules/` directory
2. Follow the naming convention: `{category-prefix}-{rule-name}.md`
3. Include YAML frontmatter with title, impact, impactDescription, and tags
4. Provide clear examples of incorrect and correct implementations
5. Regenerate AGENTS.md by running the compilation script

## License

MIT License - Laravel Community
