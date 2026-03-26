---
title: Semantic HTML for SEO
impact: HIGH
impactDescription: "Semantic elements help crawlers understand page structure"
tags: semantic-html, accessibility, structure
---

## Semantic HTML for SEO

**Impact: HIGH (Semantic elements help crawlers understand page structure)**

Semantic HTML gives meaning to your markup so search engines can distinguish navigation from content, sidebars from articles, and headers from footers. This improves indexing accuracy and accessibility compliance.

## Incorrect

```html
<!-- ❌ All divs with no semantic meaning -->
<div class="header">
  <div class="logo">My Site</div>
  <div class="nav">
    <div class="nav-item"><a href="/">Home</a></div>
    <div class="nav-item"><a href="/blog">Blog</a></div>
    <div class="nav-item"><a href="/contact">Contact</a></div>
  </div>
</div>

<div class="content">
  <div class="post">
    <div class="post-title">Understanding Semantic HTML</div>
    <div class="post-body">
      <p>Semantic HTML is important for SEO...</p>
    </div>
  </div>
  <div class="sidebar">
    <div class="widget">Related Posts</div>
  </div>
</div>

<div class="footer">
  <div class="copyright">&copy; 2026 My Site</div>
</div>
```

**Problems:**
- Crawlers cannot distinguish navigation, content, and supplementary sections
- Screen readers have no landmark regions to jump between
- The document structure is invisible without inspecting class names

## Correct

```html
<!-- ✅ Proper semantic elements with one main per page -->
<header>
  <a href="/" class="logo">My Site</a>
  <nav aria-label="Primary">
    <ul>
      <li><a href="/">Home</a></li>
      <li><a href="/blog">Blog</a></li>
      <li><a href="/contact">Contact</a></li>
    </ul>
  </nav>
</header>

<main>
  <article>
    <h1>Understanding Semantic HTML</h1>
    <p>Semantic HTML is important for SEO...</p>

    <section>
      <h2>Why It Matters</h2>
      <p>Search engines use element types to weight content relevance.</p>
    </section>

    <section>
      <h2>Key Elements</h2>
      <p>The most impactful elements are main, article, nav, and section.</p>
    </section>
  </article>

  <aside aria-label="Sidebar">
    <h2>Related Posts</h2>
    <ul>
      <li><a href="/blog/html5-guide">HTML5 Guide</a></li>
    </ul>
  </aside>
</main>

<footer>
  <p>&copy; 2026 My Site</p>
</footer>
```

**Benefits:**
- Crawlers identify the primary content via `<main>` and `<article>`, boosting indexing accuracy
- `<nav>` signals navigation links, helping search engines discover internal pages
- `<aside>` marks supplementary content so it is not confused with the main topic
- Screen readers can jump between landmark regions (`header`, `main`, `footer`, `nav`)

Reference: [MDN - HTML Elements Reference](https://developer.mozilla.org/en-US/docs/Web/HTML/Element)
