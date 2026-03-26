---
title: Repository Pattern
impact: MEDIUM
impactDescription: Abstracts data access from business logic
tags: architecture, repository, data-access, abstraction
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
```

```php
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
```

```php
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
```

```php
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
