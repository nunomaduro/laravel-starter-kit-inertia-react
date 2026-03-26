---
title: Modern Image Formats (WebP/AVIF)
impact: HIGH
impactDescription: "30-50% smaller files vs JPEG/PNG"
tags: images, webp, avif, performance
---

## Modern Image Formats (WebP/AVIF)

**Impact: HIGH (30-50% smaller files vs JPEG/PNG)**

Serving images in modern formats like AVIF and WebP dramatically reduces file sizes without visible quality loss. Smaller images mean faster page loads, lower bandwidth costs, and better Core Web Vitals scores. Using the `<picture>` element with fallbacks ensures all browsers receive the best format they support.

## Incorrect

```html
<!-- ❌ Bad: serving only JPEG with no responsive sizing -->
<img src="/images/product-hero.jpg" alt="Product showcase" />

<img
  src="/images/team-photo.png"
  alt="Our team"
  width="1920"
  height="1080"
/>
```

**Problems:**
- JPEG and PNG files are significantly larger than WebP/AVIF equivalents at the same visual quality
- No `srcset` means the browser downloads the full-size image on all devices, wasting bandwidth on mobile
- No fallback chain means you cannot adopt newer formats without breaking older browsers

## Correct

```html
<!-- ✅ Good: picture element with AVIF/WebP/JPEG fallback and responsive srcset -->
<picture>
  <source
    type="image/avif"
    srcset="
      /images/product-hero-400.avif 400w,
      /images/product-hero-800.avif 800w,
      /images/product-hero-1200.avif 1200w
    "
    sizes="(max-width: 600px) 100vw, (max-width: 1024px) 80vw, 1200px"
  />
  <source
    type="image/webp"
    srcset="
      /images/product-hero-400.webp 400w,
      /images/product-hero-800.webp 800w,
      /images/product-hero-1200.webp 1200w
    "
    sizes="(max-width: 600px) 100vw, (max-width: 1024px) 80vw, 1200px"
  />
  <img
    src="/images/product-hero-1200.jpg"
    srcset="
      /images/product-hero-400.jpg 400w,
      /images/product-hero-800.jpg 800w,
      /images/product-hero-1200.jpg 1200w
    "
    sizes="(max-width: 600px) 100vw, (max-width: 1024px) 80vw, 1200px"
    alt="Product showcase"
    width="1200"
    height="800"
    loading="lazy"
    decoding="async"
  />
</picture>
```

```tsx
// ✅ React: responsive image with modern format sources
export default function ProductCard({ product }: { product: { name: string; image: string } }) {
  return (
    <picture>
      <source
        type="image/avif"
        srcSet={`${product.imageBase}-400.avif 400w, ${product.imageBase}-800.avif 800w`}
        sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"
      />
      <source
        type="image/webp"
        srcSet={`${product.imageBase}-400.webp 400w, ${product.imageBase}-800.webp 800w`}
        sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"
      />
      <img
        src={product.imageUrl}
        alt={product.name}
        width={800}
        height={600}
        loading="lazy"
        decoding="async"
      />
    </picture>
  );
}
```

**Benefits:**
- AVIF offers 50% savings over JPEG; WebP offers 30% savings, with automatic fallback to JPEG for older browsers
- Responsive `srcset` with `sizes` ensures the browser downloads only the appropriate resolution for each viewport
- Explicit `width` and `height` prevent layout shifts (CLS) while the image loads
- `decoding="async"` prevents the image decode from blocking the main thread

Reference: [Use modern image formats](https://web.dev/articles/choose-the-right-image-format)
