---
title: Heading Hierarchy and Structure
impact: HIGH
impactDescription: "Crawlers and screen readers rely on heading structure"
tags: headings, h1, content-structure, accessibility
---

## Heading Hierarchy and Structure

**Impact: HIGH (Crawlers and screen readers rely on heading structure)**

Search engines use headings to understand content hierarchy and topical relevance. A clear heading structure also improves accessibility for screen-reader users navigating by heading landmarks.

## Incorrect

```html
<!-- ❌ Multiple h1 tags on one page -->
<h1>Welcome to Our Store</h1>
<section>
  <h1>Latest Products</h1>
  <h4>Running Shoes</h4> <!-- Skipped h2 and h3 -->
  <p>High-performance running shoes for every terrain.</p>
  <h2>Customer Reviews</h2>
</section>
<section>
  <h1>About Us</h1> <!-- Third h1 on the same page -->
  <p>We have been selling shoes since 2010.</p>
</section>

<!-- ❌ Using headings purely for styling -->
<h3 class="big-text">Free shipping on orders over $50</h3>
```

**Problems:**
- Multiple `<h1>` tags dilute the primary topic signal for crawlers
- Skipping from `<h1>` to `<h4>` breaks the logical outline and confuses assistive technology
- Using heading tags for visual styling instead of structure misleads search engines about content importance

## Correct

```html
<!-- ✅ Single h1 with target keyword, logical nesting -->
<h1>Running Shoes for Every Terrain</h1>

<section>
  <h2>Latest Products</h2>
  <h3>Trail Running Shoes</h3>
  <p>Grip-focused shoes designed for off-road surfaces.</p>
  <h3>Road Running Shoes</h3>
  <p>Lightweight cushioned shoes for pavement.</p>
</section>

<section>
  <h2>Customer Reviews</h2>
  <h3>Top-Rated This Month</h3>
  <p>See what runners are saying about our best sellers.</p>
</section>

<!-- Use CSS classes for styling, not heading tags -->
<p class="promo-banner">Free shipping on orders over $50</p>
```

**Benefits:**
- Single `<h1>` clearly signals the page topic to search engines
- Logical `h1 > h2 > h3` nesting creates a scannable outline for crawlers and screen readers
- Heading levels are never skipped, preserving document structure integrity

Reference: [Google Search Central - Headings](https://developers.google.com/search/docs/appearance/title-link)
