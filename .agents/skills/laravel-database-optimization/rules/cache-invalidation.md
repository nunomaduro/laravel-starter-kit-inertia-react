---
title: Automate Cache Invalidation with Model Observers
impact: HIGH
impactDescription: "Prevents stale data by coupling cache lifecycle to model events"
tags: cache, invalidation, observers, events
---

## Automate Cache Invalidation with Model Observers

**Impact: HIGH (Prevents stale data by coupling cache lifecycle to model events)**

Relying on developers to manually call `Cache::forget()` after every model update is error-prone and leads to stale cached data. Automating invalidation through model observers or lifecycle hooks guarantees the cache stays consistent with the database.

## Incorrect

```php
// ❌ Manual cache invalidation scattered across controllers
class ProductController extends Controller
{
    public function update(Request $request, Product $product)
    {
        $product->update($request->validated());

        // Developer must remember to clear cache everywhere products change
        Cache::forget('products:featured');
        Cache::forget("products:{$product->id}");
        Cache::forget("categories:{$product->category_id}:products");

        return redirect()->back();
    }

    public function destroy(Product $product)
    {
        $product->delete();

        // Easy to forget cache keys here — stale data served
        Cache::forget("products:{$product->id}");
        // Forgot to clear 'products:featured' — now stale
    }
}
```

**Problems:**
- Cache keys must be tracked and cleared manually in every controller method
- Missing a single `Cache::forget()` leads to stale data served to users
- Cache logic is duplicated across controllers, commands, jobs, and listeners
- New developers are unaware of which cache keys need clearing

## Correct

```php
// ✅ Option 1: Model observer — centralized cache invalidation
// app/Observers/ProductObserver.php
class ProductObserver
{
    public function saved(Product $product): void
    {
        Cache::forget("products:{$product->id}");
        Cache::forget('products:featured');
        Cache::forget("categories:{$product->category_id}:products");
    }

    public function deleted(Product $product): void
    {
        Cache::forget("products:{$product->id}");
        Cache::forget('products:featured');
        Cache::forget("categories:{$product->category_id}:products");
    }
}

// Register in AppServiceProvider or use the ObservedBy attribute
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy(ProductObserver::class)]
class Product extends Model
{
    // ...
}

// ✅ Option 2: Inline booted() — for simpler models
class Product extends Model
{
    protected static function booted(): void
    {
        static::saved(function (Product $product) {
            Cache::forget("products:{$product->id}");
            Cache::forget('products:featured');
        });

        static::deleted(function (Product $product) {
            Cache::forget("products:{$product->id}");
            Cache::forget('products:featured');
        });
    }
}
```

**Benefits:**
- Cache invalidation runs automatically on every save and delete, regardless of where the change originates
- Single source of truth for which cache keys relate to which model
- Works for changes made via controllers, commands, jobs, tinker, and queued processes
- `ObservedBy` attribute (Laravel 11+) makes the binding explicit and discoverable

Reference: [Laravel Model Observers](https://laravel.com/docs/13.x/eloquent#observers)
