---
title: Optimize Image Loading for UX
impact: MEDIUM
impactDescription: "Prevents layout shifts and improves perceived performance"
tags: performance, images, cls, lazy-loading, responsive
---

## Optimize Image Loading for UX

**Impact: MEDIUM (Prevents layout shifts and improves perceived performance)**

Unoptimized images are a leading cause of layout shifts and slow page loads. Providing explicit dimensions, lazy loading off-screen images, and serving modern formats dramatically improves both Core Web Vitals and perceived performance.

## Incorrect

```html
<!-- ❌ Bad: no dimensions, no lazy loading, no responsive sources, missing alt -->
<img src="/photos/hero.jpg" />

<img src="/photos/team.png" />

<img src="/photos/product.jpg" class="product-thumb" />
```

```tsx
// ❌ Bad: React component with same issues
function Gallery() {
  return (
    <div>
      <img src="/photos/hero.jpg" />
      <img src="/photos/team.png" />
      {products.map((p) => (
        <img key={p.id} src={p.image} />
      ))}
    </div>
  )
}
```

**Problems:**
- Missing `width` and `height` causes Cumulative Layout Shift (CLS) as images load
- All images load eagerly, blocking the critical rendering path
- No modern format (WebP/AVIF) sources wastes bandwidth
- Missing `alt` text harms accessibility and SEO
- No `fetchpriority` hint means the browser cannot prioritize the hero image

## Correct

```html
<!-- ✅ Good: hero image with high priority, explicit dimensions, alt text -->
<img
  src="/photos/hero.jpg"
  alt="Team collaboration in the office"
  width="1200"
  height="600"
  fetchpriority="high"
/>

<!-- ✅ Good: below-fold image with lazy loading and responsive sources -->
<picture>
  <source srcset="/photos/team.avif" type="image/avif" />
  <source srcset="/photos/team.webp" type="image/webp" />
  <img
    src="/photos/team.png"
    alt="Our engineering team"
    width="800"
    height="450"
    loading="lazy"
    decoding="async"
  />
</picture>

<!-- ✅ Good: responsive image with srcset for different viewports -->
<img
  src="/photos/product-400.jpg"
  srcset="
    /photos/product-400.jpg 400w,
    /photos/product-800.jpg 800w,
    /photos/product-1200.jpg 1200w
  "
  sizes="(max-width: 600px) 400px, (max-width: 1024px) 800px, 1200px"
  alt="Product thumbnail"
  width="400"
  height="400"
  loading="lazy"
  decoding="async"
/>
```

```tsx
// ✅ Good: React component with optimized image loading
function Gallery({ products }: { products: Product[] }) {
  return (
    <div>
      {/* Hero image: high priority, no lazy loading */}
      <img
        src="/photos/hero.jpg"
        alt="Team collaboration in the office"
        width={1200}
        height={600}
        fetchPriority="high"
      />

      {/* Below-fold image: modern formats with fallback */}
      <picture>
        <source srcSet="/photos/team.avif" type="image/avif" />
        <source srcSet="/photos/team.webp" type="image/webp" />
        <img
          src="/photos/team.png"
          alt="Our engineering team"
          width={800}
          height={450}
          loading="lazy"
          decoding="async"
        />
      </picture>

      {/* Product list: lazy loaded with dimensions */}
      {products.map((p) => (
        <img
          key={p.id}
          src={p.image}
          alt={p.name}
          width={300}
          height={300}
          loading="lazy"
          decoding="async"
        />
      ))}
    </div>
  )
}
```

**Benefits:**
- Explicit `width` and `height` eliminate layout shifts by reserving space before load
- `loading="lazy"` defers off-screen images, reducing initial page weight
- `<picture>` with WebP/AVIF sources can reduce image size by 25-50%
- `fetchpriority="high"` on the hero image improves Largest Contentful Paint (LCP)
- `decoding="async"` prevents image decoding from blocking the main thread
- Proper `alt` text improves accessibility and SEO

Reference: [web.dev - Optimize images](https://web.dev/articles/fast#optimize_your_images)
