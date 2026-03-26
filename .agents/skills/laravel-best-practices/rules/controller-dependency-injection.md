---
title: Dependency Injection in Controllers
impact: HIGH
impactDescription: Testable and loosely coupled controllers
tags: controllers, dependency-injection, testability
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
```

```php
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
```

```php
// Service class with injected dependencies
namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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
```

```php
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
```

```php
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
