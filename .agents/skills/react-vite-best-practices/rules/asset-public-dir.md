---
title: Public Directory vs Import
impact: HIGH
impactDescription: "Wrong asset handling breaks caching and increases bundle size"
tags: assets, public, import, static
---

## Public Directory vs Import

**Impact: HIGH (Wrong asset handling breaks caching and increases bundle size)**

Vite offers two ways to serve assets: the `public/` directory and JavaScript imports. Using the wrong one leads to cache busting failures or unnecessary bundling. Prefer importing assets via JavaScript unless the specific guarantees of the public directory are required.

## Incorrect

```tsx
// ❌ Bad — importing files that should stay static in public/
import robots from '../public/robots.txt?raw'
import manifest from '../public/manifest.json'

// ❌ Bad — putting everything in public/ to avoid imports
function Logo() {
  return <img src="/logo.png" alt="Logo" /> // No hash, no cache busting
}

function App() {
  return (
    <div>
      <Logo />
      {/* All assets in public/ — none get hashed */}
      <img src="/hero-banner.png" alt="Hero" />
      <img src="/icons/arrow.svg" alt="Arrow" />
    </div>
  )
}
```

**Problems:**
- Assets in `public/` are served as-is with no content hashing — browser cache issues on updates
- Importing from `public/` bypasses Vite's asset pipeline
- No tree-shaking or dead code elimination for unused assets
- Large images in `public/` are not optimized or inlined by Vite
- Missing assets in `public/` fail silently at runtime instead of at build time

## Correct

```tsx
// ✅ Good — import assets that benefit from hashing and optimization
import logo from './assets/logo.png'        // → /assets/logo-a1b2c3d4.png
import heroBanner from './assets/hero.png'   // → /assets/hero-e5f6g7h8.png
import ArrowIcon from './assets/arrow.svg?react'

function App() {
  return (
    <div>
      {/* Imported — hashed filename, cache-busted on change */}
      <img src={logo} alt="Logo" />
      <img src={heroBanner} alt="Hero" />
      <ArrowIcon />
    </div>
  )
}
```

```
# ✅ Good — public/ only for files that MUST keep exact names
public/
├── favicon.ico          # Browsers look for exact path
├── robots.txt           # Crawlers expect /robots.txt
├── manifest.json        # PWA manifest at fixed URL
├── _redirects           # Hosting platform config (Netlify)
└── og-image.png         # Open Graph — URL shared externally
```

```typescript
// ✅ Good — reference public/ files by absolute path (no import needed)
function Head() {
  return (
    <Helmet>
      <link rel="icon" href="/favicon.ico" />
      <meta property="og:image" content="/og-image.png" />
    </Helmet>
  )
}
```

```typescript
// ✅ Good — dynamic imports for assets based on runtime values
function CountryFlag({ code }: { code: string }) {
  // Vite glob import — all matched files are hashed
  const flags = import.meta.glob('./assets/flags/*.svg', {
    eager: true,
    as: 'url',
  })

  const src = flags[`./assets/flags/${code}.svg`]
  return src ? <img src={src} alt={code} /> : null
}
```

**Benefits:**
- Imported assets get content-hashed filenames for reliable cache busting
- Build fails if an imported asset is missing — no silent 404s at runtime
- Small assets are automatically inlined as base64 (below `assetsInlineLimit`)
- `public/` files keep exact names required by browsers and external services
- Clear separation of concerns between processed and static assets

Reference: [Vite Static Asset Handling](https://vite.dev/guide/assets.html#the-public-directory)
