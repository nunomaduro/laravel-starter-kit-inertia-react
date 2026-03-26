---
title: Optimize for Screen Reader Compatibility
impact: CRITICAL
impactDescription: "WCAG 2.1 Level A - Perceivable and operable"
tags: accessibility, screen-readers, semantic-html, aria
---

## Optimize for Screen Reader Compatibility

**Impact: CRITICAL (WCAG 2.1 Level A - Perceivable and operable)**

Design and code with screen reader users in mind. Screen readers convert visual content to audio output, requiring careful attention to how information is structured and announced.

## Incorrect

```html
<!-- ❌ Bad: Poor screen reader experience -->
<div class="breadcrumb">
  Home > Products > Electronics > Phones
</div>

<table>
  <tr>
    <td>Name</td>
    <td>Price</td>
    <td>Stock</td>
  </tr>
  <tr>
    <td>Widget</td>
    <td>$9.99</td>
    <td>&#10003;</td>
  </tr>
</table>

<div class="rating">
  &#9733;&#9733;&#9733;&#9733;&#9734;
</div>

<button class="close-btn">&times;</button>

<div class="price">
  <span class="currency">$</span>
  <span class="dollars">29</span>
  <span class="cents">99</span>
</div>

<img src="chart.png">
```

**Problems:**
- Breadcrumbs as plain text lose navigation structure and meaning
- Tables without `<th>`, `scope`, or `<caption>` are unnavigable by screen readers
- Star ratings, symbols, and split price text are announced as gibberish
- Close button with only a symbol has no accessible name
- Images without alt text are invisible to screen reader users

## Correct

```html
<!-- ✅ Good: Screen reader optimized -->
<nav aria-label="Breadcrumb">
  <ol class="breadcrumb">
    <li><a href="/">Home</a></li>
    <li><a href="/products">Products</a></li>
    <li><a href="/products/electronics">Electronics</a></li>
    <li aria-current="page">Phones</li>
  </ol>
</nav>

<table>
  <caption>Product Inventory</caption>
  <thead>
    <tr>
      <th scope="col">Name</th>
      <th scope="col">Price</th>
      <th scope="col">In Stock</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th scope="row">Widget</th>
      <td>$9.99</td>
      <td>
        <span aria-label="Yes, in stock">&#10003;</span>
      </td>
    </tr>
  </tbody>
</table>

<div class="rating" role="img" aria-label="4 out of 5 stars">
  <span aria-hidden="true">&#9733;&#9733;&#9733;&#9733;&#9734;</span>
</div>

<button class="close-btn" aria-label="Close dialog">
  <span aria-hidden="true">&times;</span>
</button>

<div class="price">
  <span class="visually-hidden">Price: $29.99</span>
  <span aria-hidden="true">
    <span class="currency">$</span>
    <span class="dollars">29</span>
    <span class="cents">99</span>
  </span>
</div>

<figure>
  <img src="chart.png" alt="Sales chart showing 25% growth in Q4">
  <figcaption>
    Quarterly sales data for 2024. Q4 shows the highest growth at 25%.
  </figcaption>
</figure>

<!-- Visually hidden utility class -->
<style>
.visually-hidden {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}
</style>
```

**Benefits:**
- Semantic breadcrumbs, tables, and landmarks provide clear navigation structure
- Symbols and visual-only content are replaced with meaningful ARIA labels
- Split text (prices, ratings) is announced as a single coherent value
- Screen reader users get the same information as sighted users

Reference: [WCAG 1.3.1 Info and Relationships](https://www.w3.org/WAI/WCAG21/Understanding/info-and-relationships.html)
