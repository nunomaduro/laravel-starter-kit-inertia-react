---
title: Configure Build for Effective Tree Shaking
impact: CRITICAL
impactDescription: "15-30% smaller bundles"
tags: build, tree-shaking, optimization, dead-code, vite
---

## Configure Build for Effective Tree Shaking

**Impact: CRITICAL (15-30% smaller bundles)**

Configure your Vite build to effectively eliminate dead code through tree shaking, reducing bundle size significantly.

## Incorrect

```tsx
// ❌ Bad: Barrel export that prevents tree shaking
// utils/index.ts
export * from './strings';
export * from './numbers';
export * from './dates';
export * from './arrays';
export * from './objects';

// Using namespace imports
import * as utils from './utils';

function Component() {
  // Only using one function but importing everything
  return <div>{utils.formatDate(new Date())}</div>;
}
```

```tsx
// ❌ Bad: Importing entire libraries
import _ from 'lodash';
import moment from 'moment';

function processData(items: Item[]) {
  return _.uniqBy(items, 'id').map(item => ({
    ...item,
    date: moment(item.date).format('YYYY-MM-DD'),
  }));
}
```

```json
// ❌ Bad: package.json missing sideEffects field
{
  "name": "my-app",
  "version": "1.0.0",
  "main": "dist/index.js",
  "module": "dist/index.esm.js"
}
```

**Problems:**
- Barrel exports with `export *` pull in entire modules even when only one function is used
- Namespace imports (`import *`) prevent the bundler from identifying unused exports
- Libraries like `lodash` (CJS) and `moment` are not tree-shakeable
- Missing `sideEffects` field forces the bundler to assume all modules have side effects

## Correct

```tsx
// ✅ Good: Named exports for better tree shaking
// utils/index.ts
export { formatString, capitalize, truncate } from './strings';
export { formatNumber, clamp, round } from './numbers';
export { formatDate, parseDate, isValidDate } from './dates';
export { unique, groupBy, sortBy } from './arrays';
export { pick, omit, merge } from './objects';

// Direct named imports
import { formatDate } from './utils';

function Component() {
  return <div>{formatDate(new Date())}</div>;
}
```

```tsx
// ✅ Good: Import only what you need from tree-shakeable libraries
import uniqBy from 'lodash-es/uniqBy';
import { format } from 'date-fns';

function processData(items: Item[]) {
  return uniqBy(items, 'id').map(item => ({
    ...item,
    date: format(new Date(item.date), 'yyyy-MM-dd'),
  }));
}
```

```json
// ✅ Good: package.json with proper sideEffects configuration
{
  "name": "my-app",
  "version": "1.0.0",
  "main": "dist/index.js",
  "module": "dist/index.esm.js",
  "sideEffects": [
    "*.css",
    "*.scss",
    "./src/polyfills.ts"
  ]
}
```

```tsx
// ✅ Good: vite.config.ts - Optimize dependencies for tree shaking
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  build: {
    rollupOptions: {
      treeshake: {
        moduleSideEffects: 'no-external',
        propertyReadSideEffects: false,
        tryCatchDeoptimization: false,
      },
    },
  },
  optimizeDeps: {
    include: ['lodash-es'],
  },
});
```

**Benefits:**
- Named exports let the bundler eliminate unused functions at build time
- ESM-compatible libraries (`lodash-es`, `date-fns`) enable per-function tree shaking
- The `sideEffects` field tells the bundler which files are safe to remove when unused
- Aggressive treeshake options maximize dead code elimination
- Use `rollup-plugin-visualizer` to audit bundle contents and verify tree shaking effectiveness

Reference: [Vite Build Options - rollupOptions](https://vitejs.dev/config/build-options.html#build-rollupoptions) | [Rollup Tree Shaking](https://rollupjs.org/configuration-options/#treeshake)
