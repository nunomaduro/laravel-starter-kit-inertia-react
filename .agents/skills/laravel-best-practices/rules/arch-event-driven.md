---
title: Event-Driven Architecture
impact: HIGH
impactDescription: Decouples components and enables async processing
tags: architecture, events, listeners, decoupling, async
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
```

```php
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
```

```php
// Register listeners in AppServiceProvider::boot() (Laravel 11+)
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\OrderPlaced;
use App\Listeners\SendOrderConfirmation;
use App\Listeners\UpdateInventory;
use App\Listeners\TrackOrderAnalytics;
use App\Listeners\NotifyAdminOfNewOrder;
use App\Listeners\UpdateLoyaltyPoints;
use App\Listeners\SyncOrderToExternalSystems;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(OrderPlaced::class, [
            SendOrderConfirmation::class,
            UpdateInventory::class,
            TrackOrderAnalytics::class,
            NotifyAdminOfNewOrder::class,
            UpdateLoyaltyPoints::class,
            SyncOrderToExternalSystems::class,
        ]);
    }
}
```

```php
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
```

```php
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
