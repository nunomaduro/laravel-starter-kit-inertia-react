---
title: Web Font Loading Strategy
impact: HIGH
impactDescription: "Prevents FOIT/FOUT and reduces CLS"
tags: fonts, performance, cls, web-fonts
---

## Web Font Loading Strategy

**Impact: HIGH (Prevents FOIT/FOUT and reduces CLS)**

Web fonts cause Flash of Invisible Text (FOIT) or Flash of Unstyled Text (FOUT) that shifts layout and degrades user experience. A proper font loading strategy uses `font-display: swap`, preloads critical fonts, subsets character sets, and self-hosts to eliminate third-party round trips.

## Incorrect

```css
/* ❌ Bad: no font-display, loading all weights from third-party CDN */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=block');

body {
  font-family: 'Inter', sans-serif;
}
```

```html
<!-- ❌ Bad: loading fonts from third-party CDN with no preconnect -->
<head>
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&family=Playfair+Display:wght@400;700&display=block"
    rel="stylesheet"
  />
</head>
```

**Problems:**
- `font-display: block` causes FOIT, hiding text for up to 3 seconds while the font downloads
- Loading all 9 font weights downloads hundreds of kilobytes that are never used
- Third-party CSS from Google Fonts requires DNS lookup, TCP connection, and TLS handshake to `fonts.googleapis.com`, then another connection to `fonts.gstatic.com`
- `@import` in CSS is render-blocking and delays font discovery further

## Correct

```css
/* ✅ Good: self-hosted, subsetted fonts with font-display: swap and size-adjust */
@font-face {
  font-family: 'Inter';
  src: url('/fonts/inter-latin-400.woff2') format('woff2');
  font-weight: 400;
  font-style: normal;
  font-display: swap;
  unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+2000-206F;
}

@font-face {
  font-family: 'Inter';
  src: url('/fonts/inter-latin-700.woff2') format('woff2');
  font-weight: 700;
  font-style: normal;
  font-display: swap;
  unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+2000-206F;
}

/* Fallback font with size-adjust to minimize CLS */
@font-face {
  font-family: 'Inter Fallback';
  src: local('Arial');
  size-adjust: 107%;
  ascent-override: 90%;
  descent-override: 22%;
  line-gap-override: 0%;
}

body {
  font-family: 'Inter', 'Inter Fallback', system-ui, sans-serif;
}
```

```html
<!-- ✅ Good: preload critical font files -->
<head>
  <link
    rel="preload"
    as="font"
    type="font/woff2"
    href="/fonts/inter-latin-400.woff2"
    crossorigin
  />
  <link rel="stylesheet" href="/css/fonts.css" />
</head>
```

**Benefits:**
- `font-display: swap` shows text immediately in the fallback font, eliminating FOIT
- `size-adjust` on the fallback font matches metrics to the web font, minimizing layout shift (CLS) during the swap
- Self-hosting eliminates third-party DNS lookups, connection overhead, and potential downtime
- Subsetting with `unicode-range` reduces each font file to only the characters needed
- Preloading the regular weight font starts the download before CSS is parsed

Reference: [Best practices for fonts](https://web.dev/articles/font-best-practices)
