---
title: Add ARIA Labels to Interactive Elements
impact: CRITICAL
impactDescription: "WCAG 2.1 Level A - Provides accessible names"
tags: accessibility, aria, labels, screen-readers
---

## Add ARIA Labels to Interactive Elements

**Impact: CRITICAL (WCAG 2.1 Level A - Provides accessible names)**

Use ARIA (Accessible Rich Internet Applications) labels to provide accessible names and descriptions for elements that lack visible text or need additional context for assistive technologies.

## Incorrect

```html
<!-- ❌ Bad: Missing accessible names -->
<button>
  <svg viewBox="0 0 24 24">
    <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
  </svg>
</button>

<div class="search-box">
  <input type="text">
  <button>
    <img src="search-icon.png">
  </button>
</div>

<nav>
  <ul>
    <li><a href="/">Home</a></li>
    <li><a href="/about">About</a></li>
  </ul>
</nav>
<nav>
  <ul>
    <li><a href="/docs">Documentation</a></li>
    <li><a href="/api">API Reference</a></li>
  </ul>
</nav>
```

**Problems:**
- Icon-only buttons have no accessible name for screen readers
- Search input has no label, so users do not know its purpose
- Multiple `<nav>` elements are indistinguishable without ARIA labels
- Decorative SVGs are announced unnecessarily by assistive technology

## Correct

```html
<!-- ✅ Good: Using ARIA labels appropriately -->
<button aria-label="Add new item">
  <svg viewBox="0 0 24 24" aria-hidden="true">
    <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
  </svg>
</button>

<div class="search-box" role="search">
  <label for="site-search" class="visually-hidden">Search the site</label>
  <input type="text" id="site-search" aria-describedby="search-hint">
  <span id="search-hint" class="visually-hidden">Press Enter to search</span>
  <button aria-label="Submit search">
    <img src="search-icon.png" alt="" aria-hidden="true">
  </button>
</div>

<nav aria-label="Main navigation">
  <ul>
    <li><a href="/">Home</a></li>
    <li><a href="/about">About</a></li>
  </ul>
</nav>
<nav aria-label="Documentation navigation">
  <ul>
    <li><a href="/docs">Documentation</a></li>
    <li><a href="/api">API Reference</a></li>
  </ul>
</nav>
```

**Benefits:**
- Screen reader users can identify and activate icon-only buttons
- Multiple navigation regions are distinguishable
- Decorative elements are hidden from assistive technology with `aria-hidden="true"`
- Supplementary context is provided via `aria-describedby`

Reference: [WCAG 4.1.2 Name, Role, Value](https://www.w3.org/WAI/WCAG21/Understanding/name-role-value.html)
