---
title: Configure Dependency Pre-bundling
impact: HIGH
impactDescription: "2-5x faster cold start"
tags: dev, dependencies, prebundling, optimization, vite
---

## Configure Dependency Pre-bundling

**Impact: HIGH (2-5x faster cold start)**

Vite pre-bundles dependencies to convert CommonJS/UMD to ESM and reduce the number of module requests. Proper configuration speeds up cold starts and prevents runtime issues.

## Incorrect

```typescript
// ❌ Bad: No optimizeDeps configuration
export default defineConfig({
  // Vite auto-detects but may miss some deps
})
```

**Problems:**
- Some dependencies may not be pre-bundled, causing slow page loads
- Cold start can be slow with many dependencies
- Runtime errors from unbundled CommonJS modules
- Repeated "optimizing dependencies" messages during development

## Correct

```typescript
// ✅ Good: Explicitly include dependencies for pre-bundling
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],

  optimizeDeps: {
    include: [
      'react',
      'react-dom',
      'react-router-dom',
      '@tanstack/react-query',
      'zustand',
      'axios',
      'date-fns',
      'react-dom/client',
    ],

    exclude: [
      // '@some/esm-only-package',
    ],
  },
})
```

```typescript
// ✅ Good: Handle CommonJS dependencies
export default defineConfig({
  optimizeDeps: {
    include: [
      'lodash-es',
      'linked-package > some-dep',
    ],

    esbuildOptions: {
      define: {
        global: 'globalThis',
      },
    },
  },
})
```

```typescript
// ✅ Good: Warmup frequently used files (Vite 5+)
export default defineConfig({
  server: {
    warmup: {
      clientFiles: [
        './src/main.tsx',
        './src/App.tsx',
        './src/components/index.ts',
      ],
    },
  },
})
```

```bash
# Force re-bundling when deps update
vite --force
```

```bash
# Debug pre-bundling
DEBUG=vite:deps vite

# Check the pre-bundle output
ls node_modules/.vite/deps/
```

**Benefits:**
- 2-5x faster cold start by pre-bundling dependencies upfront
- Eliminates "optimizing dependencies" interruptions during development
- Prevents CommonJS/ESM compatibility issues at runtime
- Server warmup pre-transforms critical files on start for instant page loads

Reference: [Vite Dep Pre-Bundling](https://vitejs.dev/guide/dep-pre-bundling.html)
