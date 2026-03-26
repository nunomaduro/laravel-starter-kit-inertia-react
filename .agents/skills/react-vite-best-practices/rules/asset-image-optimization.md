---
title: Optimize Image Loading and Format
impact: HIGH
impactDescription: "40-70% reduction in image payload"
tags: asset, images, optimization, webp, lazy-loading
---

## Optimize Image Loading and Format

**Impact: HIGH (40-70% reduction in image payload)**

Unoptimized images are often the largest assets, significantly impacting page load time. Proper image handling reduces bandwidth and improves Core Web Vitals.

## Incorrect

```typescript
// ❌ Bad: Large images loaded eagerly with no optimization
function Gallery() {
  return (
    <div>
      <img src="/images/hero.png" />
      <img src="/images/feature1.png" />
      <img src="/images/feature2.png" />
      <img src="/images/feature3.png" />
    </div>
  )
}
```

**Problems:**
- No lazy loading — all images downloaded immediately
- No responsive images — oversized images on small screens
- No explicit dimensions — causes Cumulative Layout Shift (CLS)
- Unoptimized PNG format — WebP/AVIF are significantly smaller
- Missing alt attributes — accessibility violation

## Correct

```typescript
// ✅ Good: Optimized image loading
function Gallery() {
  return (
    <div>
      {/* Critical above-fold image */}
      <img
        src="/images/hero.webp"
        alt="Hero banner"
        width={1200}
        height={600}
        fetchPriority="high"
      />

      {/* Below-fold images - lazy load */}
      <img
        src="/images/feature1.webp"
        alt="Feature 1"
        width={400}
        height={300}
        loading="lazy"
        decoding="async"
      />
      <img
        src="/images/feature2.webp"
        alt="Feature 2"
        width={400}
        height={300}
        loading="lazy"
        decoding="async"
      />
    </div>
  )
}
```

```typescript
// ✅ Good: Responsive images with format fallback
function ResponsiveImage() {
  return (
    <picture>
      <source
        srcSet="/images/hero-480.webp 480w,
                /images/hero-768.webp 768w,
                /images/hero-1200.webp 1200w"
        type="image/webp"
        sizes="(max-width: 480px) 480px,
               (max-width: 768px) 768px,
               1200px"
      />
      <img
        src="/images/hero-1200.jpg"
        alt="Hero image"
        width={1200}
        height={600}
        loading="lazy"
      />
    </picture>
  )
}
```

```typescript
// ✅ Good: Vite image optimization plugin
// vite.config.ts
import { defineConfig } from 'vite'
import { ViteImageOptimizer } from 'vite-plugin-image-optimizer'

export default defineConfig({
  plugins: [
    ViteImageOptimizer({
      png: { quality: 80 },
      jpeg: { quality: 80 },
      webp: { lossless: true },
    }),
  ],
})
```

```typescript
// ✅ Good: Reusable Image component
interface ImageProps {
  src: string
  alt: string
  width: number
  height: number
  priority?: boolean
  className?: string
}

export function Image({
  src,
  alt,
  width,
  height,
  priority = false,
  className,
}: ImageProps) {
  return (
    <img
      src={src}
      alt={alt}
      width={width}
      height={height}
      loading={priority ? 'eager' : 'lazy'}
      decoding={priority ? 'sync' : 'async'}
      fetchPriority={priority ? 'high' : 'auto'}
      className={className}
    />
  )
}
```

```typescript
// ✅ Good: Inline small images and use URL imports for backgrounds
// vite.config.ts
export default defineConfig({
  build: {
    assetsInlineLimit: 4096, // Inline images < 4KB as base64
  },
})

// For CSS background images
import heroImage from './images/hero.webp?url'

function Hero() {
  return (
    <div
      className="hero"
      style={{ backgroundImage: `url(${heroImage})` }}
    />
  )
}
```

**Benefits:**
- 40-70% reduction in image payload with modern formats (WebP, AVIF)
- Better LCP (Largest Contentful Paint) with priority loading for hero images
- Reduced CLS (Cumulative Layout Shift) by specifying explicit dimensions
- Lazy loading defers off-screen images, speeding up initial page load
- Automatic inlining of small images eliminates extra HTTP requests

Reference: [Vite Static Asset Handling](https://vitejs.dev/guide/assets.html) | [web.dev Image Optimization](https://web.dev/fast/#optimize-your-images)
