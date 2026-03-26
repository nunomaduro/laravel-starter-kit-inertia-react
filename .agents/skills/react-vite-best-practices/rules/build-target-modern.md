---
title: Target Modern Browsers for Smaller Bundles
impact: CRITICAL
impactDescription: "10-15% smaller bundles"
tags: build, target, modern, optimization, vite
---

## Target Modern Browsers for Smaller Bundles

**Impact: CRITICAL (10-15% smaller bundles)**

Vite defaults to `'baseline-widely-available'`, which targets browser features that are widely available across all major browsers. Explicitly targeting older browsers includes unnecessary polyfills and transpilation, increasing bundle size.

## Incorrect

```typescript
// vite.config.ts - Targeting old browsers unnecessarily
export default defineConfig({
  build: {
    target: 'es2015', // Too old, includes many polyfills
  },
})
```

**Problems:**
- Targeting es2015 adds polyfills for features all modern browsers support natively
- Larger bundle size from unnecessary transpilation
- Slower builds due to extra transformation passes

## Correct

```typescript
// vite.config.ts
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  build: {
    // Default is 'baseline-widely-available' — good for most apps
    // Use 'esnext' for the smallest bundle if you control the browser environment
    target: 'esnext',

    // Or be specific about browser versions
    // target: ['es2022', 'edge88', 'firefox78', 'chrome87', 'safari14'],
  },
})
```

```typescript
// vite.config.ts - With legacy browser support
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import legacy from '@vitejs/plugin-legacy'

export default defineConfig({
  plugins: [
    react(),
    legacy({
      targets: ['defaults', 'not IE 11'],
      // Modern chunks for modern browsers
      // Legacy chunks only loaded by old browsers
    }),
  ],
  build: {
    target: 'esnext', // Modern build
  },
})
```

**Benefits:**
- `esnext` produces the smallest bundles by using native browser features
- `baseline-widely-available` (default) balances size with broad compatibility
- The `@vitejs/plugin-legacy` plugin provides a fallback for older browsers without penalizing modern ones
- Specific browser version targets give fine-grained control

| Target | Use Case |
|--------|----------|
| `esnext` | Latest features, smallest bundle |
| `baseline-widely-available` | Default — broad modern browser support |
| `es2022` | Good balance, wide support |
| Custom array | Specific browser versions |

Reference: [Vite Build Options - target](https://vitejs.dev/config/build-options.html#build-target)
