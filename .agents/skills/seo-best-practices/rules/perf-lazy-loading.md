---
title: Lazy Loading Implementation
impact: HIGH
impactDescription: "Reduces initial page weight and improves LCP"
tags: lazy-loading, performance, images
---

## Lazy Loading Implementation

**Impact: HIGH (Reduces initial page weight and improves LCP)**

Native lazy loading defers off-screen images until the user scrolls near them, reducing initial page weight and speeding up the critical rendering path. However, applying lazy loading to the LCP element delays the most important visual content and directly hurts your Core Web Vitals score.

## Incorrect

```html
<!-- ❌ Bad: lazy loading the hero/LCP image -->
<section class="hero">
  <img
    src="/images/hero-banner.jpg"
    loading="lazy"
    alt="Welcome to our store"
  />
</section>

<!-- ❌ Bad: using a custom JS lazy loader for all images -->
<img data-src="/images/product-1.jpg" class="lazyload" alt="Product 1" />
<img data-src="/images/product-2.jpg" class="lazyload" alt="Product 2" />
<script src="/js/lazysizes.min.js"></script>
```

**Problems:**
- `loading="lazy"` on the hero image causes the browser to deprioritize the LCP element, delaying the largest paint
- Custom JavaScript lazy loaders add bundle weight, introduce a runtime dependency, and are invisible to the browser's preload scanner
- Images using `data-src` instead of `src` are not discoverable by the browser until JavaScript executes

## Correct

```html
<!-- ✅ Good: hero image loads eagerly with high priority -->
<section class="hero">
  <img
    src="/images/hero-banner.webp"
    fetchpriority="high"
    width="1200"
    height="600"
    alt="Welcome to our store"
  />
</section>

<!-- ✅ Good: below-fold images use native lazy loading -->
<section class="product-grid">
  <img
    src="/images/product-1.webp"
    loading="lazy"
    decoding="async"
    width="400"
    height="300"
    alt="Product 1"
  />
  <img
    src="/images/product-2.webp"
    loading="lazy"
    decoding="async"
    width="400"
    height="300"
    alt="Product 2"
  />
</section>
```

```tsx
// ✅ React: eager loading for LCP image, lazy loading for below-fold images
export default function HomePage() {
  return (
    <>
      {/* LCP element: no lazy loading, high fetch priority */}
      <img
        src="/images/hero-banner.webp"
        alt="Welcome to our store"
        width={1200}
        height={600}
        fetchPriority="high"
      />

      {/* Below-fold: native lazy loading */}
      <div className="product-grid">
        <img
          src="/images/product-1.webp"
          alt="Product 1"
          width={400}
          height={300}
          loading="lazy"
          decoding="async"
        />
      </div>
    </>
  );
}
```

**Benefits:**
- `fetchpriority="high"` on the LCP element ensures it is fetched before other resources, improving LCP
- Native `loading="lazy"` requires no JavaScript, works with the browser's preload scanner, and is supported by all modern browsers
- Real `src` attributes allow the browser to discover images immediately, unlike `data-src` patterns
- Explicit `width` and `height` reserve layout space and prevent CLS

Reference: [Browser-level image lazy loading](https://web.dev/articles/browser-level-image-lazy-loading)
