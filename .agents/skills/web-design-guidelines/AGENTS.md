# Web Design Guidelines - Complete Reference

**Version:** 2.1.0
**Date:** March 2026
**License:** MIT

## Abstract

WCAG accessibility, semantic HTML, keyboard navigation, forms, and performance patterns for inclusive web interfaces. Contains 23 rules across 4 categories. Supports both coding reference and audit mode.

## How to Audit

When asked to "audit accessibility" or "check WCAG compliance", run through each rule in this document as a checklist. For each item output **PASS**, **FAIL** (with `file:line` and fix), or **N/A**. End with a summary of pass/fail counts, WCAG level assessment, and top 3 priority fixes.

## References

- [WCAG 2.2 Quick Reference](https://www.w3.org/WAI/WCAG22/quickref/)
- [WAI-ARIA Authoring Practices](https://www.w3.org/WAI/ARIA/apg/)
- [web.dev Accessibility](https://web.dev/accessibility/)
- [MDN Accessibility](https://developer.mozilla.org/en-US/docs/Web/Accessibility)
- [The A11Y Project](https://a11yproject.com/)

---

# Sections

This file defines all sections, their ordering, impact levels, and descriptions.
The section ID (in parentheses) is the filename prefix used to group rules.

---

## 1. Accessibility (a11y)

**Impact:** CRITICAL
**Description:** WCAG 2.2 compliance patterns for inclusive web interfaces. Semantic HTML structure, heading hierarchy, keyboard navigation, focus management, ARIA labels, color contrast ratios, meaningful alt text, accessible error messages, form label associations, live regions for dynamic content, skip links, and screen reader optimization.

## 2. Forms (form)

**Impact:** HIGH
**Description:** Accessible and user-friendly form patterns. Autocomplete attributes for autofill, correct input types for mobile keyboards, clear error display with ARIA associations, user-friendly validation timing, inline validation with debounce, multi-step form progression, appropriate placeholder usage, and clear submission feedback.

## 3. Animation & Motion (motion)

**Impact:** CRITICAL
**Description:** Respecting user motion preferences. The prefers-reduced-motion media query detects users with vestibular disorders who need reduced or eliminated animations. WCAG 2.1 SC 2.3.3 (Level AAA) requires providing controls to disable non-essential animations.

## 4. Performance & UX (perf)

**Impact:** MEDIUM
**Description:** Image loading optimization and layout stability. Preventing Cumulative Layout Shift (CLS) with explicit dimensions, lazy loading below-fold images, responsive images with modern formats, skeleton placeholders, and font loading strategies that minimize visual disruption.


---

## Use Semantic HTML Elements

**Impact: CRITICAL (WCAG 2.1 Level A - Foundation for accessibility)**

Semantic HTML provides meaning to content, enabling screen readers and assistive technologies to understand page structure. It improves SEO, maintainability, and accessibility without extra effort.

## Incorrect

```tsx
// ❌ Bad: Div soup - no semantic meaning
<div className="header">
  <div className="logo">Logo</div>
  <div className="nav">
    <div onClick={handleHome}>Home</div>
    <div onClick={handleAbout}>About</div>
  </div>
</div>

<div className="main">
  <div className="article">
    <div className="title">Article Title</div>
    <div className="content">Article content...</div>
  </div>
  <div className="sidebar">Related links</div>
</div>

<div className="footer">
  Copyright 2024
</div>
```

**Problems:**
- Screen readers cannot identify page structure
- No keyboard navigation for "clickable" divs
- Search engines cannot understand content hierarchy
- Assistive technology cannot navigate sections

## Correct

```tsx
// ✅ Good: Semantic HTML - meaningful structure
<header>
  <a href="/" aria-label="Home">Logo</a>
  <nav aria-label="Main navigation">
    <ul>
      <li><a href="/">Home</a></li>
      <li><a href="/about">About</a></li>
    </ul>
  </nav>
</header>

<main>
  <article>
    <h1>Article Title</h1>
    <p>Article content...</p>
  </article>
  <aside aria-label="Related content">
    <h2>Related Links</h2>
    <ul>
      <li><a href="/related-1">Related Article 1</a></li>
    </ul>
  </aside>
</main>

<footer>
  <p>Copyright 2024</p>
</footer>
```

**Benefits:**
- Screen readers announce page structure
- Keyboard users can navigate by landmarks
- Better SEO indexing
- Easier to style with CSS
- Future-proof and maintainable

## Semantic Elements Reference

| Element | Purpose | Use For |
|---------|---------|---------|
| `<header>` | Introductory content | Page/section headers |
| `<nav>` | Navigation links | Main nav, breadcrumbs |
| `<main>` | Main content | Primary page content (one per page) |
| `<article>` | Self-contained content | Blog posts, news articles |
| `<section>` | Thematic grouping | Chapters, tabs, grouped content |
| `<aside>` | Tangentially related | Sidebars, pull quotes |
| `<footer>` | Footer content | Copyright, links, contact |
| `<figure>` | Self-contained media | Images with captions |
| `<figcaption>` | Caption for figure | Image/chart descriptions |
| `<time>` | Date/time | Dates, times, durations |
| `<address>` | Contact information | Author/organization contact |
| `<mark>` | Highlighted text | Search results highlighting |

## Interactive Elements

```tsx
// ❌ Bad: Div with click handler - not accessible
<div onClick={handleClick} className="button">
  Click me
</div>

// ✅ Good: Button element - accessible by default
<button onClick={handleClick}>
  Click me
</button>

// ❌ Bad: Span as link
<span onClick={() => navigate('/about')} className="link">
  About
</span>

// ✅ Good: Anchor element
<a href="/about">About</a>

// ✅ Good: Link component (React Router/Next.js)
<Link href="/about">About</Link>
```

## Headings Hierarchy

```tsx
// ❌ Bad: Skipping heading levels
<h1>Page Title</h1>
<h3>Section Title</h3>  {/* Skipped h2 */}
<h5>Subsection</h5>     {/* Skipped h4 */}

// ✅ Good: Proper heading hierarchy
<h1>Page Title</h1>
<h2>Section Title</h2>
<h3>Subsection Title</h3>
<h2>Another Section</h2>
<h3>Its Subsection</h3>
```

## Lists

```tsx
// ❌ Bad: Not using lists for list content
<div className="menu">
  <div>Item 1</div>
  <div>Item 2</div>
  <div>Item 3</div>
</div>

// ✅ Good: Use appropriate list elements
<ul>  {/* Unordered list */}
  <li>Item 1</li>
  <li>Item 2</li>
</ul>

<ol>  {/* Ordered list */}
  <li>Step 1</li>
  <li>Step 2</li>
</ol>

<dl>  {/* Description list */}
  <dt>Term</dt>
  <dd>Definition</dd>
</dl>
```

## Tables

```tsx
// ✅ Good: Accessible table
<table>
  <caption>Monthly Sales Data</caption>
  <thead>
    <tr>
      <th scope="col">Month</th>
      <th scope="col">Sales</th>
      <th scope="col">Revenue</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th scope="row">January</th>
      <td>100</td>
      <td>$10,000</td>
    </tr>
  </tbody>
</table>
```

## Common Patterns

```tsx
// Card component
<article className="card">
  <header>
    <h3>Card Title</h3>
  </header>
  <p>Card content...</p>
  <footer>
    <a href="/read-more">Read more</a>
  </footer>
</article>

// Search form
<search>  {/* HTML5.2 element, or use role="search" */}
  <form role="search">
    <label htmlFor="search">Search</label>
    <input type="search" id="search" name="q" />
    <button type="submit">Search</button>
  </form>
</search>
```

Reference: [MDN Semantics](https://developer.mozilla.org/en-US/docs/Glossary/Semantics)


---

## Maintain Proper Heading Hierarchy

**Impact: CRITICAL (WCAG 2.1 Level A - Info and Relationships)**

Use heading elements (h1-h6) in a logical, hierarchical order to create a clear document outline. Proper heading structure is essential for screen reader navigation and SEO.

## Incorrect

```html
<!-- ❌ Bad: Broken heading hierarchy -->
<body>
  <!-- Skipping h1 -->
  <h2>Welcome to Our Store</h2>

  <div class="hero">
    <!-- Skipping levels (h2 to h4) -->
    <h4>Shop the Latest Collection</h4>
  </div>

  <section>
    <!-- Using h1 for styling purposes -->
    <h1 class="small-heading">Featured Products</h1>

    <div class="product">
      <!-- Heading levels chosen for styling, not structure -->
      <h5>Product Name</h5>
      <h6>$29.99</h6>
    </div>
  </section>

  <aside>
    <!-- Multiple h1s on the page -->
    <h1>Special Offers</h1>
  </aside>

  <!-- Using heading for non-heading content -->
  <h3>&copy; 2024 Our Company</h3>
</body>
```

**Problems:**
- Skipped heading levels break the document outline for screen reader navigation
- Multiple `<h1>` elements confuse the page hierarchy
- Choosing heading levels for visual styling instead of structure
- Non-heading content (copyright) marked up as a heading misleads assistive technology

## Correct

```html
<!-- ✅ Good: Logical heading hierarchy -->
<body>
  <header>
    <a href="/" aria-label="Home">
      <img src="logo.svg" alt="Acme Store">
    </a>
  </header>

  <!-- Single h1 for main page title -->
  <main>
    <h1>Welcome to Acme Store</h1>

    <section aria-labelledby="hero-heading">
      <h2 id="hero-heading">Shop the Latest Collection</h2>
      <p>Discover our new arrivals for the season.</p>
    </section>

    <section aria-labelledby="featured-heading">
      <h2 id="featured-heading">Featured Products</h2>

      <article class="product">
        <h3>Wireless Headphones</h3>
        <p class="price">$29.99</p>
        <p>High-quality audio with 20-hour battery life.</p>
      </article>

      <article class="product">
        <h3>Smart Watch</h3>
        <p class="price">$199.99</p>
        <p>Track your fitness and stay connected.</p>

        <!-- Sub-sections within product -->
        <section>
          <h4>Key Features</h4>
          <ul>
            <li>Heart rate monitor</li>
            <li>GPS tracking</li>
            <li>Water resistant</li>
          </ul>
        </section>

        <section>
          <h4>Customer Reviews</h4>
          <div class="review">
            <h5>Great product!</h5>
            <p>Exceeded my expectations...</p>
          </div>
        </section>
      </article>
    </section>

    <section aria-labelledby="categories-heading">
      <h2 id="categories-heading">Shop by Category</h2>

      <div class="category">
        <h3>Electronics</h3>
        <ul>
          <li><a href="/electronics/phones">Phones</a></li>
          <li><a href="/electronics/tablets">Tablets</a></li>
        </ul>
      </div>

      <div class="category">
        <h3>Clothing</h3>
        <ul>
          <li><a href="/clothing/mens">Men's</a></li>
          <li><a href="/clothing/womens">Women's</a></li>
        </ul>
      </div>
    </section>
  </main>

  <aside aria-labelledby="offers-heading">
    <h2 id="offers-heading">Special Offers</h2>
    <p>Get 20% off your first order!</p>
  </aside>

  <footer>
    <!-- Non-heading content styled differently -->
    <p><small>&copy; 2024 Acme Store. All rights reserved.</small></p>
  </footer>
</body>

<style>
/* Style headings independently of their level */
.product h3 {
  font-size: 1.25rem;
  font-weight: 600;
}

.product h4 {
  font-size: 1rem;
  font-weight: 500;
  color: #666;
}

/* Utility for visually styling any element as a heading */
.looks-like-h2 {
  font-size: 1.5rem;
  font-weight: bold;
  margin-bottom: 1rem;
}
</style>
```

**Benefits:**
- Screen reader users navigate by headings (H key) with a clear, logical outline
- Search engines understand page structure and content importance
- Users with cognitive disabilities can scan the document outline easily
- CSS controls visual appearance independently of semantic heading level

Reference: [WCAG 1.3.1 Info and Relationships](https://www.w3.org/WAI/WCAG21/Understanding/info-and-relationships.html)


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


---

## Provide Skip Links for Navigation

**Impact: HIGH (WCAG 2.1 Level A - Bypass blocks)**

Provide skip links to allow keyboard and screen reader users to bypass repetitive content and navigate directly to main content areas.

## Incorrect

```html
<!-- ❌ Bad: No skip links -->
<!DOCTYPE html>
<html>
<head>
  <title>My Website</title>
</head>
<body>
  <header>
    <nav>
      <a href="/">Home</a>
      <a href="/about">About</a>
      <a href="/services">Services</a>
      <a href="/products">Products</a>
      <a href="/portfolio">Portfolio</a>
      <a href="/blog">Blog</a>
      <a href="/contact">Contact</a>
      <!-- 20+ more links in mega menu -->
    </nav>
  </header>
  <main>
    <h1>Welcome</h1>
    <!-- User must tab through ALL nav links to reach this content -->
  </main>
</body>
</html>
```

**Problems:**
- Keyboard users must tab through every navigation link on every page to reach main content
- Screen reader users have no shortcut to bypass repetitive header content
- Users with motor disabilities waste effort on repeated key presses
- No mechanism to jump between major page sections

## Correct

```html
<!-- ✅ Good: Comprehensive skip links -->
<!DOCTYPE html>
<html>
<head>
  <title>My Website</title>
  <style>
    .skip-links {
      position: absolute;
      top: 0;
      left: 0;
      z-index: 9999;
    }

    .skip-link {
      position: absolute;
      left: -10000px;
      top: auto;
      width: 1px;
      height: 1px;
      overflow: hidden;
      background: #000;
      color: #fff;
      padding: 1rem;
      text-decoration: none;
      font-weight: bold;
    }

    .skip-link:focus {
      position: fixed;
      top: 0;
      left: 0;
      width: auto;
      height: auto;
      overflow: visible;
      outline: 3px solid #005fcc;
      outline-offset: 2px;
    }

    /* Smooth scroll for skip link targets */
    html {
      scroll-behavior: smooth;
    }

    /* Ensure skip link targets are focusable */
    [id]:target {
      scroll-margin-top: 1rem;
    }
  </style>
</head>
<body>
  <div class="skip-links">
    <a href="#main-content" class="skip-link">Skip to main content</a>
    <a href="#main-nav" class="skip-link">Skip to navigation</a>
    <a href="#search" class="skip-link">Skip to search</a>
    <a href="#footer" class="skip-link">Skip to footer</a>
  </div>

  <header>
    <form id="search" role="search" tabindex="-1">
      <label for="search-input" class="visually-hidden">Search</label>
      <input type="search" id="search-input" placeholder="Search...">
      <button type="submit">Search</button>
    </form>

    <nav id="main-nav" aria-label="Main navigation" tabindex="-1">
      <a href="/">Home</a>
      <a href="/about">About</a>
      <a href="/services">Services</a>
      <a href="/products">Products</a>
      <a href="/portfolio">Portfolio</a>
      <a href="/blog">Blog</a>
      <a href="/contact">Contact</a>
    </nav>
  </header>

  <main id="main-content" tabindex="-1">
    <h1>Welcome to Our Website</h1>
    <p>Main content starts here...</p>

    <!-- For long pages, provide in-page skip links -->
    <nav aria-label="Page sections">
      <h2>On this page</h2>
      <ul>
        <li><a href="#section-1">Introduction</a></li>
        <li><a href="#section-2">Features</a></li>
        <li><a href="#section-3">Pricing</a></li>
      </ul>
    </nav>

    <section id="section-1" tabindex="-1">
      <h2>Introduction</h2>
      <!-- Content -->
    </section>

    <section id="section-2" tabindex="-1">
      <h2>Features</h2>
      <!-- Content -->
    </section>

    <section id="section-3" tabindex="-1">
      <h2>Pricing</h2>
      <!-- Content -->
    </section>
  </main>

  <footer id="footer" tabindex="-1">
    <p>&copy; 2024 My Website</p>
  </footer>
</body>
</html>
```

**Benefits:**
- Keyboard users can bypass navigation and jump directly to main content
- Screen reader users get a consistent, fast way to reach key page sections
- Motor-impaired users reduce the number of key presses needed
- In-page navigation helps users of long pages find content quickly

Reference: [WCAG 2.4.1 Bypass Blocks](https://www.w3.org/WAI/WCAG21/Understanding/bypass-blocks.html)


---

## Ensure Full Keyboard Navigation

**Impact: CRITICAL (WCAG 2.1 Level A - Required for keyboard-only users)**

Ensure all interactive elements and functionality are fully accessible via keyboard. Users should be able to navigate, activate, and interact with all features without requiring a mouse.

## Incorrect

```html
<!-- ❌ Bad: Click-only interactions -->
<div class="card" onclick="showDetails()">
  <img src="product.jpg">
  <span class="title">Product Name</span>
</div>

<span class="link" onclick="navigate('/page')">Go to page</span>

<div class="dropdown">
  <div class="trigger" onclick="toggleMenu()">Menu</div>
  <div class="menu">
    <div class="item" onclick="selectItem(1)">Option 1</div>
    <div class="item" onclick="selectItem(2)">Option 2</div>
    <div class="item" onclick="selectItem(3)">Option 3</div>
  </div>
</div>

<div class="slider" onmousedown="startDrag()">
  <div class="handle"></div>
</div>
```

**Problems:**
- `<div>` and `<span>` elements are not focusable or activatable by keyboard
- Click-only handlers exclude all keyboard and switch-device users
- No ARIA roles or states to communicate component behavior to assistive technology
- Custom slider has no keyboard interaction pattern

## Correct

```html
<!-- ✅ Good: Full keyboard support -->
<article class="card"
         tabindex="0"
         role="button"
         onclick="showDetails()"
         onkeydown="handleCardKey(event)">
  <img src="product.jpg" alt="Product Name - Blue Widget">
  <h3 class="title">Product Name</h3>
</article>

<a href="/page" class="link">Go to page</a>

<div class="dropdown">
  <button class="trigger"
          aria-expanded="false"
          aria-haspopup="menu"
          onclick="toggleMenu()"
          onkeydown="handleTriggerKey(event)">
    Menu
  </button>
  <ul class="menu" role="menu" hidden>
    <li role="menuitem" tabindex="-1" onkeydown="handleMenuKey(event, 0)">Option 1</li>
    <li role="menuitem" tabindex="-1" onkeydown="handleMenuKey(event, 1)">Option 2</li>
    <li role="menuitem" tabindex="-1" onkeydown="handleMenuKey(event, 2)">Option 3</li>
  </ul>
</div>

<div class="slider"
     role="slider"
     tabindex="0"
     aria-valuemin="0"
     aria-valuemax="100"
     aria-valuenow="50"
     aria-label="Volume"
     onkeydown="handleSliderKey(event)">
  <div class="handle"></div>
</div>

<script>
function handleCardKey(event) {
  if (event.key === 'Enter' || event.key === ' ') {
    event.preventDefault();
    showDetails();
  }
}

function handleTriggerKey(event) {
  switch (event.key) {
    case 'ArrowDown':
    case 'Enter':
    case ' ':
      event.preventDefault();
      openMenu();
      focusFirstItem();
      break;
    case 'Escape':
      closeMenu();
      break;
  }
}

function handleMenuKey(event, index) {
  switch (event.key) {
    case 'ArrowDown':
      event.preventDefault();
      focusNextItem(index);
      break;
    case 'ArrowUp':
      event.preventDefault();
      focusPrevItem(index);
      break;
    case 'Enter':
    case ' ':
      event.preventDefault();
      selectItem(index);
      closeMenu();
      break;
    case 'Escape':
      closeMenu();
      focusTrigger();
      break;
  }
}

function handleSliderKey(event) {
  const slider = event.target;
  let value = parseInt(slider.getAttribute('aria-valuenow'));

  switch (event.key) {
    case 'ArrowRight':
    case 'ArrowUp':
      event.preventDefault();
      value = Math.min(100, value + 1);
      break;
    case 'ArrowLeft':
    case 'ArrowDown':
      event.preventDefault();
      value = Math.max(0, value - 1);
      break;
    case 'PageUp':
      event.preventDefault();
      value = Math.min(100, value + 10);
      break;
    case 'PageDown':
      event.preventDefault();
      value = Math.max(0, value - 10);
      break;
    case 'Home':
      event.preventDefault();
      value = 0;
      break;
    case 'End':
      event.preventDefault();
      value = 100;
      break;
  }

  slider.setAttribute('aria-valuenow', value);
  updateSliderPosition(value);
}
</script>
```

**Benefits:**
- All interactive elements are reachable and operable via keyboard
- Standard keyboard patterns (Enter, Space, Arrow keys, Escape) are implemented
- ARIA roles and states communicate component behavior to screen readers
- Native HTML elements (`<a>`, `<button>`) provide built-in keyboard support

Reference: [WCAG 2.1.1 Keyboard](https://www.w3.org/WAI/WCAG21/Understanding/keyboard.html)


---

## Manage Keyboard Focus Properly

**Impact: CRITICAL (WCAG 2.1 Level A - Focus order and visibility)**

Manage keyboard focus intentionally to create a logical, predictable navigation experience. Focus should follow the user's expectations and never get lost or trapped.

## Incorrect

```html
<!-- ❌ Bad: Poor focus management -->
<div class="modal" style="display: block;">
  <div class="modal-content">
    <h2>Delete Item?</h2>
    <p>This action cannot be undone.</p>
    <button onclick="closeModal()">Cancel</button>
    <button onclick="deleteItem()">Delete</button>
  </div>
</div>
<!-- Focus stays on the trigger button behind the modal -->

<script>
function openModal() {
  document.querySelector('.modal').style.display = 'block';
  // No focus management - user is lost
}

function closeModal() {
  document.querySelector('.modal').style.display = 'none';
  // Focus doesn't return to trigger
}
</script>
```

```css
/* ❌ Bad: Removing focus indicator entirely */
*:focus {
  outline: none;
}
```

**Problems:**
- Modal opens without moving focus, leaving keyboard users stranded behind the overlay
- Focus is not trapped inside the modal, so users can tab to hidden content
- Closing the modal does not return focus to the trigger element
- Removing all focus outlines makes keyboard navigation impossible

## Correct

```html
<!-- ✅ Good: Proper focus management -->
<button id="delete-trigger" onclick="openModal()">Delete Item</button>

<div class="modal"
     role="dialog"
     aria-modal="true"
     aria-labelledby="modal-title"
     hidden>
  <div class="modal-content">
    <h2 id="modal-title">Delete Item?</h2>
    <p>This action cannot be undone.</p>
    <button id="cancel-btn" onclick="closeModal()">Cancel</button>
    <button onclick="deleteItem()">Delete</button>
  </div>
</div>

<script>
let lastFocusedElement;

function openModal() {
  lastFocusedElement = document.activeElement;
  const modal = document.querySelector('.modal');
  modal.hidden = false;

  // Move focus to first focusable element
  document.getElementById('cancel-btn').focus();

  // Trap focus within modal
  modal.addEventListener('keydown', trapFocus);
}

function closeModal() {
  const modal = document.querySelector('.modal');
  modal.hidden = true;
  modal.removeEventListener('keydown', trapFocus);

  // Return focus to trigger element
  lastFocusedElement.focus();
}

function trapFocus(e) {
  if (e.key !== 'Tab') return;

  const focusableElements = e.currentTarget.querySelectorAll(
    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
  );
  const firstElement = focusableElements[0];
  const lastElement = focusableElements[focusableElements.length - 1];

  if (e.shiftKey && document.activeElement === firstElement) {
    lastElement.focus();
    e.preventDefault();
  } else if (!e.shiftKey && document.activeElement === lastElement) {
    firstElement.focus();
    e.preventDefault();
  }
}
</script>
```

```css
/* ✅ Good: Custom focus indicator */
*:focus {
  outline: 2px solid #005fcc;
  outline-offset: 2px;
}

/* Enhanced focus for better visibility */
*:focus-visible {
  outline: 3px solid #005fcc;
  outline-offset: 2px;
  box-shadow: 0 0 0 4px rgba(0, 95, 204, 0.3);
}
```

**Benefits:**
- Focus moves into the modal on open, so keyboard users interact with the correct content
- Focus trap prevents users from accidentally leaving the modal
- Focus returns to the trigger element on close, preserving navigation context
- Custom focus indicators ensure keyboard navigation is always visible

Reference: [WCAG 2.4.3 Focus Order](https://www.w3.org/WAI/WCAG21/Understanding/focus-order.html)


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


---

## Ensure Sufficient Color Contrast

**Impact: CRITICAL (WCAG 2.1 Level AA - 4.5:1 for text, 3:1 for large text)**

Ensure sufficient color contrast between text and backgrounds to make content readable for users with low vision or color blindness.

## Incorrect

```css
/* ❌ Bad: Insufficient contrast ratios */
.hero-text {
  color: #999999; /* Gray text */
  background-color: #ffffff; /* White background */
  /* Contrast ratio: 2.85:1 - FAILS */
}

.subtle-text {
  color: #b3b3b3;
  background-color: #f5f5f5;
  /* Contrast ratio: 1.63:1 - FAILS */
}

.button-primary {
  color: #ffffff;
  background-color: #66b3ff; /* Light blue */
  /* Contrast ratio: 2.48:1 - FAILS */
}

.link {
  color: #6699ff; /* Light blue link on white */
  /* Contrast ratio: 3.03:1 - FAILS for normal text */
}

.placeholder {
  color: #cccccc; /* Placeholder text */
  /* Too light to read */
}
```

```html
<!-- ❌ Bad: Relying only on color -->
<p>Required fields are marked in <span style="color: red;">red</span>.</p>
<input type="text" style="border-color: red;">
```

**Problems:**
- Low contrast text is unreadable for users with low vision
- Color-only indicators are invisible to color-blind users
- Light placeholders fail WCAG minimum contrast requirements
- Users in bright environments or on low-quality screens cannot read the text

## Correct

```css
/* ✅ Good: WCAG compliant contrast ratios */
.hero-text {
  color: #595959; /* Darker gray */
  background-color: #ffffff;
  /* Contrast ratio: 7:1 - PASSES AAA */
}

.body-text {
  color: #333333;
  background-color: #ffffff;
  /* Contrast ratio: 12.63:1 - PASSES AAA */
}

.button-primary {
  color: #ffffff;
  background-color: #0066cc; /* Darker blue */
  /* Contrast ratio: 7.05:1 - PASSES AAA */
}

.link {
  color: #0055aa; /* Darker blue link */
  text-decoration: underline;
  /* Contrast ratio: 7.28:1 - PASSES AAA */
}

.link:hover,
.link:focus {
  color: #003366;
  text-decoration: none;
  background-color: #e6f0ff;
}

/* Large text can have lower contrast (3:1 minimum) */
.large-heading {
  font-size: 24px;
  font-weight: bold;
  color: #666666;
  background-color: #ffffff;
  /* Contrast ratio: 5.74:1 - PASSES AA for large text */
}

/* Input placeholder with sufficient contrast */
.input::placeholder {
  color: #757575;
  /* Contrast ratio: 4.6:1 - PASSES AA */
}
```

```html
<!-- ✅ Good: Color plus additional indicators -->
<p>Required fields are marked with an asterisk (*).</p>
<label for="email">
  Email <span aria-hidden="true">*</span>
  <span class="visually-hidden">required</span>
</label>
<input type="email" id="email" required aria-required="true">

<!-- Error state with icon and text, not just color -->
<div class="error-message" role="alert">
  <svg aria-hidden="true" class="error-icon"><!-- X icon --></svg>
  <span>Please enter a valid email address</span>
</div>
```

**Benefits:**
- Text is readable by users with low vision and color deficiencies
- Content remains accessible in bright sunlight or on low-quality screens
- Non-color indicators ensure information reaches all users
- Meets WCAG AA and AAA contrast requirements

Reference: [WCAG 1.4.3 Contrast (Minimum)](https://www.w3.org/WAI/WCAG21/Understanding/contrast-minimum.html)


---

## Provide Meaningful Alt Text for Images

**Impact: CRITICAL (WCAG 2.1 Level A - Required for non-text content)**

Provide meaningful alternative text for images that conveys the purpose and content of the image to users who cannot see it.

## Incorrect

```html
<!-- ❌ Bad: Missing or poor alt text -->
<img src="hero.jpg">

<img src="logo.png" alt="logo">

<img src="team-photo.jpg" alt="image">

<img src="chart.png" alt="chart.png">

<img src="product.jpg" alt="Click here to buy now!">

<!-- Decorative image with alt text -->
<img src="decorative-border.png" alt="decorative border image">

<!-- Complex image with inadequate description -->
<img src="infographic.png" alt="infographic">

<!-- Icon with redundant alt -->
<a href="/search">
  <img src="search-icon.svg" alt="search icon">
  Search
</a>
```

**Problems:**
- Missing alt text makes images invisible to screen reader users
- Generic alt text ("logo", "image") provides no meaningful context
- File names as alt text are meaningless
- Decorative images with alt text create noise for screen readers
- Redundant alt text on icons alongside visible text causes double announcements

## Correct

```html
<!-- ✅ Good: Meaningful, contextual alt text -->

<!-- Informative image: describe content and purpose -->
<img src="hero.jpg" alt="Team of developers collaborating around a whiteboard filled with code diagrams">

<!-- Logo: include company name -->
<img src="logo.png" alt="Acme Corporation">

<!-- Team photo: describe relevant details -->
<img src="team-photo.jpg" alt="Our 12-person development team at the 2024 company retreat in Colorado">

<!-- Chart: describe the data and insights -->
<img src="chart.png" alt="Line chart showing website traffic growth from 10,000 monthly visitors in January to 45,000 in December 2024">

<!-- Product image: describe the product -->
<img src="product.jpg" alt="Blue wireless headphones with cushioned ear cups and adjustable headband">

<!-- Decorative image: use empty alt -->
<img src="decorative-border.png" alt="" role="presentation">

<!-- Complex image: use figure and detailed description -->
<figure>
  <img src="infographic.png" alt="2024 Industry trends infographic (detailed description below)">
  <figcaption>
    <details>
      <summary>Detailed description of infographic</summary>
      <p>This infographic shows five key industry trends for 2024:</p>
      <ol>
        <li>AI adoption increased 45% year over year</li>
        <li>Remote work stabilized at 35% of workforce</li>
        <li>Cybersecurity spending grew by 20%</li>
        <li>Cloud migration reached 80% completion</li>
        <li>Sustainability initiatives in 60% of companies</li>
      </ol>
    </details>
  </figcaption>
</figure>

<!-- Icon with text: hide redundant icon -->
<a href="/search">
  <img src="search-icon.svg" alt="" aria-hidden="true">
  Search
</a>

<!-- Functional image: describe the action -->
<button>
  <img src="print-icon.svg" alt="Print this page">
</button>

<!-- Image as link: describe destination -->
<a href="/products/headphones">
  <img src="headphones-thumb.jpg" alt="View Blue Wireless Headphones product details">
</a>

<!-- Background image with important content -->
<div class="hero" style="background-image: url('hero-bg.jpg');" role="img" aria-label="Aerial view of San Francisco skyline at sunset">
  <h1>Welcome to San Francisco</h1>
</div>
```

**Benefits:**
- Screen reader users understand the purpose and content of every image
- Images degrade gracefully when they fail to load
- Search engines can index image content for better SEO
- Users with cognitive disabilities gain additional context
- Legal compliance with WCAG non-text content requirements

Reference: [WCAG 1.1.1 Non-text Content](https://www.w3.org/WAI/WCAG21/Understanding/non-text-content.html)


---

## Provide Accessible Error Messages

**Impact: CRITICAL (WCAG 2.1 Level A - Error identification and suggestions)**

Error messages must be perceivable, understandable, and programmatically associated with their related form fields. Users should be able to identify and correct errors easily.

## Incorrect

```html
<!-- ❌ Bad: Poor error handling -->
<form>
  <!-- Error only shown visually with color -->
  <div class="form-group error">
    <label for="email">Email</label>
    <input type="email" id="email" style="border-color: red;">
  </div>

  <!-- Error message not associated with input -->
  <div class="form-group">
    <label for="password">Password</label>
    <input type="password" id="password">
  </div>
  <div class="error-text">Password must be 8 characters</div>

  <!-- Generic error message -->
  <div class="error-message">
    Please correct the errors above.
  </div>

  <!-- Error not announced to screen readers -->
  <div class="form-group">
    <label for="phone">Phone</label>
    <input type="tel" id="phone">
    <span class="error">Invalid phone number</span>
  </div>
</form>

<script>
// Anti-pattern: Alert boxes for errors
function validateForm() {
  if (!isValid) {
    alert('Please fix the errors in the form');
  }
}
</script>
```

**Problems:**
- Color-only error indicators are invisible to color-blind users
- Error messages not linked to inputs via `aria-describedby`
- Generic messages do not help users identify or fix specific errors
- Screen readers cannot discover errors that lack ARIA roles or live regions
- JavaScript `alert()` interrupts the user and provides no field context

## Correct

```html
<!-- ✅ Good: Accessible error handling -->
<form novalidate aria-describedby="form-error-summary">
  <!-- Error summary at top of form -->
  <div id="form-error-summary"
       class="error-summary"
       role="alert"
       tabindex="-1"
       hidden>
    <h2>There were 2 errors with your submission</h2>
    <ul>
      <li><a href="#email">Email address is required</a></li>
      <li><a href="#password">Password must be at least 8 characters</a></li>
    </ul>
  </div>

  <!-- Field with error - complete implementation -->
  <div class="form-group">
    <label for="email">
      Email Address
      <span aria-hidden="true">*</span>
    </label>
    <input type="email"
           id="email"
           name="email"
           required
           aria-required="true"
           aria-invalid="true"
           aria-describedby="email-error email-hint">
    <p id="email-hint" class="hint-text">
      We'll never share your email with anyone
    </p>
    <p id="email-error" class="error-message" role="alert">
      <svg aria-hidden="true" class="error-icon"><!-- error icon --></svg>
      <span>Please enter a valid email address (e.g., name@example.com)</span>
    </p>
  </div>

  <!-- Field with error - visual and programmatic indicators -->
  <div class="form-group has-error">
    <label for="password">
      Password
      <span aria-hidden="true">*</span>
    </label>
    <input type="password"
           id="password"
           name="password"
           required
           aria-required="true"
           aria-invalid="true"
           aria-describedby="password-error password-requirements">
    <p id="password-requirements" class="hint-text">
      Must contain at least 8 characters, one uppercase, one number
    </p>
    <p id="password-error" class="error-message" role="alert">
      <svg aria-hidden="true" class="error-icon"><!-- error icon --></svg>
      <span>Password is too short. Please use at least 8 characters.</span>
    </p>
  </div>

  <!-- Success state after correction -->
  <div class="form-group has-success">
    <label for="username">Username</label>
    <input type="text"
           id="username"
           name="username"
           aria-invalid="false"
           aria-describedby="username-success">
    <p id="username-success" class="success-message">
      <svg aria-hidden="true" class="success-icon"><!-- checkmark --></svg>
      <span>Username is available</span>
    </p>
  </div>
</form>

<script>
function showError(input, message) {
  const formGroup = input.closest('.form-group');
  const errorElement = formGroup.querySelector('.error-message');

  // Set ARIA attributes
  input.setAttribute('aria-invalid', 'true');

  // Show error message
  errorElement.textContent = message;
  errorElement.hidden = false;

  // Move focus to input (for form submission errors)
  input.focus();
}

function clearError(input) {
  const formGroup = input.closest('.form-group');
  const errorElement = formGroup.querySelector('.error-message');

  input.setAttribute('aria-invalid', 'false');
  errorElement.hidden = true;
}

function showErrorSummary(errors) {
  const summary = document.getElementById('form-error-summary');
  const list = summary.querySelector('ul');

  // Build error list
  list.innerHTML = errors.map(error =>
    `<li><a href="#${error.fieldId}">${error.message}</a></li>`
  ).join('');

  // Update heading
  summary.querySelector('h2').textContent =
    `There ${errors.length === 1 ? 'was 1 error' : `were ${errors.length} errors`} with your submission`;

  // Show and focus summary
  summary.hidden = false;
  summary.focus();
}
</script>

<style>
.error-summary {
  background-color: #fef2f2;
  border: 2px solid #dc2626;
  border-radius: 4px;
  padding: 1rem;
  margin-bottom: 1.5rem;
}

.error-summary:focus {
  outline: 3px solid #005fcc;
  outline-offset: 2px;
}

.error-message {
  color: #dc2626;
  font-size: 0.875rem;
  margin-top: 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.has-error input {
  border-color: #dc2626;
  border-width: 2px;
}

.has-error input:focus {
  outline-color: #dc2626;
  box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.2);
}

.success-message {
  color: #059669;
  font-size: 0.875rem;
  margin-top: 0.5rem;
}
</style>
```

**Benefits:**
- Errors are announced to screen readers via `role="alert"` live regions
- Each error is programmatically linked to its field with `aria-describedby`
- Error summary with links lets users jump directly to problem fields
- Visual indicators (icons, borders, text) work alongside color for all users
- Specific, actionable messages guide users to correct their input

Reference: [WCAG 3.3.1 Error Identification](https://www.w3.org/WAI/WCAG21/Understanding/error-identification.html)


---

## Associate Labels with Form Inputs

**Impact: CRITICAL (WCAG 2.1 Level A - Info and Relationships)**

Every form input must have an associated label that clearly identifies its purpose. Labels are essential for screen reader users and improve usability for all users.

## Incorrect

```html
<!-- ❌ Bad: Missing or improper labels -->
<form>
  <!-- No label at all -->
  <input type="text" placeholder="Enter your name">

  <!-- Placeholder as label (disappears on input) -->
  <input type="email" placeholder="Email address">

  <!-- Label not associated with input -->
  <div>
    <span>Password</span>
    <input type="password">
  </div>

  <!-- Mismatched for/id -->
  <label for="username">Username</label>
  <input type="text" id="user-name">

  <!-- Non-label element used -->
  <p>Phone Number</p>
  <input type="tel">

  <!-- Label too far from input -->
  <label for="address">Address</label>
  <p>Enter your full mailing address including zip code</p>
  <div class="spacer"></div>
  <input type="text" id="address">
</form>
```

**Problems:**
- Screen readers announce nothing when inputs lack associated labels
- Placeholders vanish on focus, leaving users with no context
- Mismatched `for`/`id` breaks the programmatic association
- Non-`<label>` elements do not provide click-to-focus behavior
- Voice control users cannot target inputs without proper labels

## Correct

```html
<!-- ✅ Good: Properly associated labels -->
<form>
  <!-- Explicit label association with for/id -->
  <div class="form-group">
    <label for="full-name">Full Name</label>
    <input type="text" id="full-name" name="fullName" autocomplete="name">
  </div>

  <!-- Implicit label (input inside label) -->
  <label class="form-group">
    Email Address
    <input type="email" name="email" autocomplete="email">
  </label>

  <!-- Label with required indicator -->
  <div class="form-group">
    <label for="password">
      Password
      <span aria-hidden="true">*</span>
      <span class="visually-hidden">(required)</span>
    </label>
    <input type="password" id="password" name="password"
           required aria-required="true"
           aria-describedby="password-requirements">
    <p id="password-requirements" class="helper-text">
      Must be at least 8 characters with one number
    </p>
  </div>

  <!-- Visually hidden label (when design requires no visible label) -->
  <div class="form-group search-box">
    <label for="search" class="visually-hidden">Search products</label>
    <input type="search" id="search" name="search"
           placeholder="Search..." autocomplete="off">
    <button type="submit" aria-label="Submit search">
      <svg aria-hidden="true"><!-- search icon --></svg>
    </button>
  </div>

  <!-- Fieldset for grouped inputs -->
  <fieldset>
    <legend>Shipping Address</legend>

    <div class="form-group">
      <label for="street">Street Address</label>
      <input type="text" id="street" name="street" autocomplete="street-address">
    </div>

    <div class="form-group">
      <label for="city">City</label>
      <input type="text" id="city" name="city" autocomplete="address-level2">
    </div>
  </fieldset>

  <!-- Radio buttons with fieldset/legend -->
  <fieldset>
    <legend>Preferred Contact Method</legend>

    <div class="radio-group">
      <input type="radio" id="contact-email" name="contact" value="email">
      <label for="contact-email">Email</label>
    </div>

    <div class="radio-group">
      <input type="radio" id="contact-phone" name="contact" value="phone">
      <label for="contact-phone">Phone</label>
    </div>

    <div class="radio-group">
      <input type="radio" id="contact-sms" name="contact" value="sms">
      <label for="contact-sms">Text Message</label>
    </div>
  </fieldset>

  <!-- Checkbox with label -->
  <div class="checkbox-group">
    <input type="checkbox" id="newsletter" name="newsletter">
    <label for="newsletter">Subscribe to our newsletter</label>
  </div>

  <!-- Multiple related inputs with single label -->
  <fieldset>
    <legend>Date of Birth</legend>
    <div class="date-inputs">
      <div>
        <label for="dob-month">Month</label>
        <select id="dob-month" name="dobMonth">
          <option value="">--</option>
          <option value="1">January</option>
          <!-- ... -->
        </select>
      </div>
      <div>
        <label for="dob-day">Day</label>
        <input type="text" id="dob-day" name="dobDay" maxlength="2">
      </div>
      <div>
        <label for="dob-year">Year</label>
        <input type="text" id="dob-year" name="dobYear" maxlength="4">
      </div>
    </div>
  </fieldset>
</form>

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
- Screen readers announce the label when an input receives focus
- Clicking a label focuses its associated input, increasing the click target
- Voice control users can identify and interact with inputs by label text
- Grouped inputs with `<fieldset>`/`<legend>` provide clear context for related fields
- Meets WCAG requirements for programmatically associated labels

Reference: [WCAG 1.3.1 Info and Relationships](https://www.w3.org/WAI/WCAG21/Understanding/info-and-relationships.html)


---

## Use ARIA Live Regions for Dynamic Content

**Impact: HIGH (WCAG 2.1 Level AA - Status messages)**

Use ARIA live regions to announce dynamic content changes to screen reader users. Live regions ensure that updates happening outside the user's current focus are communicated appropriately.

## Incorrect

```html
<!-- ❌ Bad: Dynamic updates not announced -->

<!-- Cart count updates silently -->
<div class="cart-count">3</div>

<!-- Loading state not communicated -->
<div class="loading-spinner" style="display: none;">
  <div class="spinner"></div>
</div>

<!-- Toast notification appears but isn't announced -->
<div class="toast success">
  Your changes have been saved!
</div>

<!-- Search results update without announcement -->
<div class="search-results">
  <p>Showing 25 results for "widgets"</p>
  <!-- results -->
</div>

<!-- Form submission feedback not announced -->
<script>
function submitForm() {
  // ... submit logic
  document.querySelector('.success-message').style.display = 'block';
}
</script>
<div class="success-message" style="display: none;">
  Form submitted successfully!
</div>
```

**Problems:**
- Screen reader users have no way of knowing content changed elsewhere on the page
- Cart count updates, toast notifications, and search results are invisible to non-sighted users
- Loading and submission states are not communicated
- Dynamic content appears visually but is never announced

## Correct

```html
<!-- ✅ Good: Dynamic updates properly announced -->

<!-- Cart count with live region -->
<div class="cart">
  <span class="visually-hidden" aria-live="polite" aria-atomic="true">
    Shopping cart: <span id="cart-count-announcement">3 items</span>
  </span>
  <button aria-label="Shopping cart, 3 items">
    <svg aria-hidden="true"><!-- cart icon --></svg>
    <span class="cart-count" aria-hidden="true">3</span>
  </button>
</div>

<!-- Loading state with status updates -->
<div class="content-area">
  <div id="loading-status"
       role="status"
       aria-live="polite"
       class="visually-hidden">
  </div>
  <div class="loading-spinner" hidden>
    <div class="spinner" aria-hidden="true"></div>
    <span class="visually-hidden">Loading content, please wait</span>
  </div>
  <div id="content">
    <!-- Dynamic content here -->
  </div>
</div>

<!-- Toast notification with proper live region -->
<div id="notification-area"
     role="alert"
     aria-live="assertive"
     aria-atomic="true">
</div>

<!-- Search results with polite announcement -->
<div class="search-container">
  <div id="search-status"
       role="status"
       aria-live="polite"
       aria-atomic="true"
       class="visually-hidden">
  </div>
  <div class="search-results" aria-labelledby="results-heading">
    <h2 id="results-heading">Search Results</h2>
    <p id="results-summary">Showing 25 results for "widgets"</p>
    <!-- results -->
  </div>
</div>

<!-- Form with proper feedback -->
<form id="contact-form">
  <div id="form-status"
       role="status"
       aria-live="polite"
       aria-atomic="true">
  </div>
  <!-- form fields -->
  <button type="submit">Submit</button>
</form>

<script>
// Cart update with announcement
function updateCart(count) {
  document.getElementById('cart-count-announcement').textContent =
    `${count} item${count !== 1 ? 's' : ''}`;
}

// Loading with status updates
function loadContent() {
  const status = document.getElementById('loading-status');
  const spinner = document.querySelector('.loading-spinner');

  status.textContent = 'Loading content, please wait...';
  spinner.hidden = false;

  fetch('/api/content')
    .then(response => response.json())
    .then(data => {
      spinner.hidden = true;
      document.getElementById('content').innerHTML = data.html;
      status.textContent = 'Content loaded successfully';
    })
    .catch(error => {
      spinner.hidden = true;
      status.textContent = 'Failed to load content. Please try again.';
    });
}

// Toast notification
function showToast(message, type = 'info') {
  const area = document.getElementById('notification-area');
  area.innerHTML = `
    <div class="toast ${type}">
      <svg aria-hidden="true" class="${type}-icon"><!-- icon --></svg>
      ${message}
    </div>
  `;

  // Auto-dismiss after delay
  setTimeout(() => {
    area.innerHTML = '';
  }, 5000);
}

// Search results announcement
function updateSearchResults(query, count) {
  const status = document.getElementById('search-status');
  status.textContent = `Showing ${count} results for "${query}"`;
}

// Form submission with feedback
document.getElementById('contact-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const status = document.getElementById('form-status');

  status.textContent = 'Submitting form...';

  try {
    await submitForm();
    status.textContent = 'Form submitted successfully! We will contact you within 24 hours.';
  } catch (error) {
    status.textContent = 'Form submission failed. Please try again or contact support.';
  }
});
</script>
```

**Benefits:**
- Screen reader users are notified of dynamic content changes without losing their place
- Polite announcements wait for idle moments; assertive announcements interrupt for critical updates
- Loading states, search results, and form feedback reach all users
- Pre-existing live regions in the DOM ensure reliable announcement across screen readers

Reference: [WCAG 4.1.3 Status Messages](https://www.w3.org/WAI/WCAG21/Understanding/status-messages.html)


---

## Use Autocomplete Attributes for Forms

**Impact: CRITICAL (WCAG 2.1 Level AA - Identify input purpose)**

The `autocomplete` attribute enables browsers and password managers to autofill forms correctly. Proper autocomplete improves user experience, reduces errors, and is required for WCAG 1.3.5 compliance.

## Incorrect

```tsx
// ❌ Bad: No autocomplete attributes
<form>
  <input type="text" name="name" />
  <input type="text" name="email" />
  <input type="password" name="password" />
</form>

// ❌ Bad: Autocomplete disabled (frustrating for users)
<input type="email" autoComplete="off" />
```

**Problems:**
- Browsers and password managers cannot autofill fields without autocomplete hints
- Users must manually type every field, increasing friction and errors
- Disabling autocomplete on login/address fields is user-hostile
- Fails WCAG 1.3.5 Identify Input Purpose compliance

## Correct

```tsx
// ✅ Good: Proper autocomplete attributes
<form>
  {/* Name fields */}
  <input type="text" name="name" autoComplete="name" />
  <input type="text" name="firstName" autoComplete="given-name" />
  <input type="text" name="lastName" autoComplete="family-name" />

  {/* Contact fields */}
  <input type="email" name="email" autoComplete="email" />
  <input type="tel" name="phone" autoComplete="tel" />

  {/* Address fields */}
  <input type="text" name="address" autoComplete="street-address" />
  <input type="text" name="city" autoComplete="address-level2" />
  <input type="text" name="state" autoComplete="address-level1" />
  <input type="text" name="zip" autoComplete="postal-code" />
  <input type="text" name="country" autoComplete="country-name" />

  {/* Payment fields */}
  <input type="text" name="ccName" autoComplete="cc-name" />
  <input type="text" name="ccNumber" autoComplete="cc-number" />
  <input type="text" name="ccExp" autoComplete="cc-exp" />
  <input type="text" name="ccCsc" autoComplete="cc-csc" />
</form>
```

**Benefits:**
- Faster form completion via browser and password manager autofill
- Fewer input errors from manual typing
- Better mobile experience with native keyboard hints
- Password manager integration for secure credential handling
- WCAG 1.3.5 compliance

## Login Form

```tsx
// ✅ Good: Login form with proper autocomplete
<form>
  <div>
    <label htmlFor="username">Username or Email</label>
    <input
      id="username"
      type="email"
      name="email"
      autoComplete="username"  // Tells password managers this is the identifier
    />
  </div>

  <div>
    <label htmlFor="password">Password</label>
    <input
      id="password"
      type="password"
      name="password"
      autoComplete="current-password"  // For existing password
    />
  </div>

  <button type="submit">Sign In</button>
</form>
```

## Registration Form

```tsx
// ✅ Good: Registration form
<form>
  <div>
    <label htmlFor="email">Email</label>
    <input
      id="email"
      type="email"
      name="email"
      autoComplete="email"
    />
  </div>

  <div>
    <label htmlFor="newPassword">Password</label>
    <input
      id="newPassword"
      type="password"
      name="password"
      autoComplete="new-password"  // For new password creation
    />
  </div>

  <div>
    <label htmlFor="confirmPassword">Confirm Password</label>
    <input
      id="confirmPassword"
      type="password"
      name="confirmPassword"
      autoComplete="new-password"
    />
  </div>

  <button type="submit">Create Account</button>
</form>
```

## One-Time Codes (OTP)

```tsx
// ✅ Good: OTP input
<div>
  <label htmlFor="otp">Verification Code</label>
  <input
    id="otp"
    type="text"
    inputMode="numeric"
    pattern="[0-9]*"
    autoComplete="one-time-code"  // Triggers SMS autofill on mobile
    maxLength={6}
  />
</div>
```

## Common Autocomplete Values

| Value | Use For |
|-------|---------|
| `name` | Full name |
| `given-name` | First name |
| `family-name` | Last name |
| `email` | Email address |
| `tel` | Full phone number |
| `username` | Username (login identifier) |
| `new-password` | New password (registration) |
| `current-password` | Existing password (login) |
| `one-time-code` | OTP/verification code |
| `street-address` | Full street address |
| `address-level1` | State/Province |
| `address-level2` | City |
| `postal-code` | ZIP/Postal code |
| `country-name` | Country name |
| `cc-name` | Cardholder name |
| `cc-number` | Credit card number |
| `cc-exp` | Card expiration (MM/YY) |
| `cc-csc` | Card security code |
| `bday` | Birthday |
| `organization` | Company name |

## Shipping vs Billing

```tsx
// ✅ Good: Distinguish shipping and billing addresses
<fieldset>
  <legend>Shipping Address</legend>
  <input
    type="text"
    name="shippingAddress"
    autoComplete="shipping street-address"
  />
  <input
    type="text"
    name="shippingCity"
    autoComplete="shipping address-level2"
  />
</fieldset>

<fieldset>
  <legend>Billing Address</legend>
  <input
    type="text"
    name="billingAddress"
    autoComplete="billing street-address"
  />
  <input
    type="text"
    name="billingCity"
    autoComplete="billing address-level2"
  />
</fieldset>
```

## When to Disable Autocomplete

```tsx
// ✅ Good: Legitimate cases to disable autocomplete
<input
  type="text"
  name="search"
  autoComplete="off"  // Search boxes
/>

<input
  type="text"
  name="captcha"
  autoComplete="off"  // CAPTCHA responses
/>

// ❌ Bad: Don't disable for these (user hostile)
<input type="email" autoComplete="off" />    // Email
<input type="password" autoComplete="off" /> // Password
<input type="text" name="address" autoComplete="off" /> // Address
```

Reference: [WCAG 1.3.5 Identify Input Purpose](https://www.w3.org/WAI/WCAG21/Understanding/identify-input-purpose.html)


---

## Use Correct Input Types

**Impact: HIGH (Improves mobile UX and reduces errors)**

Use the correct HTML input types to provide appropriate virtual keyboards, validation, and user experience across devices.

## Incorrect

```html
<!-- ❌ Bad: Using generic text inputs for everything -->
<form>
  <label for="email">Email</label>
  <input type="text" id="email" name="email">

  <label for="phone">Phone</label>
  <input type="text" id="phone" name="phone">

  <label for="age">Age</label>
  <input type="text" id="age" name="age">

  <label for="website">Website</label>
  <input type="text" id="website" name="website">

  <label for="dob">Date of Birth</label>
  <input type="text" id="dob" name="dob" placeholder="MM/DD/YYYY">

  <label for="password">Password</label>
  <input type="text" id="password" name="password">

  <label for="color-choice">Favorite Color</label>
  <input type="text" id="color-choice" name="color">
</form>
```

**Problems:**
- Generic `type="text"` shows a standard keyboard on mobile instead of optimized layouts
- No built-in browser validation for email, URL, or number formats
- No native date, time, or color pickers
- Password fields are not masked, exposing sensitive input
- Screen readers cannot announce the expected input type

## Correct

```html
<!-- ✅ Good: Semantic input types -->
<form>
  <!-- Email: brings up @ keyboard on mobile -->
  <label for="email">Email</label>
  <input type="email"
         id="email"
         name="email"
         autocomplete="email"
         inputmode="email"
         placeholder="name@example.com">

  <!-- Phone: numeric keypad on mobile -->
  <label for="phone">Phone</label>
  <input type="tel"
         id="phone"
         name="phone"
         autocomplete="tel"
         inputmode="tel"
         placeholder="+1 (555) 123-4567">

  <!-- Number: numeric input with spinners -->
  <label for="age">Age</label>
  <input type="number"
         id="age"
         name="age"
         min="0"
         max="150"
         inputmode="numeric">

  <!-- URL: optimized keyboard for URLs -->
  <label for="website">Website</label>
  <input type="url"
         id="website"
         name="website"
         autocomplete="url"
         inputmode="url"
         placeholder="https://example.com">

  <!-- Date: native date picker -->
  <label for="dob">Date of Birth</label>
  <input type="date"
         id="dob"
         name="dob"
         min="1900-01-01"
         max="2024-12-31"
         autocomplete="bday">

  <!-- Password: masked input with browser features -->
  <label for="password">Password</label>
  <input type="password"
         id="password"
         name="password"
         autocomplete="new-password"
         minlength="8">

  <!-- Color picker -->
  <label for="color-choice">Favorite Color</label>
  <input type="color"
         id="color-choice"
         name="color"
         value="#3b82f6">

  <!-- Search: may show clear button and search styling -->
  <label for="search">Search</label>
  <input type="search"
         id="search"
         name="search"
         autocomplete="off"
         inputmode="search">

  <!-- Range slider -->
  <label for="volume">Volume: <output id="volume-output">50</output>%</label>
  <input type="range"
         id="volume"
         name="volume"
         min="0"
         max="100"
         value="50"
         oninput="document.getElementById('volume-output').textContent = this.value">

  <!-- Time input -->
  <label for="meeting-time">Meeting Time</label>
  <input type="time"
         id="meeting-time"
         name="meeting-time"
         min="09:00"
         max="18:00">

  <!-- File upload -->
  <label for="document">Upload Document</label>
  <input type="file"
         id="document"
         name="document"
         accept=".pdf,.doc,.docx"
         multiple>

  <!-- Hidden field for form data -->
  <input type="hidden" name="csrf_token" value="abc123">
</form>
```

**Benefits:**
- Mobile users get optimized virtual keyboards (email with @, numeric for phone)
- Browsers provide built-in validation for email, URL, and number formats
- Native date, time, and color pickers reduce implementation effort
- Password fields mask input and trigger password manager integration
- Screen readers announce the expected input type and constraints

Reference: [MDN Input Types](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input#input_types)


---

## Display Form Errors Clearly

**Impact: HIGH (WCAG 2.1 Level A - Error identification)**

Display form errors clearly and consistently to help users identify and correct mistakes. Error messages should be visible, associated with their fields, and provide actionable guidance.

## Incorrect

```html
<!-- ❌ Bad: Poor error display -->
<form>
  <!-- Error message far from input -->
  <div class="errors">
    <p>Email is invalid</p>
    <p>Password is required</p>
  </div>

  <input type="text" name="email" value="bad-email">
  <input type="password" name="password">

  <!-- Generic JavaScript alert -->
  <script>
    function validate() {
      alert('There are errors in your form');
    }
  </script>

  <!-- Error shown only by color change -->
  <input type="text" style="border-color: red;" placeholder="Enter name">

  <!-- Tooltip-only errors (disappear quickly) -->
  <input type="email" title="Please enter a valid email">

  <!-- Clearing the field on error -->
  <input type="text" id="phone" oninvalid="this.value=''">
</form>
```

**Problems:**
- Error messages far from their fields make it hard to identify which input has the problem
- JavaScript `alert()` provides no context about specific fields
- Color-only error indicators are invisible to color-blind users
- Tooltip-only errors are not persistent and may not be perceived
- Clearing valid input on error forces users to retype everything

## Correct

```html
<!-- ✅ Good: Clear, accessible error display -->
<form id="contact-form" novalidate>
  <!-- Error summary at top of form -->
  <div id="error-summary"
       role="alert"
       class="error-summary"
       hidden>
    <h2>Please correct the following errors:</h2>
    <ul id="error-list">
      <!-- Populated by JavaScript -->
    </ul>
  </div>

  <div class="form-group" id="email-group">
    <label for="email">
      Email Address
      <span class="required" aria-hidden="true">*</span>
    </label>
    <input type="email"
           id="email"
           name="email"
           required
           aria-required="true"
           aria-describedby="email-hint email-error"
           aria-invalid="false">
    <p id="email-hint" class="hint">Enter your work email address</p>
    <p id="email-error" class="field-error" hidden>
      <span class="error-icon" aria-hidden="true">
        <svg width="16" height="16" viewBox="0 0 16 16">
          <circle cx="8" cy="8" r="7" fill="none" stroke="currentColor" stroke-width="2"/>
          <path d="M8 4v5M8 11v1" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </span>
      <span class="error-text"></span>
    </p>
  </div>

  <div class="form-group" id="phone-group">
    <label for="phone">Phone Number</label>
    <input type="tel"
           id="phone"
           name="phone"
           aria-describedby="phone-error"
           aria-invalid="false">
    <p id="phone-error" class="field-error" hidden>
      <span class="error-icon" aria-hidden="true">
        <svg width="16" height="16" viewBox="0 0 16 16">
          <circle cx="8" cy="8" r="7" fill="none" stroke="currentColor" stroke-width="2"/>
          <path d="M8 4v5M8 11v1" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </span>
      <span class="error-text"></span>
    </p>
  </div>

  <div class="form-group" id="message-group">
    <label for="message">
      Message
      <span class="required" aria-hidden="true">*</span>
    </label>
    <textarea id="message"
              name="message"
              required
              aria-required="true"
              aria-describedby="message-counter message-error"
              maxlength="500"
              rows="4"></textarea>
    <div class="field-footer">
      <p id="message-counter" class="character-counter">0/500 characters</p>
      <p id="message-error" class="field-error" hidden>
        <span class="error-icon" aria-hidden="true">
          <svg width="16" height="16" viewBox="0 0 16 16">
            <circle cx="8" cy="8" r="7" fill="none" stroke="currentColor" stroke-width="2"/>
            <path d="M8 4v5M8 11v1" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </span>
        <span class="error-text"></span>
      </p>
    </div>
  </div>

  <button type="submit">Send Message</button>
</form>

<script>
const form = document.getElementById('contact-form');
const errorSummary = document.getElementById('error-summary');
const errorList = document.getElementById('error-list');

form.addEventListener('submit', function(e) {
  e.preventDefault();
  clearAllErrors();

  const errors = validateForm();

  if (errors.length > 0) {
    displayErrors(errors);
  } else {
    // submit form logic here
  }
});

function validateForm() {
  const errors = [];

  // Email validation
  const email = document.getElementById('email');
  if (!email.value) {
    errors.push({ field: 'email', message: 'Email address is required' });
  } else if (!isValidEmail(email.value)) {
    errors.push({ field: 'email', message: 'Enter a valid email address, like name@company.com' });
  }

  // Phone validation (optional but must be valid if provided)
  const phone = document.getElementById('phone');
  if (phone.value && !isValidPhone(phone.value)) {
    errors.push({ field: 'phone', message: 'Enter a valid phone number, like (555) 123-4567' });
  }

  // Message validation
  const message = document.getElementById('message');
  if (!message.value.trim()) {
    errors.push({ field: 'message', message: 'Message is required' });
  } else if (message.value.length < 10) {
    errors.push({ field: 'message', message: 'Message must be at least 10 characters' });
  }

  return errors;
}

function displayErrors(errors) {
  // Build error summary
  errorList.innerHTML = errors.map(error =>
    `<li><a href="#${error.field}">${error.message}</a></li>`
  ).join('');

  errorSummary.hidden = false;

  // Display inline errors
  errors.forEach(error => {
    const input = document.getElementById(error.field);
    const errorEl = document.getElementById(`${error.field}-error`);
    const errorText = errorEl.querySelector('.error-text');

    input.setAttribute('aria-invalid', 'true');
    errorText.textContent = error.message;
    errorEl.hidden = false;

    // Add error class to form group
    document.getElementById(`${error.field}-group`).classList.add('has-error');
  });

  // Focus error summary (or first error field)
  errorSummary.scrollIntoView({ behavior: 'smooth', block: 'start' });
  errorList.querySelector('a').focus();
}

function clearAllErrors() {
  errorSummary.hidden = true;
  errorList.innerHTML = '';

  document.querySelectorAll('.field-error').forEach(el => {
    el.hidden = true;
  });

  document.querySelectorAll('[aria-invalid="true"]').forEach(el => {
    el.setAttribute('aria-invalid', 'false');
  });

  document.querySelectorAll('.has-error').forEach(el => {
    el.classList.remove('has-error');
  });
}
</script>

<style>
.error-summary {
  background: #fef2f2;
  border: 2px solid #dc2626;
  border-radius: 8px;
  padding: 1rem;
  margin-bottom: 1.5rem;
}

.error-summary h2 {
  color: #991b1b;
  font-size: 1rem;
  margin: 0 0 0.5rem;
}

.error-summary ul {
  margin: 0;
  padding-left: 1.5rem;
}

.error-summary a {
  color: #dc2626;
}

.field-error {
  color: #dc2626;
  font-size: 0.875rem;
  margin-top: 0.5rem;
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
}

.has-error input,
.has-error textarea {
  border-color: #dc2626;
  border-width: 2px;
}

.has-error input:focus,
.has-error textarea:focus {
  outline-color: #dc2626;
  box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.15);
}
</style>
```

**Benefits:**
- Error summary at the top provides an overview with links to each problem field
- Inline errors next to each field make identification immediate
- Icons and text alongside color ensure errors are perceivable by all users
- ARIA attributes link errors programmatically for screen readers
- User input is preserved on error so nothing needs to be retyped

Reference: [WCAG 3.3.1 Error Identification](https://www.w3.org/WAI/WCAG21/Understanding/error-identification.html)


---

## Design User-Friendly Form Validation

**Impact: HIGH (Reduces form abandonment by 20-30%)**

Design form validation that helps users succeed. Good validation provides clear feedback, prevents frustration, and guides users to correct errors efficiently.

## Incorrect

```html
<!-- ❌ Bad: Poor validation UX -->
<form onsubmit="return validateForm()">
  <input type="text" id="email" placeholder="Email">
  <input type="text" id="password" placeholder="Password">
  <button type="submit">Sign Up</button>
</form>

<script>
function validateForm() {
  // Only validates on submit
  const email = document.getElementById('email').value;
  const password = document.getElementById('password').value;

  // Generic, unhelpful error message
  if (!email || !password) {
    alert('Please fill in all fields');
    return false;
  }

  // Regex-only validation with no feedback
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    alert('Invalid email');
    return false;
  }

  // Unclear requirements
  if (password.length < 8) {
    alert('Password too short');
    return false;
  }

  return true;
}
</script>

<style>
/* Error state with no guidance */
.error { border-color: red; }
</style>
```

**Problems:**
- Submit-only validation delays all feedback until the end
- JavaScript `alert()` provides no field-level context and interrupts the user
- Generic messages ("Invalid email") do not explain how to fix the error
- Password requirements are hidden until the user fails
- No ARIA attributes to communicate errors to screen readers

## Correct

```html
<!-- ✅ Good: User-friendly validation -->
<form id="signup-form" novalidate>
  <div class="form-group">
    <label for="email">
      Email Address
      <span class="required" aria-hidden="true">*</span>
    </label>
    <input type="email"
           id="email"
           name="email"
           required
           aria-required="true"
           aria-describedby="email-hint email-error"
           autocomplete="email">
    <p id="email-hint" class="hint">We'll send your confirmation here</p>
    <p id="email-error" class="error-message" role="alert" hidden></p>
  </div>

  <div class="form-group">
    <label for="password">
      Password
      <span class="required" aria-hidden="true">*</span>
    </label>
    <input type="password"
           id="password"
           name="password"
           required
           aria-required="true"
           aria-describedby="password-requirements password-error"
           minlength="8"
           autocomplete="new-password">

    <!-- Show requirements upfront, not just on error -->
    <div id="password-requirements" class="requirements">
      <p>Password must contain:</p>
      <ul>
        <li id="req-length" class="requirement">
          <span class="icon" aria-hidden="true"></span>
          At least 8 characters
        </li>
        <li id="req-uppercase" class="requirement">
          <span class="icon" aria-hidden="true"></span>
          One uppercase letter
        </li>
        <li id="req-number" class="requirement">
          <span class="icon" aria-hidden="true"></span>
          One number
        </li>
      </ul>
    </div>
    <p id="password-error" class="error-message" role="alert" hidden></p>
  </div>

  <div class="form-group">
    <label for="confirm-password">
      Confirm Password
      <span class="required" aria-hidden="true">*</span>
    </label>
    <input type="password"
           id="confirm-password"
           name="confirmPassword"
           required
           aria-required="true"
           aria-describedby="confirm-error"
           autocomplete="new-password">
    <p id="confirm-error" class="error-message" role="alert" hidden></p>
  </div>

  <button type="submit" id="submit-btn">Create Account</button>
</form>

<script>
const form = document.getElementById('signup-form');
const emailInput = document.getElementById('email');
const passwordInput = document.getElementById('password');
const confirmInput = document.getElementById('confirm-password');

// Validate on blur (when leaving field)
emailInput.addEventListener('blur', validateEmail);
passwordInput.addEventListener('input', validatePassword);
confirmInput.addEventListener('blur', validateConfirmPassword);

// Also validate on submit
form.addEventListener('submit', function(e) {
  e.preventDefault();

  const isEmailValid = validateEmail();
  const isPasswordValid = validatePassword();
  const isConfirmValid = validateConfirmPassword();

  if (isEmailValid && isPasswordValid && isConfirmValid) {
    // submit form logic here
  } else {
    // Focus first invalid field
    const firstError = form.querySelector('[aria-invalid="true"]');
    if (firstError) firstError.focus();
  }
});

function validateEmail() {
  const email = emailInput.value.trim();
  const errorEl = document.getElementById('email-error');

  if (!email) {
    showError(emailInput, errorEl, 'Email address is required');
    return false;
  }

  if (!isValidEmail(email)) {
    showError(emailInput, errorEl, 'Please enter a valid email address (e.g., name@example.com)');
    return false;
  }

  clearError(emailInput, errorEl);
  return true;
}

function validatePassword() {
  const password = passwordInput.value;
  const requirements = {
    length: password.length >= 8,
    uppercase: /[A-Z]/.test(password),
    number: /[0-9]/.test(password)
  };

  // Update requirement indicators in real-time
  updateRequirement('req-length', requirements.length);
  updateRequirement('req-uppercase', requirements.uppercase);
  updateRequirement('req-number', requirements.number);

  const allMet = Object.values(requirements).every(Boolean);

  if (allMet) {
    passwordInput.setAttribute('aria-invalid', 'false');
    return true;
  }

  passwordInput.setAttribute('aria-invalid', 'true');
  return false;
}

function validateConfirmPassword() {
  const password = passwordInput.value;
  const confirm = confirmInput.value;
  const errorEl = document.getElementById('confirm-error');

  if (!confirm) {
    showError(confirmInput, errorEl, 'Please confirm your password');
    return false;
  }

  if (confirm !== password) {
    showError(confirmInput, errorEl, 'Passwords do not match');
    return false;
  }

  clearError(confirmInput, errorEl);
  return true;
}

function updateRequirement(id, met) {
  const el = document.getElementById(id);
  el.classList.toggle('met', met);
  el.classList.toggle('unmet', !met);
}

function showError(input, errorEl, message) {
  input.setAttribute('aria-invalid', 'true');
  errorEl.textContent = message;
  errorEl.hidden = false;
}

function clearError(input, errorEl) {
  input.setAttribute('aria-invalid', 'false');
  errorEl.hidden = true;
}

function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}
</script>

<style>
.requirement.met .icon::before { content: '✓'; color: green; }
.requirement.unmet .icon::before { content: '○'; color: #666; }
.error-message { color: #dc2626; }
</style>
```

**Benefits:**
- Blur validation provides feedback at the right moment without interrupting typing
- Password requirements are shown upfront so users know what is expected
- Real-time requirement indicators give positive feedback as criteria are met
- Specific, actionable error messages guide users to correct input
- Focus moves to the first invalid field on submit for efficient error correction

Reference: [web.dev - Best Practices for Form Validation](https://web.dev/learn/forms/validation)


---

## Implement Smart Inline Validation

**Impact: MEDIUM (Reduces errors by 20-40%)**

Implement inline validation that provides immediate, contextual feedback as users complete form fields. Validate at the right moment to help without interrupting.

## Incorrect

```html
<!-- ❌ Bad: Aggressive inline validation -->
<form>
  <input type="email"
         id="email"
         oninput="validateEmail()"
         placeholder="Email">
  <span id="email-error"></span>
</form>

<script>
// Validates on every keystroke (too aggressive)
function validateEmail() {
  const email = document.getElementById('email').value;
  const error = document.getElementById('email-error');

  // Shows error immediately when user starts typing
  if (!email.includes('@')) {
    error.textContent = 'Invalid email!';
    error.style.color = 'red';
  } else {
    error.textContent = '';
  }
}
</script>

<!-- ❌ Bad: No inline validation (only on submit) -->
<form onsubmit="return validate()">
  <input type="text" name="username">
  <input type="password" name="password">
  <!-- User only finds out about errors after clicking submit -->
  <button type="submit">Sign Up</button>
</form>
```

**Problems:**
- Validating on every keystroke shows errors before the user has finished typing
- Submit-only validation delays feedback until the very end, causing frustration
- No ARIA attributes to communicate validation state to screen readers
- Error messages lack specificity and actionable guidance

## Correct

```html
<!-- ✅ Good: Balanced inline validation -->
<form id="signup-form" novalidate>
  <!-- Email with validation on blur and after correction -->
  <div class="form-group" data-validate="email">
    <label for="email">Email Address *</label>
    <input type="email"
           id="email"
           name="email"
           required
           aria-required="true"
           aria-describedby="email-hint email-feedback"
           autocomplete="email">
    <p id="email-hint" class="hint">We'll send your confirmation here</p>
    <div id="email-feedback"
         class="feedback"
         role="status"
         aria-live="polite"></div>
  </div>

  <!-- Username with async validation -->
  <div class="form-group" data-validate="username">
    <label for="username">Username *</label>
    <input type="text"
           id="username"
           name="username"
           required
           aria-required="true"
           aria-describedby="username-hint username-feedback"
           pattern="^[a-zA-Z0-9_]{3,20}$"
           autocomplete="username">
    <p id="username-hint" class="hint">3-20 characters, letters, numbers, underscores</p>
    <div id="username-feedback"
         class="feedback"
         role="status"
         aria-live="polite"></div>
  </div>

  <!-- Password with real-time strength indicator -->
  <div class="form-group" data-validate="password">
    <label for="password">Password *</label>
    <input type="password"
           id="password"
           name="password"
           required
           aria-required="true"
           aria-describedby="password-requirements password-feedback"
           minlength="8"
           autocomplete="new-password">

    <div id="password-requirements" class="requirements">
      <span class="requirements-label">Requirements:</span>
      <ul class="requirements-list">
        <li data-requirement="length">
          <span class="req-icon" aria-hidden="true"></span>
          <span class="req-text">At least 8 characters</span>
        </li>
        <li data-requirement="uppercase">
          <span class="req-icon" aria-hidden="true"></span>
          <span class="req-text">One uppercase letter</span>
        </li>
        <li data-requirement="lowercase">
          <span class="req-icon" aria-hidden="true"></span>
          <span class="req-text">One lowercase letter</span>
        </li>
        <li data-requirement="number">
          <span class="req-icon" aria-hidden="true"></span>
          <span class="req-text">One number</span>
        </li>
      </ul>
    </div>

    <div class="password-strength">
      <div class="strength-bar">
        <div class="strength-fill" id="strength-fill"></div>
      </div>
      <span id="strength-text" class="strength-text"></span>
    </div>

    <div id="password-feedback"
         class="feedback"
         role="status"
         aria-live="polite"></div>
  </div>

  <!-- Confirm password with match validation -->
  <div class="form-group" data-validate="confirmPassword">
    <label for="confirm-password">Confirm Password *</label>
    <input type="password"
           id="confirm-password"
           name="confirmPassword"
           required
           aria-required="true"
           aria-describedby="confirm-feedback"
           autocomplete="new-password">
    <div id="confirm-feedback"
         class="feedback"
         role="status"
         aria-live="polite"></div>
  </div>

  <button type="submit">Create Account</button>
</form>

<script>
// Validation timing configuration
const validationConfig = {
  email: { on: 'blur', revalidate: 'input' },
  username: { on: 'blur', revalidate: 'input', debounce: 500 },
  password: { on: 'input', immediate: true },
  confirmPassword: { on: 'blur', revalidate: 'input' }
};

// Track if field has been touched
const touchedFields = new Set();

// Debounce utility
function debounce(fn, delay) {
  let timeoutId;
  return function(...args) {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => fn.apply(this, args), delay);
  };
}

// Email validation
const emailInput = document.getElementById('email');
emailInput.addEventListener('blur', () => {
  touchedFields.add('email');
  validateEmail();
});
emailInput.addEventListener('input', () => {
  if (touchedFields.has('email')) {
    validateEmail();
  }
});

function validateEmail() {
  const email = emailInput.value.trim();
  const feedback = document.getElementById('email-feedback');

  if (!email) {
    showError(emailInput, feedback, 'Email address is required');
    return false;
  }

  if (!isValidEmail(email)) {
    showError(emailInput, feedback, 'Please enter a valid email (e.g., name@example.com)');
    return false;
  }

  showSuccess(emailInput, feedback, 'Email looks good');
  return true;
}

// Username validation with async availability check
const usernameInput = document.getElementById('username');
const checkUsername = debounce(validateUsername, 500);

usernameInput.addEventListener('blur', () => {
  touchedFields.add('username');
  validateUsername();
});
usernameInput.addEventListener('input', () => {
  if (touchedFields.has('username')) {
    checkUsername();
  }
});

async function validateUsername() {
  const username = usernameInput.value.trim();
  const feedback = document.getElementById('username-feedback');

  if (!username) {
    showError(usernameInput, feedback, 'Username is required');
    return false;
  }

  if (username.length < 3) {
    showError(usernameInput, feedback, 'Username must be at least 3 characters');
    return false;
  }

  if (!/^[a-zA-Z0-9_]+$/.test(username)) {
    showError(usernameInput, feedback, 'Only letters, numbers, and underscores allowed');
    return false;
  }

  // Check availability
  showPending(usernameInput, feedback, 'Checking availability...');

  try {
    const response = await fetch(`/api/check-username?username=${username}`);
    const { available } = await response.json();

    if (available) {
      showSuccess(usernameInput, feedback, 'Username is available');
      return true;
    } else {
      showError(usernameInput, feedback, 'Username is already taken');
      return false;
    }
  } catch (error) {
    showError(usernameInput, feedback, 'Could not check availability');
    return false;
  }
}

// Password validation with real-time feedback
const passwordInput = document.getElementById('password');
passwordInput.addEventListener('input', validatePassword);

function validatePassword() {
  const password = passwordInput.value;
  const requirements = {
    length: password.length >= 8,
    uppercase: /[A-Z]/.test(password),
    lowercase: /[a-z]/.test(password),
    number: /[0-9]/.test(password)
  };

  // Update requirement indicators
  Object.entries(requirements).forEach(([req, met]) => {
    const el = document.querySelector(`[data-requirement="${req}"]`);
    el.classList.toggle('met', met);
    el.classList.toggle('unmet', !met);
  });

  // Calculate and show strength
  const strength = Object.values(requirements).filter(Boolean).length;
  updateStrengthIndicator(strength);

  // Only show feedback after user has typed something
  if (password.length > 0) {
    touchedFields.add('password');
  }

  return Object.values(requirements).every(Boolean);
}

function updateStrengthIndicator(strength) {
  const fill = document.getElementById('strength-fill');
  const text = document.getElementById('strength-text');

  const levels = [
    { label: '', color: '', width: '0%' },
    { label: 'Weak', color: '#dc2626', width: '25%' },
    { label: 'Fair', color: '#f59e0b', width: '50%' },
    { label: 'Good', color: '#3b82f6', width: '75%' },
    { label: 'Strong', color: '#22c55e', width: '100%' }
  ];

  const level = levels[strength];
  fill.style.width = level.width;
  fill.style.backgroundColor = level.color;
  text.textContent = level.label;
  text.style.color = level.color;
}

// Confirm password
const confirmInput = document.getElementById('confirm-password');
confirmInput.addEventListener('blur', () => {
  touchedFields.add('confirmPassword');
  validateConfirm();
});
confirmInput.addEventListener('input', () => {
  if (touchedFields.has('confirmPassword')) {
    validateConfirm();
  }
});

function validateConfirm() {
  const confirm = confirmInput.value;
  const password = passwordInput.value;
  const feedback = document.getElementById('confirm-feedback');

  if (!confirm) {
    showError(confirmInput, feedback, 'Please confirm your password');
    return false;
  }

  if (confirm !== password) {
    showError(confirmInput, feedback, 'Passwords do not match');
    return false;
  }

  showSuccess(confirmInput, feedback, 'Passwords match');
  return true;
}

// Feedback display helpers
function showError(input, feedback, message) {
  input.setAttribute('aria-invalid', 'true');
  feedback.className = 'feedback error';
  feedback.innerHTML = `<span class="icon">&#10005;</span> ${message}`;
}

function showSuccess(input, feedback, message) {
  input.setAttribute('aria-invalid', 'false');
  feedback.className = 'feedback success';
  feedback.innerHTML = `<span class="icon">&#10003;</span> ${message}`;
}

function showPending(input, feedback, message) {
  input.removeAttribute('aria-invalid');
  feedback.className = 'feedback pending';
  feedback.innerHTML = `<span class="spinner"></span> ${message}`;
}
</script>

<style>
.feedback.error { color: #dc2626; }
.feedback.success { color: #22c55e; }
.feedback.pending { color: #6b7280; }
.requirements-list li.met { color: #22c55e; }
.requirements-list li.unmet { color: #6b7280; }
</style>
```

**Benefits:**
- Blur-first validation avoids interrupting users while they type
- Revalidation on input clears errors as soon as the user corrects them
- Password strength indicator provides real-time, positive feedback
- Async username check with debounce avoids overwhelming the server
- ARIA live regions announce validation results to screen readers

Reference: [web.dev - Best Practices for Form Validation](https://web.dev/learn/forms/validation)


---

## Design Effective Multi-Step Forms

**Impact: MEDIUM (Increases completion rates by 15-30%)**

Design multi-step forms that are easy to navigate, maintain context, and don't overwhelm users. Break complex forms into logical, manageable steps.

## Incorrect

```html
<!-- ❌ Bad: Poor multi-step form -->
<form>
  <!-- No progress indication -->
  <!-- No step numbers or titles -->

  <div class="step" id="step1">
    <input type="text" placeholder="Name">
    <input type="email" placeholder="Email">
    <button type="button" onclick="nextStep()">Next</button>
  </div>

  <div class="step" id="step2" hidden>
    <input type="text" placeholder="Address">
    <!-- No back button -->
    <button type="button" onclick="nextStep()">Next</button>
  </div>

  <div class="step" id="step3" hidden>
    <!-- No summary of previous steps -->
    <button type="submit">Submit</button>
  </div>
</form>

<script>
let step = 1;
function nextStep() {
  // Doesn't validate before proceeding
  document.getElementById(`step${step}`).hidden = true;
  step++;
  document.getElementById(`step${step}`).hidden = false;
  // Doesn't save progress
  // Doesn't announce step change to screen readers
}
</script>
```

**Problems:**
- No progress indicator, so users do not know how many steps remain
- No back navigation to return to previous steps
- No validation before advancing, allowing incomplete data
- No screen reader announcements for step changes
- No data persistence if the user navigates away
- No review step before final submission

## Correct

```html
<!-- ✅ Good: Accessible multi-step form -->
<div class="multi-step-form">
  <!-- Progress indicator -->
  <nav aria-label="Form progress">
    <ol class="progress-steps" role="list">
      <li class="step-indicator current"
          aria-current="step">
        <span class="step-number">1</span>
        <span class="step-label">Personal Info</span>
      </li>
      <li class="step-indicator">
        <span class="step-number">2</span>
        <span class="step-label">Address</span>
      </li>
      <li class="step-indicator">
        <span class="step-number">3</span>
        <span class="step-label">Review</span>
      </li>
    </ol>
    <div class="progress-bar">
      <div class="progress-fill" style="width: 33%;"
           role="progressbar"
           aria-valuenow="1"
           aria-valuemin="1"
           aria-valuemax="3"
           aria-label="Step 1 of 3"></div>
    </div>
  </nav>

  <!-- Status announcement for screen readers -->
  <div id="step-status"
       role="status"
       aria-live="polite"
       class="visually-hidden">
  </div>

  <form id="registration-form" novalidate>
    <!-- Step 1: Personal Information -->
    <fieldset id="step-1" class="form-step">
      <legend tabindex="-1">
        <span class="step-title">Step 1 of 3: Personal Information</span>
      </legend>

      <div class="form-group">
        <label for="full-name">Full Name *</label>
        <input type="text"
               id="full-name"
               name="fullName"
               required
               aria-required="true"
               autocomplete="name">
        <p id="name-error" class="error" hidden></p>
      </div>

      <div class="form-group">
        <label for="email">Email Address *</label>
        <input type="email"
               id="email"
               name="email"
               required
               aria-required="true"
               autocomplete="email">
        <p id="email-error" class="error" hidden></p>
      </div>

      <div class="form-group">
        <label for="phone">Phone Number</label>
        <input type="tel"
               id="phone"
               name="phone"
               autocomplete="tel">
      </div>

      <div class="form-actions">
        <button type="button"
                class="btn-next"
                onclick="goToStep(2)">
          Continue to Address
          <svg aria-hidden="true" class="arrow-icon"><!-- arrow right --></svg>
        </button>
      </div>
    </fieldset>

    <!-- Step 2: Address -->
    <fieldset id="step-2" class="form-step" hidden>
      <legend tabindex="-1">
        <span class="step-title">Step 2 of 3: Address</span>
      </legend>

      <div class="form-group">
        <label for="street">Street Address *</label>
        <input type="text"
               id="street"
               name="street"
               required
               aria-required="true"
               autocomplete="street-address">
        <p id="street-error" class="error" hidden></p>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="city">City *</label>
          <input type="text"
                 id="city"
                 name="city"
                 required
                 aria-required="true"
                 autocomplete="address-level2">
        </div>

        <div class="form-group">
          <label for="state">State *</label>
          <select id="state"
                  name="state"
                  required
                  aria-required="true"
                  autocomplete="address-level1">
            <option value="">Select...</option>
            <option value="CA">California</option>
            <option value="NY">New York</option>
            <!-- more states -->
          </select>
        </div>

        <div class="form-group">
          <label for="zip">ZIP Code *</label>
          <input type="text"
                 id="zip"
                 name="zip"
                 required
                 aria-required="true"
                 autocomplete="postal-code"
                 inputmode="numeric"
                 pattern="[0-9]{5}">
        </div>
      </div>

      <div class="form-actions">
        <button type="button"
                class="btn-back"
                onclick="goToStep(1)">
          <svg aria-hidden="true" class="arrow-icon"><!-- arrow left --></svg>
          Back
        </button>
        <button type="button"
                class="btn-next"
                onclick="goToStep(3)">
          Review Your Information
          <svg aria-hidden="true" class="arrow-icon"><!-- arrow right --></svg>
        </button>
      </div>
    </fieldset>

    <!-- Step 3: Review -->
    <fieldset id="step-3" class="form-step" hidden>
      <legend tabindex="-1">
        <span class="step-title">Step 3 of 3: Review & Submit</span>
      </legend>

      <div class="review-section">
        <h3>Personal Information</h3>
        <dl class="review-list">
          <dt>Name</dt>
          <dd id="review-name"></dd>
          <dt>Email</dt>
          <dd id="review-email"></dd>
          <dt>Phone</dt>
          <dd id="review-phone"></dd>
        </dl>
        <button type="button"
                class="btn-edit"
                onclick="goToStep(1)">
          Edit Personal Info
        </button>
      </div>

      <div class="review-section">
        <h3>Address</h3>
        <dl class="review-list">
          <dt>Street</dt>
          <dd id="review-street"></dd>
          <dt>City, State ZIP</dt>
          <dd id="review-city-state-zip"></dd>
        </dl>
        <button type="button"
                class="btn-edit"
                onclick="goToStep(2)">
          Edit Address
        </button>
      </div>

      <div class="form-actions">
        <button type="button"
                class="btn-back"
                onclick="goToStep(2)">
          <svg aria-hidden="true" class="arrow-icon"><!-- arrow left --></svg>
          Back
        </button>
        <button type="submit" class="btn-submit">
          Submit Registration
        </button>
      </div>
    </fieldset>
  </form>
</div>

<script>
let currentStep = 1;
const totalSteps = 3;
const form = document.getElementById('registration-form');
const statusEl = document.getElementById('step-status');

function goToStep(newStep) {
  // Validate current step before proceeding forward
  if (newStep > currentStep && !validateStep(currentStep)) {
    return;
  }

  // Save current step data
  saveProgress();

  // Hide current step
  document.getElementById(`step-${currentStep}`).hidden = true;

  // Update progress indicators
  updateProgressIndicators(newStep);

  // Show new step
  currentStep = newStep;
  const newStepEl = document.getElementById(`step-${newStep}`);
  newStepEl.hidden = false;

  // If review step, populate summary
  if (newStep === 3) {
    populateReview();
  }

  // Focus management
  newStepEl.querySelector('legend').focus();

  // Announce step change
  const stepTitle = newStepEl.querySelector('.step-title').textContent;
  statusEl.textContent = `Now on ${stepTitle}`;

  // Scroll to top of form
  newStepEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function validateStep(step) {
  const stepEl = document.getElementById(`step-${step}`);
  const requiredFields = stepEl.querySelectorAll('[required]');
  let isValid = true;
  let firstError = null;

  requiredFields.forEach(field => {
    if (!field.validity.valid) {
      isValid = false;
      showFieldError(field);
      if (!firstError) firstError = field;
    } else {
      clearFieldError(field);
    }
  });

  if (firstError) {
    firstError.focus();
    statusEl.textContent = 'Please correct the errors before continuing.';
  }

  return isValid;
}

function updateProgressIndicators(newStep) {
  const indicators = document.querySelectorAll('.step-indicator');
  indicators.forEach((indicator, index) => {
    const stepNum = index + 1;
    indicator.classList.toggle('completed', stepNum < newStep);
    indicator.classList.toggle('current', stepNum === newStep);
    if (stepNum === newStep) {
      indicator.setAttribute('aria-current', 'step');
    } else {
      indicator.removeAttribute('aria-current');
    }
  });

  // Update progress bar
  const progressFill = document.querySelector('.progress-fill');
  const percentage = (newStep / totalSteps) * 100;
  progressFill.style.width = `${percentage}%`;
  progressFill.setAttribute('aria-valuenow', newStep);
}

function saveProgress() {
  const formData = new FormData(form);
  const data = Object.fromEntries(formData.entries());
  localStorage.setItem('registration-progress', JSON.stringify({
    step: currentStep,
    data: data
  }));
}

function populateReview() {
  document.getElementById('review-name').textContent =
    document.getElementById('full-name').value || 'Not provided';
  document.getElementById('review-email').textContent =
    document.getElementById('email').value;
  document.getElementById('review-phone').textContent =
    document.getElementById('phone').value || 'Not provided';
  document.getElementById('review-street').textContent =
    document.getElementById('street').value;
  document.getElementById('review-city-state-zip').textContent =
    `${document.getElementById('city').value}, ${document.getElementById('state').value} ${document.getElementById('zip').value}`;
}

// Restore progress on page load
window.addEventListener('load', () => {
  const saved = localStorage.getItem('registration-progress');
  if (saved) {
    const { step, data } = JSON.parse(saved);
    // Populate form fields
    Object.entries(data).forEach(([name, value]) => {
      const field = form.elements[name];
      if (field) field.value = value;
    });
    // Go to saved step
    if (step > 1) goToStep(step);
  }
});
</script>
```

**Benefits:**
- Progress indicator shows users where they are and how much is left
- Back navigation and edit buttons let users revisit and correct previous steps
- Per-step validation catches errors early before advancing
- Screen reader announcements communicate step changes
- Data persistence via localStorage preserves progress across page reloads
- Review step lets users verify all information before final submission

Reference: [web.dev - Multi-step Forms](https://web.dev/learn/forms/auto)


---

## Use Placeholders Appropriately

**Impact: MEDIUM (Prevents accessibility and usability issues)**

Use placeholders appropriately as supplementary hints, never as replacements for labels. Placeholders have significant accessibility and usability limitations.

## Incorrect

```html
<!-- ❌ Bad: Placeholders as labels -->
<form>
  <!-- No visible labels - only placeholders -->
  <input type="text" placeholder="First Name">
  <input type="text" placeholder="Last Name">
  <input type="email" placeholder="Email Address">
  <input type="password" placeholder="Password (min 8 characters)">

  <!-- Required indicator in placeholder -->
  <input type="text" placeholder="Phone Number *">

  <!-- Critical information in placeholder -->
  <input type="text" placeholder="Enter date as MM/DD/YYYY">

  <!-- Placeholder that looks like entered text -->
  <input type="text"
         placeholder="John Smith"
         style="color: #333;">

  <!-- Long placeholder that gets truncated -->
  <input type="text"
         placeholder="Enter your full legal name as it appears on your government ID">
</form>

<style>
/* Anti-pattern: Hiding labels */
label { display: none; }
</style>
```

**Problems:**
- Placeholders vanish when users start typing, removing all context
- Users must remember what each field requires while entering data
- Default placeholder contrast often fails WCAG minimum requirements
- Some screen readers do not announce placeholder text
- Browser autofill may not identify fields without proper labels
- Users with cognitive disabilities struggle when instructions disappear

## Correct

```html
<!-- ✅ Good: Labels with optional placeholder hints -->
<form>
  <div class="form-group">
    <label for="first-name">First Name</label>
    <input type="text"
           id="first-name"
           name="firstName"
           autocomplete="given-name">
    <!-- No placeholder needed - label is clear -->
  </div>

  <div class="form-group">
    <label for="email">
      Email Address
      <span class="required" aria-hidden="true">*</span>
    </label>
    <input type="email"
           id="email"
           name="email"
           required
           aria-required="true"
           placeholder="name@example.com"
           autocomplete="email">
    <!-- Placeholder shows format example -->
  </div>

  <div class="form-group">
    <label for="phone">Phone Number</label>
    <input type="tel"
           id="phone"
           name="phone"
           placeholder="(555) 123-4567"
           aria-describedby="phone-hint"
           autocomplete="tel">
    <p id="phone-hint" class="hint">Include area code</p>
    <!-- Hint text for persistent instructions -->
  </div>

  <div class="form-group">
    <label for="date">Event Date</label>
    <input type="text"
           id="date"
           name="date"
           aria-describedby="date-format"
           placeholder="MM/DD/YYYY">
    <p id="date-format" class="hint">Format: MM/DD/YYYY</p>
    <!-- Important format info in persistent hint -->
  </div>

  <div class="form-group">
    <label for="password">
      Password
      <span class="required" aria-hidden="true">*</span>
    </label>
    <input type="password"
           id="password"
           name="password"
           required
           aria-required="true"
           aria-describedby="password-requirements"
           minlength="8"
           autocomplete="new-password">
    <p id="password-requirements" class="hint">
      Must be at least 8 characters with one uppercase letter and one number
    </p>
    <!-- Requirements in persistent text, not placeholder -->
  </div>

  <div class="form-group">
    <label for="search">Search Products</label>
    <input type="search"
           id="search"
           name="search"
           placeholder="e.g., blue widget, SKU-1234"
           autocomplete="off">
    <!-- Placeholder shows example queries -->
  </div>

  <div class="form-group">
    <label for="bio">Bio</label>
    <textarea id="bio"
              name="bio"
              placeholder="Tell us about yourself..."
              aria-describedby="bio-hint"
              rows="4"></textarea>
    <p id="bio-hint" class="hint">Optional. Max 500 characters.</p>
  </div>
</form>

<style>
/* Placeholder styling - clearly different from entered text */
::placeholder {
  color: #9ca3af;
  opacity: 1; /* Firefox fix */
  font-style: italic;
}

/* Hint text is always visible */
.hint {
  color: #6b7280;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

/* Never hide labels */
label {
  display: block;
  font-weight: 500;
  margin-bottom: 0.25rem;
}
</style>
```

**Benefits:**
- Visible labels persist regardless of input state, providing constant context
- Persistent hint text via `aria-describedby` keeps format instructions available
- Placeholders serve as supplementary examples, not primary labels
- Screen readers announce both the label and the described-by hint text
- Browser autofill correctly identifies fields by their labels

Reference: [MDN Placeholder Attribute](https://developer.mozilla.org/en-US/docs/Web/HTML/Attributes/placeholder)


---

## Provide Clear Form Submission Feedback

**Impact: HIGH (Prevents user confusion and double submissions)**

Provide clear, immediate feedback when users submit forms. Users should always know the current state: submitting, success, or failure.

## Incorrect

```html
<!-- ❌ Bad: No submission feedback -->
<form onsubmit="submitForm()">
  <input type="email" name="email" required>
  <button type="submit">Subscribe</button>
</form>

<script>
async function submitForm() {
  // No loading indication
  const response = await fetch('/api/subscribe', {
    method: 'POST',
    body: new FormData(form)
  });

  // Silent success - user doesn't know it worked
  if (response.ok) {
    console.log('Success');
  }

  // Unhelpful error
  if (!response.ok) {
    alert('Error');
  }
}
</script>
```

```html
<!-- ❌ Bad: Confusing states -->
<form>
  <input type="email" name="email">
  <!-- Button changes but no visual feedback -->
  <button type="submit" id="btn">Subscribe</button>
</form>

<script>
btn.textContent = 'Sending...';
// User can click multiple times
// No disabled state
// Page reloads on error, losing context
</script>
```

**Problems:**
- No loading indicator leaves users wondering if the form submitted
- Silent success provides no confirmation that the action worked
- Generic `alert('Error')` gives no actionable guidance
- Button is not disabled during submission, allowing double-clicks
- Page reload on error loses all entered data

## Correct

```html
<!-- ✅ Good: Comprehensive submission feedback -->
<form id="newsletter-form" novalidate>
  <div class="form-group">
    <label for="email">Email Address</label>
    <input type="email"
           id="email"
           name="email"
           required
           aria-required="true"
           aria-describedby="email-error"
           autocomplete="email">
    <p id="email-error" class="error" hidden></p>
  </div>

  <button type="submit"
          id="submit-btn"
          aria-describedby="submit-status">
    <span class="btn-text">Subscribe</span>
    <span class="btn-loading" hidden>
      <svg class="spinner" aria-hidden="true" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="3"/>
      </svg>
      <span>Subscribing...</span>
    </span>
  </button>

  <!-- Status message for screen readers -->
  <div id="submit-status"
       role="status"
       aria-live="polite"
       class="visually-hidden">
  </div>
</form>

<!-- Success state (shown after successful submission) -->
<div id="success-message" class="success-panel" tabindex="-1" hidden>
  <svg class="success-icon" aria-hidden="true" viewBox="0 0 24 24">
    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" fill="currentColor"/>
  </svg>
  <h2>You're subscribed!</h2>
  <p>Check your inbox for a confirmation email.</p>
  <button type="button" onclick="resetForm()">Subscribe another email</button>
</div>

<script>
const form = document.getElementById('newsletter-form');
const submitBtn = document.getElementById('submit-btn');
const btnText = submitBtn.querySelector('.btn-text');
const btnLoading = submitBtn.querySelector('.btn-loading');
const statusEl = document.getElementById('submit-status');
const successMessage = document.getElementById('success-message');

form.addEventListener('submit', async (e) => {
  e.preventDefault();

  // Validate
  const email = document.getElementById('email');
  if (!email.validity.valid) {
    showError(email, 'Please enter a valid email address');
    return;
  }

  // Set loading state
  setLoadingState(true);

  try {
    const response = await fetch('/api/subscribe', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: email.value })
    });

    const data = await response.json();

    if (response.ok) {
      showSuccess();
    } else {
      // Server returned an error
      showError(email, data.message || 'Subscription failed. Please try again.');
      setLoadingState(false);
    }
  } catch (error) {
    // Network or other error
    showError(email, 'Network error. Please check your connection and try again.');
    setLoadingState(false);
  }
});

function setLoadingState(isLoading) {
  submitBtn.disabled = isLoading;
  submitBtn.setAttribute('aria-busy', isLoading);
  btnText.hidden = isLoading;
  btnLoading.hidden = !isLoading;

  // Announce to screen readers
  if (isLoading) {
    statusEl.textContent = 'Submitting your subscription...';
  }
}

function showSuccess() {
  form.hidden = true;
  successMessage.hidden = false;

  // Announce success
  statusEl.textContent = 'Success! You have been subscribed to our newsletter.';

  // Focus the success message for accessibility
  successMessage.focus();
}

function showError(input, message) {
  const errorEl = document.getElementById(`${input.id}-error`);
  input.setAttribute('aria-invalid', 'true');
  errorEl.textContent = message;
  errorEl.hidden = false;
  input.focus();

  // Announce error
  statusEl.textContent = `Error: ${message}`;
}

function resetForm() {
  form.reset();
  form.hidden = false;
  successMessage.hidden = true;
  document.getElementById('email').focus();
}
</script>

<style>
/* Loading spinner animation */
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.spinner {
  width: 1rem;
  height: 1rem;
  animation: spin 1s linear infinite;
}

.spinner circle {
  stroke-dasharray: 50;
  stroke-dashoffset: 20;
}

/* Button states */
button[type="submit"] {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  min-width: 140px;
  justify-content: center;
}

button[type="submit"]:disabled {
  opacity: 0.7;
  cursor: wait;
}

/* Success panel */
.success-panel {
  text-align: center;
  padding: 2rem;
  background: #f0fdf4;
  border: 2px solid #22c55e;
  border-radius: 8px;
}

.success-icon {
  width: 48px;
  height: 48px;
  color: #22c55e;
}

/* Focus for success panel */
.success-panel:focus {
  outline: 3px solid #22c55e;
  outline-offset: 2px;
}
</style>
```

**Benefits:**
- Loading state with disabled button prevents double submissions
- Screen reader users are notified of state changes via ARIA live regions
- Clear success confirmation builds user confidence
- Specific error messages with recovery guidance help users fix issues
- Form data is preserved on error so users do not need to retype

Reference: [web.dev - Form Submission](https://web.dev/learn/forms/auto)


---

## Respect Prefers-Reduced-Motion

**Impact: CRITICAL (WCAG 2.1 Level AAA - Animation from interactions)**

Some users experience motion sickness, vestibular disorders, or distraction from animations. The `prefers-reduced-motion` media query lets users opt out of non-essential motion. Respecting this preference is crucial for accessibility.

## Incorrect

```tsx
// ❌ Bad: No reduced motion consideration
<div className="animate-bounce">
  Bouncing element
</div>

// ❌ Bad: JavaScript animations without check
useEffect(() => {
  animate(element, { x: 100 }, { duration: 1000 })
}, [])
```

**Problems:**
- Animations play regardless of user preference, triggering vestibular disorders
- No CSS or JavaScript checks for the `prefers-reduced-motion` media query
- Continuous animations (bounce, pulse) are especially problematic
- Users have no way to opt out of motion on the page

## Correct

### CSS Approach

```css
/* ✅ Good: Base - no animation for reduced motion preference */
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
    scroll-behavior: auto !important;
  }
}

/* Or selectively disable */
.hero-animation {
  animation: float 3s ease-in-out infinite;
}

@media (prefers-reduced-motion: reduce) {
  .hero-animation {
    animation: none;
  }
}
```

### Tailwind CSS

```tsx
// ✅ Good: Use motion-reduce variant
<div className="
  transition-transform duration-300
  hover:scale-105
  motion-reduce:transition-none
  motion-reduce:hover:transform-none
">
  Hover me
</div>

// ✅ Good: Disable animation for reduced motion
<div className="
  animate-pulse
  motion-reduce:animate-none
">
  Loading...
</div>

// ✅ Good: Simplify rather than remove
<div className="
  transition-all duration-300 ease-out
  motion-reduce:transition-opacity motion-reduce:duration-150
">
  {/* Keeps opacity fade but removes transform */}
</div>
```

### JavaScript Check

```tsx
// ✅ Good: Hook for checking preference
function usePrefersReducedMotion(): boolean {
  const [prefersReducedMotion, setPrefersReducedMotion] = useState(false)

  useEffect(() => {
    const mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)')

    setPrefersReducedMotion(mediaQuery.matches)

    const handler = (event: MediaQueryListEvent) => {
      setPrefersReducedMotion(event.matches)
    }

    mediaQuery.addEventListener('change', handler)
    return () => mediaQuery.removeEventListener('change', handler)
  }, [])

  return prefersReducedMotion
}

// Usage
function AnimatedComponent() {
  const prefersReducedMotion = usePrefersReducedMotion()

  return (
    <motion.div
      initial={{ opacity: 0, y: prefersReducedMotion ? 0 : 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: prefersReducedMotion ? 0 : 0.3 }}
    >
      Content
    </motion.div>
  )
}
```

### Framer Motion Integration

```tsx
// ✅ Good: Framer Motion reduced motion support
import { motion, useReducedMotion } from 'framer-motion'

function Card() {
  const shouldReduceMotion = useReducedMotion()

  return (
    <motion.div
      initial={{ opacity: 0, scale: shouldReduceMotion ? 1 : 0.9 }}
      animate={{ opacity: 1, scale: 1 }}
      transition={{
        duration: shouldReduceMotion ? 0.001 : 0.3,
      }}
      whileHover={shouldReduceMotion ? {} : { scale: 1.05 }}
    >
      Card content
    </motion.div>
  )
}
```

### Animation with Fallback

```tsx
// ✅ Good: Show static alternative for reduced motion
function LoadingIndicator() {
  const prefersReducedMotion = usePrefersReducedMotion()

  if (prefersReducedMotion) {
    return <span>Loading...</span>  // Text instead of spinner
  }

  return (
    <div className="animate-spin rounded-full h-8 w-8 border-2 border-blue-600 border-t-transparent" />
  )
}
```

**Benefits:**
- Users with vestibular disorders can browse without triggering motion sickness
- Respects user OS-level preference for reduced motion
- Reduces CPU and battery usage when animations are disabled
- WCAG 2.3.3 Animation from Interactions compliance
- Provides static alternatives that preserve the information conveyed by animation

## What to Keep vs Remove

### Should Reduce/Remove
- Parallax effects
- Hover animations on non-interactive elements
- Loading spinners (use text)
- Page transition animations
- Auto-playing carousels
- Continuous animations (pulse, bounce)

### Can Keep (Essential Motion)
- Progress indicators
- Micro-interactions that provide feedback
- Opacity transitions (usually fine)
- Focus indicators
- State change indicators

```tsx
// ✅ Good: Essential feedback - keep but simplify
<button
  className={cn(
    'transition-colors',  // Color change is usually OK
    'motion-reduce:transition-none',
    'motion-safe:hover:scale-105 motion-safe:active:scale-95'  // Scale only for motion-safe
  )}
>
  Submit
</button>
```

## Testing

```tsx
// Simulate in DevTools:
// Chrome: Rendering panel > Emulate CSS media feature > prefers-reduced-motion
// Firefox: about:config > ui.prefersReducedMotion (0=no-preference, 1=reduce)

// Or in CSS:
@media (prefers-reduced-motion: no-preference) {
  /* Animations for users who haven't opted out */
}

@media (prefers-reduced-motion: reduce) {
  /* Reduced/no animations */
}
```

Reference: [WCAG 2.3.3 Animation from Interactions](https://www.w3.org/WAI/WCAG21/Understanding/animation-from-interactions.html)


---

## Optimize Image Loading for UX

**Impact: MEDIUM (Prevents layout shifts and improves perceived performance)**

Unoptimized images are a leading cause of layout shifts and slow page loads. Providing explicit dimensions, lazy loading off-screen images, and serving modern formats dramatically improves both Core Web Vitals and perceived performance.

## Incorrect

```html
<!-- ❌ Bad: no dimensions, no lazy loading, no responsive sources, missing alt -->
<img src="/photos/hero.jpg" />

<img src="/photos/team.png" />

<img src="/photos/product.jpg" class="product-thumb" />
```

```tsx
// ❌ Bad: React component with same issues
function Gallery() {
  return (
    <div>
      <img src="/photos/hero.jpg" />
      <img src="/photos/team.png" />
      {products.map((p) => (
        <img key={p.id} src={p.image} />
      ))}
    </div>
  )
}
```

**Problems:**
- Missing `width` and `height` causes Cumulative Layout Shift (CLS) as images load
- All images load eagerly, blocking the critical rendering path
- No modern format (WebP/AVIF) sources wastes bandwidth
- Missing `alt` text harms accessibility and SEO
- No `fetchpriority` hint means the browser cannot prioritize the hero image

## Correct

```html
<!-- ✅ Good: hero image with high priority, explicit dimensions, alt text -->
<img
  src="/photos/hero.jpg"
  alt="Team collaboration in the office"
  width="1200"
  height="600"
  fetchpriority="high"
/>

<!-- ✅ Good: below-fold image with lazy loading and responsive sources -->
<picture>
  <source srcset="/photos/team.avif" type="image/avif" />
  <source srcset="/photos/team.webp" type="image/webp" />
  <img
    src="/photos/team.png"
    alt="Our engineering team"
    width="800"
    height="450"
    loading="lazy"
    decoding="async"
  />
</picture>

<!-- ✅ Good: responsive image with srcset for different viewports -->
<img
  src="/photos/product-400.jpg"
  srcset="
    /photos/product-400.jpg 400w,
    /photos/product-800.jpg 800w,
    /photos/product-1200.jpg 1200w
  "
  sizes="(max-width: 600px) 400px, (max-width: 1024px) 800px, 1200px"
  alt="Product thumbnail"
  width="400"
  height="400"
  loading="lazy"
  decoding="async"
/>
```

```tsx
// ✅ Good: React component with optimized image loading
function Gallery({ products }: { products: Product[] }) {
  return (
    <div>
      {/* Hero image: high priority, no lazy loading */}
      <img
        src="/photos/hero.jpg"
        alt="Team collaboration in the office"
        width={1200}
        height={600}
        fetchPriority="high"
      />

      {/* Below-fold image: modern formats with fallback */}
      <picture>
        <source srcSet="/photos/team.avif" type="image/avif" />
        <source srcSet="/photos/team.webp" type="image/webp" />
        <img
          src="/photos/team.png"
          alt="Our engineering team"
          width={800}
          height={450}
          loading="lazy"
          decoding="async"
        />
      </picture>

      {/* Product list: lazy loaded with dimensions */}
      {products.map((p) => (
        <img
          key={p.id}
          src={p.image}
          alt={p.name}
          width={300}
          height={300}
          loading="lazy"
          decoding="async"
        />
      ))}
    </div>
  )
}
```

**Benefits:**
- Explicit `width` and `height` eliminate layout shifts by reserving space before load
- `loading="lazy"` defers off-screen images, reducing initial page weight
- `<picture>` with WebP/AVIF sources can reduce image size by 25-50%
- `fetchpriority="high"` on the hero image improves Largest Contentful Paint (LCP)
- `decoding="async"` prevents image decoding from blocking the main thread
- Proper `alt` text improves accessibility and SEO

Reference: [web.dev - Optimize images](https://web.dev/articles/fast#optimize_your_images)


---

## Prevent Cumulative Layout Shift

**Impact: MEDIUM (Layout shifts frustrate users and hurt Core Web Vitals)**

Cumulative Layout Shift (CLS) measures unexpected movement of visible page content. High CLS causes misclicks, reading disruption, and user frustration. Reserve space for dynamic content, stabilize fonts, and use skeletons to keep layouts predictable.

## Incorrect

```css
/* ❌ Bad: no space reserved for dynamic content */
.ad-banner {
  /* No dimensions — shifts page when ad loads */
}

.video-wrapper {
  /* No aspect ratio — collapses until video loads */
}

/* ❌ Bad: font swap without size adjustment causes text reflow */
@font-face {
  font-family: 'BrandFont';
  src: url('/fonts/brand.woff2') format('woff2');
  font-display: swap;
  /* No size-adjust — fallback font has different metrics */
}
```

```tsx
// ❌ Bad: dynamic content injected without reserved space
function Feed() {
  const [banner, setBanner] = useState<Banner | null>(null)

  useEffect(() => {
    fetchBanner().then(setBanner)
  }, [])

  return (
    <div>
      <h1>Latest News</h1>
      {/* Banner pops in and pushes content down */}
      {banner && (
        <div>
          <img src={banner.image} alt={banner.alt} />
        </div>
      )}
      <ArticleList />
    </div>
  )
}

// ❌ Bad: embed without dimensions
function VideoSection() {
  return (
    <div>
      <iframe
        src="https://www.youtube.com/embed/dQw4w9WgXcQ"
        title="Video"
      />
    </div>
  )
}
```

**Problems:**
- Ads and banners without reserved space push content down when they load
- Embeds and media without dimensions collapse to zero height then expand
- Font swap without `size-adjust` causes text to reflow as the web font loads (FOUT)
- Users accidentally click the wrong element when content shifts beneath their cursor

## Correct

```css
/* ✅ Good: reserve space for media with aspect-ratio */
.video-wrapper {
  aspect-ratio: 16 / 9;
  width: 100%;
  background-color: #f0f0f0;
}

.video-wrapper iframe {
  width: 100%;
  height: 100%;
}

/* ✅ Good: reserve fixed space for ad banners */
.ad-banner {
  min-height: 250px;
  width: 100%;
  background-color: #fafafa;
  contain: layout;
}

/* ✅ Good: font swap with size-adjust to match fallback metrics */
@font-face {
  font-family: 'BrandFont';
  src: url('/fonts/brand.woff2') format('woff2');
  font-display: swap;
  size-adjust: 105%;
  ascent-override: 90%;
  descent-override: 22%;
  line-gap-override: 0%;
}

/* ✅ Good: aspect-ratio for responsive images */
.thumbnail {
  aspect-ratio: 4 / 3;
  width: 100%;
  object-fit: cover;
  background-color: #e5e7eb;
}

/* ✅ Good: skeleton placeholder animation */
.skeleton {
  background: linear-gradient(90deg, #e5e7eb 25%, #f3f4f6 50%, #e5e7eb 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: 4px;
}

@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}
```

```tsx
// ✅ Good: skeleton placeholder reserves space for async content
function BannerSkeleton() {
  return (
    <div
      className="w-full rounded-lg bg-gray-200 animate-pulse"
      style={{ minHeight: 200 }}
      aria-hidden="true"
    />
  )
}

function Feed() {
  const [banner, setBanner] = useState<Banner | null>(null)
  const [isLoading, setIsLoading] = useState(true)

  useEffect(() => {
    fetchBanner()
      .then(setBanner)
      .finally(() => setIsLoading(false))
  }, [])

  return (
    <div>
      <h1>Latest News</h1>
      {/* Space is always reserved — no layout shift */}
      <div style={{ minHeight: 200 }}>
        {isLoading ? (
          <BannerSkeleton />
        ) : banner ? (
          <img
            src={banner.image}
            alt={banner.alt}
            width={800}
            height={200}
          />
        ) : null}
      </div>
      <ArticleList />
    </div>
  )
}

// ✅ Good: embed with aspect-ratio wrapper
function VideoSection() {
  return (
    <div style={{ aspectRatio: '16 / 9', width: '100%', backgroundColor: '#f0f0f0' }}>
      <iframe
        src="https://www.youtube.com/embed/dQw4w9WgXcQ"
        title="Video"
        width="100%"
        height="100%"
        loading="lazy"
      />
    </div>
  )
}

// ✅ Good: ad slot with reserved dimensions and containment
function AdSlot({ slotId }: { slotId: string }) {
  return (
    <div
      style={{
        minHeight: 250,
        width: '100%',
        contain: 'layout',
        backgroundColor: '#fafafa',
      }}
      data-ad-slot={slotId}
    >
      {/* Ad script injects content here without shifting layout */}
    </div>
  )
}
```

**Benefits:**
- `aspect-ratio` reserves the correct space for media before it loads
- Skeleton placeholders prevent content from jumping as data arrives
- `font-display: swap` with `size-adjust` minimizes text reflow during font loading
- `contain: layout` prevents ad content from affecting surrounding layout
- Reserved `min-height` on dynamic slots keeps content below them stable
- Improved CLS score directly benefits search ranking and user satisfaction

Reference: [web.dev - Cumulative Layout Shift](https://web.dev/articles/cls)


---

