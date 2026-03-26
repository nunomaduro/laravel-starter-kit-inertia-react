---
title: Content Parity Between Mobile and Desktop
impact: MEDIUM
impactDescription: "Google indexes mobile version — hidden content won't rank"
tags: mobile-first-indexing, content-parity, responsive
---

## Content Parity Between Mobile and Desktop

**Impact: MEDIUM (Google indexes mobile version -- hidden content won't rank)**

With mobile-first indexing, Google primarily crawls and indexes the mobile version of your page. If content, structured data, or images are present on desktop but hidden on mobile, Google may never see them. Both versions must contain the same meaningful content, even if the layout differs.

## Incorrect

```html
<!-- ❌ Bad: hiding content on mobile with display:none -->
<div class="product-details">
  <h1>Premium Running Shoes</h1>
  <p>Lightweight and responsive design.</p>

  <!-- This content is invisible to Google's mobile-first crawler -->
  <div class="desktop-only">
    <h2>Technical Specifications</h2>
    <table>
      <tr><td>Weight</td><td>245g</td></tr>
      <tr><td>Drop</td><td>8mm</td></tr>
      <tr><td>Cushioning</td><td>React foam</td></tr>
    </table>
  </div>
</div>
```

```css
/* ❌ Bad: removing content from mobile view */
@media (max-width: 768px) {
  .desktop-only {
    display: none;
  }

  /* Different heading text per viewport */
  .hero h1 .full-title {
    display: none;
  }
  .hero h1 .short-title {
    display: block;
  }
}

@media (min-width: 769px) {
  .hero h1 .full-title {
    display: block;
  }
  .hero h1 .short-title {
    display: none;
  }
}
```

**Problems:**
- `display: none` on mobile removes the technical specifications from the indexed version of the page
- Different heading text per viewport means Google indexes the shorter mobile heading, not the more descriptive desktop version
- Structured data referencing desktop-only content creates a mismatch that Google may penalize
- Hidden images on mobile are not indexed, losing image search traffic

## Correct

```html
<!-- ✅ Good: same content on both versions, responsive layout only -->
<div class="product-details">
  <h1>Premium Running Shoes</h1>
  <p>Lightweight and responsive design.</p>

  <div class="specs">
    <h2>Technical Specifications</h2>
    <table>
      <tr><td>Weight</td><td>245g</td></tr>
      <tr><td>Drop</td><td>8mm</td></tr>
      <tr><td>Cushioning</td><td>React foam</td></tr>
    </table>
  </div>
</div>
```

```css
/* ✅ Good: CSS changes layout and presentation, never removes content */
.product-details {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
}

@media (min-width: 768px) {
  .product-details {
    grid-template-columns: 1fr 1fr;
  }
}

/* Reorder sections visually without hiding them */
.specs {
  order: 2;
}

@media (min-width: 768px) {
  .specs {
    order: 1;
  }
}

/* Use a scrollable container instead of hiding wide tables */
.specs table {
  width: 100%;
}

@media (max-width: 768px) {
  .specs {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }
}
```

```tsx
// ✅ Good: same content rendered for all viewports, styled responsively
export default function ProductSpecs({ specs }: { specs: { label: string; value: string }[] }) {
  return (
    <section className="specs">
      <h2>Technical Specifications</h2>
      <div className="specs-table-wrapper">
        <table>
          <tbody>
            {specs.map((spec) => (
              <tr key={spec.label}>
                <td>{spec.label}</td>
                <td>{spec.value}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </section>
  );
}
```

**Benefits:**
- All content is present in the DOM for both mobile and desktop, ensuring Google indexes everything
- CSS Grid and `order` allow visual rearrangement without removing elements from the document flow
- Horizontal scrolling for wide tables preserves all data on small screens without hiding it
- Consistent headings across viewports mean Google always indexes the same, descriptive title
- Structured data matches the visible content on both versions, avoiding indexing penalties

Reference: [Mobile-first indexing best practices](https://developers.google.com/search/docs/crawling-indexing/mobile/mobile-sites-mobile-first-indexing)
