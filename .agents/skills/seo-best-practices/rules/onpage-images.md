---
title: Image SEO and Alt Text
impact: HIGH
impactDescription: "Images appear in Google Image Search and affect CWV"
tags: images, alt-text, optimization, accessibility
---

## Image SEO and Alt Text

**Impact: HIGH (Images appear in Google Image Search and affect CWV)**

Properly optimized images improve page load performance (directly affecting Core Web Vitals), surface content in Google Image Search, and provide essential context for screen-reader users.

## Incorrect

```html
<!-- ❌ Missing alt text, generic filename, no dimensions -->
<img src="/uploads/IMG_1234.jpg" />

<!-- ❌ Decorative alt text on a meaningful image -->
<img src="/photos/photo1.png" alt="image" />

<!-- ❌ No width/height causes layout shift (hurts CLS) -->
<img src="/products/shoe.jpg" alt="Running shoe" />

<!-- ❌ Serving oversized unoptimized images -->
<img
  src="/uploads/hero-banner-4000x2000.png"
  alt="Homepage banner"
  width="800"
  height="400"
/>
```

**Problems:**
- Missing `alt` text means crawlers and screen readers get zero context about the image
- Generic filenames like `IMG_1234.jpg` waste a ranking signal; Google reads filenames
- Omitting `width` and `height` causes Cumulative Layout Shift (CLS) as images load
- Serving a 4000px PNG when an 800px WebP would suffice wastes bandwidth and hurts LCP

## Correct

```html
<!-- ✅ Descriptive alt text, meaningful filename, explicit dimensions -->
<img
  src="/images/trail-running-shoe-side-view.webp"
  alt="Blue trail running shoe with Vibram sole, side view"
  width="800"
  height="600"
  loading="lazy"
/>

<!-- ✅ Responsive images with modern format sources -->
<picture>
  <source
    srcset="/images/hero-homepage.avif"
    type="image/avif"
  />
  <source
    srcset="/images/hero-homepage.webp"
    type="image/webp"
  />
  <img
    src="/images/hero-homepage.jpg"
    alt="Runner crossing a mountain trail at sunrise"
    width="1200"
    height="630"
    fetchpriority="high"
  />
</picture>

<!-- ✅ Decorative images use empty alt to be skipped by screen readers -->
<img src="/images/divider-line.svg" alt="" role="presentation" />
```

```tsx
// ✅ React component with proper image optimization
interface Product {
  slug: string;
  name: string;
  color: string;
  category: string;
}

function ProductImage({ product }: { product: Product }) {
  return (
    <img
      src={`/images/products/${product.slug}.webp`}
      alt={`${product.name} — ${product.color}, ${product.category}`}
      width={600}
      height={450}
      loading="lazy"
      decoding="async"
      sizes="(max-width: 768px) 100vw, 50vw"
    />
  );
}
```

**Benefits:**
- Descriptive `alt` text and filenames provide keyword signals for Google Image Search
- Explicit `width` and `height` eliminate layout shift, improving CLS scores
- `<picture>` with WebP/AVIF sources reduces file size by 25-50% over JPEG/PNG
- `loading="lazy"` defers off-screen images, improving LCP for above-the-fold content
- `fetchpriority="high"` on hero images tells the browser to prioritize the LCP element

Reference: [Google Search Central - Image SEO](https://developers.google.com/search/docs/appearance/google-images)
