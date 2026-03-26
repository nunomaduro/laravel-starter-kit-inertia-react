---
title: Provide Skip Links for Navigation
impact: HIGH
impactDescription: "WCAG 2.1 Level A - Bypass blocks"
tags: accessibility, navigation, keyboard, skip-links
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
