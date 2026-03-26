---
title: Resource Hints (Preconnect, Preload, Prefetch)
impact: HIGH
impactDescription: "Reduces connection latency and speeds up critical resources"
tags: preconnect, preload, prefetch, dns-prefetch, performance
---

## Resource Hints (Preconnect, Preload, Prefetch)

**Impact: HIGH (Reduces connection latency and speeds up critical resources)**

Resource hints tell the browser about resources it will need, allowing it to start DNS lookups, TCP connections, or full downloads before they would normally be discovered. Used correctly, they shave hundreds of milliseconds off critical resource loading. Used excessively, they waste bandwidth and compete with truly critical resources.

## Incorrect

```html
<!-- ❌ Bad: no resource hints, or preloading everything -->
<head>
  <!-- No preconnect for critical third-party origins -->
  <link rel="stylesheet" href="https://cdn.example.com/styles.css" />
  <script src="https://analytics.example.com/tracker.js"></script>

  <!-- Preloading non-critical resources wastes bandwidth -->
  <link rel="preload" as="image" href="/images/footer-bg.jpg" />
  <link rel="preload" as="image" href="/images/sidebar-ad.jpg" />
  <link rel="preload" as="image" href="/images/testimonial-1.jpg" />
  <link rel="preload" as="image" href="/images/testimonial-2.jpg" />
  <link rel="preload" as="font" href="/fonts/decorative.woff2" />
  <link rel="preload" as="script" href="/js/chat-widget.js" />
</head>
```

**Problems:**
- Without `preconnect`, the browser must wait until it discovers each third-party resource before starting DNS + TCP + TLS (300-500ms per origin)
- Preloading non-critical resources (footer images, chat widgets) competes with the LCP image and critical CSS for bandwidth
- Too many preload hints cause the browser to ignore priority signals, negating the benefit entirely

## Correct

```html
<!-- ✅ Good: targeted preconnect and preload for critical path only -->
<head>
  <!-- Preconnect to critical third-party origins (limit to 2-4) -->
  <link rel="preconnect" href="https://cdn.example.com" crossorigin />
  <link rel="preconnect" href="https://api.example.com" />

  <!-- dns-prefetch as fallback for browsers that don't support preconnect -->
  <link rel="dns-prefetch" href="https://cdn.example.com" />

  <!-- Preload only critical, above-the-fold resources -->
  <link
    rel="preload"
    as="image"
    href="/images/hero-banner.webp"
    fetchpriority="high"
  />
  <link
    rel="preload"
    as="font"
    type="font/woff2"
    href="/fonts/inter-400.woff2"
    crossorigin
  />
  <link rel="preload" as="style" href="/css/critical.css" />

  <!-- Prefetch resources needed for likely next navigation -->
  <link rel="prefetch" href="/js/checkout.js" />
</head>
```

```tsx
// ✅ React SPA: resource hints via Inertia.js Head component
import { Head } from '@inertiajs/react';

export default function AppLayout({ children }: { children: React.ReactNode }) {
  return (
    <>
      <Head>
        <link rel="preconnect" href="https://cdn.example.com" crossOrigin="" />
        <link rel="dns-prefetch" href="https://cdn.example.com" />
      </Head>
      {children}
    </>
  );
}
```

**Benefits:**
- `preconnect` eliminates 300-500ms of connection setup for critical third-party origins
- `dns-prefetch` provides a graceful fallback in older browsers that do not support `preconnect`
- Limiting preloads to 2-3 critical resources ensures they receive maximum bandwidth priority
- `prefetch` prepares resources for future navigations during idle time without competing with the current page
- `fetchpriority="high"` on the preloaded LCP image ensures it is prioritized above other preloaded resources

Reference: [Preconnect to required origins](https://developer.chrome.com/docs/lighthouse/performance/uses-rel-preconnect)
