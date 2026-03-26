---
title: Largest Contentful Paint Optimization
impact: CRITICAL
impactDescription: "Must be under 2.5s (Google ranking signal)"
tags: core-web-vitals, lcp, performance
---

## Largest Contentful Paint Optimization

**Impact: CRITICAL (Must be under 2.5s (Google ranking signal))**

LCP measures how long it takes for the largest visible element (usually a hero image or heading) to render. Google uses LCP as a direct ranking signal, and pages exceeding 2.5s risk lower search positions and higher bounce rates.

## Incorrect

```html
<!-- ❌ Bad: lazy-loading the hero image, no preload, render-blocking CSS -->
<head>
  <link rel="stylesheet" href="/css/all-styles.css" />
  <link rel="stylesheet" href="/css/animations.css" />
  <link rel="stylesheet" href="/css/third-party-widget.css" />
</head>
<body>
  <section class="hero">
    <img
      src="/images/hero-banner.jpg"
      loading="lazy"
      alt="Welcome to our platform"
    />
  </section>
</body>
```

**Problems:**
- `loading="lazy"` on the hero image delays the LCP element, as the browser defers loading until it enters the viewport
- No `<link rel="preload">` means the browser discovers the image only after parsing the HTML and CSS
- Multiple render-blocking stylesheets delay first render, pushing LCP further out

## Correct

```html
<!-- ✅ Good: preloaded hero image with fetchpriority, non-blocking CSS -->
<head>
  <link
    rel="preload"
    as="image"
    href="/images/hero-banner.webp"
    fetchpriority="high"
  />
  <link rel="stylesheet" href="/css/critical.css" />
  <link
    rel="stylesheet"
    href="/css/non-critical.css"
    media="print"
    onload="this.media='all'"
  />
</head>
<body>
  <section class="hero">
    <img
      src="/images/hero-banner.webp"
      fetchpriority="high"
      width="1200"
      height="600"
      alt="Welcome to our platform"
    />
  </section>
</body>
```

```tsx
// ✅ React: hero image with fetchPriority="high" and no lazy loading
export default function HeroSection() {
  return (
    <section className="hero">
      <img
        src="/images/hero-banner.webp"
        alt="Welcome to our platform"
        width={1200}
        height={600}
        fetchPriority="high"
      />
    </section>
  );
}
```

**Benefits:**
- `fetchpriority="high"` tells the browser to prioritize the hero image over other resources
- `<link rel="preload">` starts fetching the image before the browser encounters the `<img>` tag
- Non-critical CSS is deferred using the `media="print"` trick, unblocking initial render
- WebP format reduces image payload, further improving load time

Reference: [Optimize Largest Contentful Paint](https://web.dev/articles/optimize-lcp)
