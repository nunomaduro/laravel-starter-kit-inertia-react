---
title: Bundle Analysis with Visualizer
impact: MEDIUM
impactDescription: "Can't optimize what you can't measure"
tags: bundle, analysis, visualizer, rollup
---

## Bundle Analysis with Visualizer

**Impact: MEDIUM (Can't optimize what you can't measure)**

Without bundle analysis, large dependencies go unnoticed and bundle size creeps up over time. A visualizer gives you an interactive map of exactly what is in your bundle and how much space each module takes.

## Incorrect

```typescript
// ❌ Bad — guessing which dependencies are large
// "I think lodash is big, let me remove it"
// "The bundle seems slow, maybe it's the icons?"

// vite.config.ts — no analysis tooling
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  // No way to know:
  // - Which dependency is the largest?
  // - Is tree-shaking working?
  // - Are there duplicate packages?
  // - Did that new library add 200KB?
})
```

**Problems:**
- No visibility into what makes the bundle large
- Optimization efforts are based on guesswork
- Regressions in bundle size go undetected
- Duplicate or unused dependencies waste bandwidth
- Cannot verify tree-shaking is working correctly

## Correct

```bash
npm install -D rollup-plugin-visualizer
```

```typescript
// ✅ Good — vite.config.ts with bundle visualizer
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import { visualizer } from 'rollup-plugin-visualizer'

export default defineConfig({
  plugins: [
    react(),
    visualizer({
      filename: 'stats.html',      // Output file
      open: true,                   // Auto-open in browser after build
      gzipSize: true,               // Show gzipped sizes
      brotliSize: true,             // Show brotli-compressed sizes
      template: 'treemap',          // 'treemap' | 'sunburst' | 'network'
    }),
  ],
})
```

```typescript
// ✅ Good — only enable visualizer when analyzing (not every build)
import { defineConfig, type PluginOption } from 'vite'
import react from '@vitejs/plugin-react'
import { visualizer } from 'rollup-plugin-visualizer'

export default defineConfig({
  plugins: [
    react(),
    // Only include visualizer when ANALYZE env var is set
    process.env.ANALYZE === 'true' &&
      visualizer({
        filename: 'stats.html',
        open: true,
        gzipSize: true,
        brotliSize: true,
        template: 'treemap',
      }),
  ].filter(Boolean) as PluginOption[],
})
```

```json
// ✅ Good — add an analyze script to package.json
{
  "scripts": {
    "build": "vite build",
    "analyze": "ANALYZE=true vite build"
  }
}
```

```bash
# Run the analysis
npm run analyze
# Opens stats.html in browser with an interactive treemap
```

```
# How to read the visualizer output:
#
# 1. Large rectangles = large modules — focus optimization here
# 2. Check for:
#    - Unexpectedly large dependencies (e.g., moment.js, lodash full build)
#    - Duplicate packages (same lib bundled twice at different versions)
#    - Code that should be lazy-loaded but is in the main chunk
#    - Entire icon libraries when only a few icons are used
#
# 3. Common fixes after analysis:
#    - Replace moment.js (330KB) with date-fns or dayjs (2-7KB)
#    - Use named imports: import { debounce } from 'lodash-es'
#    - Lazy-load heavy routes: React.lazy(() => import('./HeavyPage'))
#    - Split vendor chunks in build.rollupOptions.output.manualChunks
```

**Benefits:**
- Interactive visualization of every module in the bundle
- Gzip and Brotli size estimates show real-world transfer sizes
- Catches regressions when new dependencies are added
- Verifies tree-shaking is eliminating unused code
- On-demand analysis avoids slowing down regular builds

Reference: [rollup-plugin-visualizer](https://github.com/btd/rollup-plugin-visualizer)
