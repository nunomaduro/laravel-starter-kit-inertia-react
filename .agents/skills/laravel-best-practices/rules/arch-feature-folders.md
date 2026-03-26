---
title: Feature Folders (Domain-Driven Structure)
impact: MEDIUM
impactDescription: Better cohesion and discoverability
tags: architecture, organization, domain-driven, structure
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

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use App\Domain\Order\Events\OrderPlaced;
use App\Domain\Order\Listeners\SendOrderConfirmation;
use App\Domain\Order\Listeners\UpdateInventory;
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

```

```json
// composer.json autoload section
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
