---
title: SPA Routing and Crawlability
impact: HIGH
impactDescription: "Search engines need real links and unique URLs to crawl"
tags: routing, crawlability, links, inertia, laravel
---

## SPA Routing and Crawlability

**Impact: HIGH (Search engines need real links and unique URLs to crawl)**

Search engines discover pages by following links. If your application uses click handlers instead of real anchor tags, crawlers cannot discover or index your pages. With Laravel + Inertia.js, routing is handled server-side by Laravel, but the frontend must still use proper `<Link>` components to render crawlable `<a>` elements.

## Incorrect

```tsx
// ❌ Bad: onClick-only navigation with no real links
import { router } from '@inertiajs/react';

export default function Navigation() {
  return (
    <nav>
      <button onClick={() => router.visit('/products')}>Products</button>
      <span onClick={() => router.visit('/about')} style={{ cursor: "pointer" }}>
        About Us
      </span>
      <div onClick={() => window.location.hash = "#contact"}>Contact</div>
    </nav>
  );
}
```

```tsx
// ❌ Bad: product card with no crawlable link
import { router } from '@inertiajs/react';

function ProductCard({ product }: { product: { slug: string; name: string } }) {
  return (
    <div
      className="product-card"
      onClick={() => router.visit(`/products/${product.slug}`)}
    >
      <h3>{product.name}</h3>
      {/* No <a> tag — search engines cannot follow this link */}
    </div>
  );
}
```

**Problems:**
- `<button>` and `<span>` with `onClick` or `router.visit()` are not crawlable; search engines cannot follow them
- No `<a href>` means users cannot right-click to open in a new tab, copy the link, or share the URL
- `router.visit()` works for navigation but does not render a crawlable HTML element

## Correct

```tsx
// ✅ Good: Inertia <Link> renders real <a> tags
import { Link } from '@inertiajs/react';

export default function Navigation() {
  return (
    <nav>
      <Link href="/products">Products</Link>
      <Link href="/about">About Us</Link>
      <Link href="/contact">Contact</Link>
    </nav>
  );
}
```

```tsx
// ✅ Good: product card with crawlable Inertia Link
import { Link } from '@inertiajs/react';

function ProductCard({ product }: { product: { slug: string; name: string; summary: string } }) {
  return (
    <article className="product-card">
      <h3>
        <Link href={`/products/${product.slug}`}>{product.name}</Link>
      </h3>
      <p>{product.summary}</p>
    </article>
  );
}
```

```php
// ✅ Laravel: proper route definitions with named routes
// routes/web.php
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/about', [AboutController::class, 'index'])->name('about');

// 404 handling is automatic — Laravel returns a 404 response for undefined routes
// Customize with resources/views/errors/404.blade.php
```

**Benefits:**
- Inertia's `<Link>` renders real `<a href>` elements that crawlers can follow to discover all pages
- Laravel handles routing server-side, so every page has a unique, indexable URL by default
- Inertia's first page load is server-rendered HTML, making content immediately visible to crawlers
- Users can right-click, open in new tab, copy link, and share URLs naturally
- Laravel's built-in 404 handling returns proper status codes for undefined routes

Reference: [Google - Links crawlable](https://developers.google.com/search/docs/crawling-indexing/links-crawlable)
