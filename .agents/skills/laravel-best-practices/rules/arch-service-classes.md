---
title: Service Classes for Business Logic
impact: CRITICAL
impactDescription: Improves maintainability, testability, and code reusability
tags: architecture, services, separation-of-concerns, business-logic
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
class UserService
{
    // Constructor injection
    public function __construct(
        private readonly NotificationService $notifications,
        private readonly PaymentGateway $payments,
    ) {}

    // Always declare return types
    public function findOrFail(int $id): User
    {
        return User::findOrFail($id);
    }

    // Handle exceptions explicitly
    public function processPayment(Order $order): PaymentResult
    {
        try {
            return $this->payments->charge($order->total);
        } catch (PaymentFailedException $e) {
            Log::error('Payment failed', ['order' => $order->id, 'error' => $e->getMessage()]);
            throw $e;
        }
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
