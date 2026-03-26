---
title: Use Semantic HTML Elements
impact: CRITICAL
impactDescription: "WCAG 2.1 Level A - Foundation for accessibility"
tags: accessibility, semantic-html, screen-readers, seo
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
