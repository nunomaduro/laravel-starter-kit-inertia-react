---
title: Configure Manual Chunks for Vendor Separation
impact: CRITICAL
impactDescription: "Optimal caching and parallel loading"
tags: build, chunks, vendor, optimization, rollup
---

## Configure Manual Chunks for Vendor Separation

**Impact: CRITICAL (Optimal caching and parallel loading)**

Without manual chunks, Vite bundles all vendor dependencies into a single chunk or mixes them with application code, leading to large initial downloads and poor cache efficiency.

## Incorrect

```typescript
// vite.config.ts
export default defineConfig({
  plugins: [react()],
  build: {
    // No manual chunks configured
    // All code bundled together
  },
})
```

**Problems:**
- React, React DOM, and other vendors are bundled with application code
- When you update your app, users must re-download everything
- No parallel loading of separate chunks
- Poor long-term caching — vendor code invalidated with every app change

## Correct

```typescript
// vite.config.ts
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  build: {
    rollupOptions: {
      output: {
        manualChunks: {
          // Core React - rarely changes
          'vendor-react': ['react', 'react-dom'],

          // Router - changes occasionally
          'vendor-router': ['react-router-dom'],

          // UI library - if using one
          // 'vendor-ui': ['@headlessui/react', '@heroicons/react'],

          // State management
          // 'vendor-state': ['zustand', '@tanstack/react-query'],
        },
      },
    },
  },
})
```

```typescript
// vite.config.ts - Dynamic manual chunks function
export default defineConfig({
  build: {
    rollupOptions: {
      output: {
        manualChunks(id) {
          // Node modules go to vendor chunk
          if (id.includes('node_modules')) {
            // Split large libraries into separate chunks
            if (id.includes('react-dom')) {
              return 'vendor-react-dom'
            }
            if (id.includes('react')) {
              return 'vendor-react'
            }
            if (id.includes('@tanstack')) {
              return 'vendor-tanstack'
            }
            // Other node_modules
            return 'vendor'
          }
        },
      },
    },
  },
})
```

**Benefits:**
- Vendor chunks cached separately from app code
- Browser can download multiple chunks simultaneously
- App changes don't invalidate vendor cache
- Smaller, more targeted cache invalidation on updates

> **Note:** Vite is transitioning from Rollup to Rolldown as its bundler. When Rolldown is fully integrated, `advancedChunks` will be the recommended replacement for `manualChunks`, offering more powerful and flexible chunking strategies. Keep an eye on Vite release notes for migration guidance.

Reference: [Vite Build Options - rollupOptions](https://vitejs.dev/config/build-options.html#build-rollupoptions)
