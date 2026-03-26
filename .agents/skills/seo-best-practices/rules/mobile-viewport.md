---
title: Viewport and Responsive Configuration
impact: MEDIUM
impactDescription: "Required for mobile-first indexing"
tags: viewport, responsive, mobile-first
---

## Viewport and Responsive Configuration

**Impact: MEDIUM (Required for mobile-first indexing)**

Google uses mobile-first indexing, meaning it primarily uses the mobile version of your page for ranking and indexing. A properly configured viewport meta tag is the foundation of responsive design and is required for your page to render correctly on mobile devices and pass Google's mobile-friendliness checks.

## Incorrect

```html
<!-- ❌ Bad: missing viewport meta tag entirely -->
<head>
  <title>My Website</title>
  <!-- No viewport tag — mobile browsers render at desktop width (typically 980px) -->
</head>
```

```html
<!-- ❌ Bad: user-scalable=no and maximum-scale restriction -->
<head>
  <meta
    name="viewport"
    content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"
  />
</head>
```

```html
<!-- ❌ Bad: fixed-width layout -->
<head>
  <meta name="viewport" content="width=1024" />
</head>
<body>
  <div style="width: 1024px; margin: 0 auto;">
    <!-- Fixed-width content that requires horizontal scrolling on mobile -->
  </div>
</body>
```

**Problems:**
- Without a viewport tag, mobile browsers render at a default width of ~980px and zoom out, making text unreadable
- `user-scalable=no` and `maximum-scale=1.0` prevent users from zooming, which is an accessibility violation (WCAG 1.4.4)
- A fixed pixel width like `width=1024` forces horizontal scrolling on any device narrower than 1024px
- Google's mobile-friendliness test will flag all of these as failures

## Correct

```html
<!-- ✅ Good: standard viewport tag with no zoom restrictions -->
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
</head>
```

```css
/* ✅ Good: responsive layout using relative units and media queries */
.container {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

.grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
}

@media (min-width: 768px) {
  .grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 1024px) {
  .grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

/* Use relative units for typography */
body {
  font-size: 1rem; /* 16px base */
  line-height: 1.5;
}

h1 {
  font-size: clamp(1.5rem, 4vw, 2.5rem);
}

/* Prevent images from overflowing */
img {
  max-width: 100%;
  height: auto;
}
```

**Benefits:**
- `width=device-width` tells the browser to match the page width to the screen width, eliminating horizontal scrolling
- No `maximum-scale` or `user-scalable` restriction ensures users can zoom for accessibility
- Relative units (`rem`, `%`, `vw`) and CSS Grid/Flexbox adapt layout to any screen size
- `clamp()` for typography scales text smoothly between breakpoints without media queries
- `max-width: 100%` on images prevents overflow on narrow screens

Reference: [Responsive web design basics](https://web.dev/articles/responsive-web-design-basics)
