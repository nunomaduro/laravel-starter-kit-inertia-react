---
title: Web Font Loading in Vite
impact: HIGH
impactDescription: "Font loading affects LCP and CLS"
tags: fonts, performance, loading, cls
---

## Web Font Loading in Vite

**Impact: HIGH (Font loading affects LCP and CLS)**

Render-blocking external font requests add network round trips and cause layout shifts. Self-hosting fonts with proper preloading eliminates third-party dependencies and gives you full control over loading behavior.

## Incorrect

```tsx
// ❌ Bad — render-blocking CDN font in index.html
// index.html
<head>
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap"
    rel="stylesheet"
  />
</head>
```

```tsx
// ❌ Bad — no font-display, no preload, full character set
// styles/global.css
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700');

body {
  font-family: 'Inter', sans-serif;
}
```

**Problems:**
- Render-blocking request to third-party CDN
- Extra DNS lookup and TLS handshake for fonts.googleapis.com and fonts.gstatic.com
- No control over font-display behavior
- Full character set downloaded even if only Latin is needed
- GDPR concerns with Google Fonts CDN (user IP sent to Google)

## Correct

```bash
# Download font files locally (e.g., Inter-Regular.woff2, Inter-Medium.woff2, Inter-Bold.woff2)
# Place them in src/assets/fonts/
```

```css
/* src/styles/fonts.css */
/* ✅ Good — self-hosted, subsetted, font-display: swap */
@font-face {
  font-family: 'Inter';
  src: url('/src/assets/fonts/Inter-Regular.woff2') format('woff2');
  font-weight: 400;
  font-style: normal;
  font-display: swap;
  unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+2000-206F;
}

@font-face {
  font-family: 'Inter';
  src: url('/src/assets/fonts/Inter-Medium.woff2') format('woff2');
  font-weight: 500;
  font-style: normal;
  font-display: swap;
  unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+2000-206F;
}

@font-face {
  font-family: 'Inter';
  src: url('/src/assets/fonts/Inter-Bold.woff2') format('woff2');
  font-weight: 700;
  font-style: normal;
  font-display: swap;
  unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+2000-206F;
}
```

```html
<!-- ✅ Good — preload critical font in index.html -->
<head>
  <link
    rel="preload"
    href="/src/assets/fonts/Inter-Regular.woff2"
    as="font"
    type="font/woff2"
    crossorigin
  />
</head>
```

```typescript
// ✅ Good — vite.config.ts handles font files
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  build: {
    rollupOptions: {
      output: {
        assetFileNames: (assetInfo) => {
          if (assetInfo.name && /\.(woff2?|ttf|otf|eot)$/.test(assetInfo.name)) {
            return 'assets/fonts/[name]-[hash][extname]'
          }
          return 'assets/[name]-[hash][extname]'
        },
      },
    },
  },
})
```

**Benefits:**
- No third-party network requests or DNS lookups
- `font-display: swap` prevents invisible text during load
- `unicode-range` limits download to needed character sets
- Preloading ensures critical fonts load early, improving LCP
- Cache-busted font files via Vite's asset hashing
- Full GDPR compliance with no external requests

Reference: [Vite Static Asset Handling](https://vite.dev/guide/assets.html)
