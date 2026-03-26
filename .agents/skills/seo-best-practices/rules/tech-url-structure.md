---
title: SEO-Friendly URL Structure
impact: HIGH
impactDescription: "Clean URLs improve CTR and crawlability"
tags: urls, routing, technical-seo
---

## SEO-Friendly URL Structure

**Impact: HIGH (Clean URLs improve CTR and crawlability)**

URLs are visible in search results and influence both click-through rates and crawl efficiency. Clean, descriptive URLs help users and search engines understand the page content before visiting it. Changing URLs without proper redirects causes 404 errors and lost link equity.

## Incorrect

```
❌ Bad URL patterns:

https://example.com/index.php?page=product&id=4827&cat=12
https://example.com/Products/Running_Shoes/ITEM-4827.html
https://example.com/shop/cat/12/subcat/45/product/4827/view/detail/ref/homepage
https://EXAMPLE.COM/Our-Amazing-Collection-Of-The-Best-Running-Shoes-For-Marathon-Training-2026
https://example.com/p/4827
```

```php
// ❌ Bad: Laravel routes with IDs and query params as primary URLs
Route::get('/product', function (Request $request) {
    $product = Product::findOrFail($request->query('id'));
    return view('product.show', compact('product'));
});
// Result: /product?id=4827
```

```tsx
// ❌ Bad: Inertia page using only numeric ID in URL
// Laravel route: Route::get('/products/{product}', ...)
// Result: /products/4827 — no keywords in URL

export default function ProductPage({ product }: { product: { id: number; name: string } }) {
  // URL is /products/4827 — no keyword context for search engines
  return <div>{product.name}</div>;
}
```

**Problems:**
- Query parameter URLs are harder for search engines to crawl and provide no keyword context
- Uppercase letters create duplicate URL variations (servers may treat `/Products` and `/products` differently)
- Underscores are not treated as word separators by Google (`running_shoes` is one token, not two)
- Excessively long URLs are truncated in search results and harder to share
- Numeric-only slugs provide no content signal to users or crawlers

## Correct

```
✅ Good URL patterns:

https://example.com/running-shoes
https://example.com/running-shoes/nike-air-zoom-pegasus
https://example.com/blog/marathon-training-guide
https://example.com/blog/marathon-training-guide/nutrition-tips
```

```php
// ✅ Laravel: slug-based routing with 301 redirects for old URLs
// routes/web.php
Route::get('/products/{product:slug}', [ProductController::class, 'show'])
    ->name('products.show');

// Redirect old query-param URLs to new slug URLs
Route::get('/product', function (Request $request) {
    $product = Product::findOrFail($request->query('id'));
    return redirect()->route('products.show', $product->slug, 301);
});

// app/Models/Product.php
class Product extends Model
{
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // Auto-generate slug from name on creation
    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            $product->slug = Str::slug($product->name);
        });
    }
}
```

```php
// ✅ Laravel: middleware to enforce lowercase URLs with 301 redirect
// app/Http/Middleware/LowercaseUrls.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LowercaseUrls
{
    public function handle(Request $request, Closure $next)
    {
        $url = $request->getRequestUri();
        $lowercase = strtolower($url);

        if ($url !== $lowercase) {
            return redirect($lowercase, 301);
        }

        return $next($request);
    }
}
```

**Benefits:**
- Hyphenated, lowercase slugs are treated as separate words by Google, improving keyword matching
- Short, descriptive URLs display fully in search results and improve click-through rates
- 301 redirects preserve link equity when URLs change, preventing SEO loss during migrations
- Slug-based routing eliminates duplicate content from query parameter variations
- Middleware enforcement ensures URL consistency across the entire application automatically

Reference: [Google's URL Structure Guidelines](https://developers.google.com/search/docs/crawling-indexing/url-structure)
