---
title: Internal Linking Strategy
impact: HIGH
impactDescription: "Distributes page authority and aids discoverability"
tags: internal-links, anchor-text, navigation
---

## Internal Linking Strategy

**Impact: HIGH (Distributes page authority and aids discoverability)**

Internal links pass PageRank between pages, help crawlers discover content, and establish topical relationships. A well-planned internal linking structure ensures every page is reachable and properly weighted.

## Incorrect

```html
<!-- ❌ Generic "click here" anchor text -->
<p>
  We wrote a guide about page speed optimization.
  <a href="/blog/page-speed">Click here</a> to read it.
</p>

<!-- ❌ Navigation driven entirely by JavaScript -->
<div onclick="window.location='/pricing'">View Pricing</div>

<!-- ❌ Orphan page — no internal links point to it -->
<!-- /blog/advanced-caching exists but is never linked from any other page -->
```

```tsx
// ❌ Inertia: using router.visit() instead of <Link>
import { router } from '@inertiajs/react';

function ProductCard({ product }: { product: Product }) {
  return (
    <div
      className="product-card"
      onClick={() => router.visit(`/products/${product.slug}`)}
    >
      <h3>{product.name}</h3>
      <p>{product.summary}</p>
      {/* No <a> tag — search engines cannot follow this link */}
    </div>
  );
}
```

**Problems:**
- "Click here" gives crawlers no context about the destination page's topic
- JavaScript-only navigation is invisible to crawlers that do not execute JS
- Orphan pages with zero internal links may never be crawled or indexed

## Correct

```html
<!-- ✅ Descriptive anchor text with contextual relevance -->
<p>
  Improve load times with our
  <a href="/blog/page-speed">page speed optimization guide</a>,
  covering image compression, caching, and lazy loading.
</p>

<!-- ✅ Breadcrumb navigation keeping pages within 3 clicks of homepage -->
<nav aria-label="Breadcrumb">
  <ol>
    <li><a href="/">Home</a></li>
    <li><a href="/blog">Blog</a></li>
    <li><a href="/blog/page-speed" aria-current="page">Page Speed Optimization</a></li>
  </ol>
</nav>

<!-- ✅ Related posts section linking to deeper content -->
<section>
  <h2>Related Articles</h2>
  <ul>
    <li><a href="/blog/advanced-caching">Advanced Caching Strategies</a></li>
    <li><a href="/blog/image-formats">Choosing the Right Image Format</a></li>
  </ul>
</section>
```

```tsx
// ✅ Inertia.js: crawlable links with <Link> component
import { Link } from '@inertiajs/react';

function ProductCard({ product }: { product: Product }) {
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

**Benefits:**
- Descriptive anchor text signals topic relevance to crawlers for both source and destination pages
- Breadcrumbs keep every page within 3 clicks of the homepage, improving crawl efficiency
- Using `<a>` tags ensures links are discoverable without JavaScript execution
- Related-content sections eliminate orphan pages and distribute link equity

Reference: [Google Search Central - Links](https://developers.google.com/search/docs/crawling-indexing/links-crawlable)
