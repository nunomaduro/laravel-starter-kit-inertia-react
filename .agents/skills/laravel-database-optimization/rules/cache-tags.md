---
title: Use Cache Tags for Group Invalidation
impact: HIGH
impactDescription: "Flush entire cache groups in one call instead of tracking individual keys"
tags: cache, tags, redis, invalidation
---

## Use Cache Tags for Group Invalidation

**Impact: HIGH (Flush entire cache groups in one call instead of tracking individual keys)**

When related cache entries need to be invalidated together, tracking individual keys is brittle and error-prone. Cache tags let you assign labels to cached items and flush all items sharing a tag in a single operation.

## Incorrect

```php
// ❌ Tracking and clearing individual cache keys manually
class ProductService
{
    public function cacheProduct(Product $product): void
    {
        Cache::put("products:{$product->id}", $product, 3600);
        Cache::put("products:{$product->id}:reviews", $product->reviews, 3600);
        Cache::put("products:{$product->id}:related", $product->related, 3600);
    }

    public function clearProductCache(Product $product): void
    {
        // Must remember every key pattern — easy to miss one
        Cache::forget("products:{$product->id}");
        Cache::forget("products:{$product->id}:reviews");
        Cache::forget("products:{$product->id}:related");
        // Forgot "products:{$product->id}:pricing" — stale data
    }

    public function clearAllProducts(): void
    {
        // Impossible without iterating all product IDs
        // or flushing the entire cache
        Cache::flush(); // Nuclear option — clears everything
    }
}
```

**Problems:**
- Every new cache key must be tracked and added to the clear method
- Flushing all entries for a group requires knowing every key or flushing the entire cache
- Forgetting a single key in the clear method results in stale data
- No way to selectively flush a category of cached items

## Correct

```php
// ✅ Cache tags — group related entries and flush by tag
class ProductService
{
    public function getProduct(int $productId): Product
    {
        return Cache::tags(['products', "product:{$productId}"])
            ->remember("products:{$productId}", 3600, function () use ($productId) {
                return Product::with('category')->findOrFail($productId);
            });
    }

    public function getProductReviews(int $productId): Collection
    {
        return Cache::tags(['products', "product:{$productId}", 'reviews'])
            ->remember("products:{$productId}:reviews", 1800, function () use ($productId) {
                return Review::where('product_id', $productId)->latest()->get();
            });
    }

    public function clearProductCache(int $productId): void
    {
        // Flush everything tagged with this specific product
        Cache::tags(["product:{$productId}"])->flush();
    }

    public function clearAllProductCaches(): void
    {
        // Flush everything tagged 'products' — one call
        Cache::tags(['products'])->flush();
    }

    public function clearAllReviewCaches(): void
    {
        // Flush all reviews across all products
        Cache::tags(['reviews'])->flush();
    }
}
```

**Benefits:**
- Flush an entire group of cache entries with a single `flush()` call
- No need to track individual cache keys — tags define the group
- Multiple tags per entry allow flexible invalidation strategies (by product, by type, etc.)
- New cache entries automatically inherit the tag-based invalidation without code changes

> **Note:** Cache tags are only supported by Redis and Memcached drivers. The `file`, `database`, and `array` drivers do not support tags.

Reference: [Laravel Cache Tags](https://laravel.com/docs/13.x/cache#cache-tags)
