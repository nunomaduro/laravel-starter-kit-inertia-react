# React + Vite Best Practices - Complete Reference

**Version:** 2.0.0
**Framework:** React + Vite
**Date:** March 2026
**License:** MIT

## Abstract

Performance optimization guide for React applications built with Vite. Contains 23 rules across 6 categories covering build optimization, code splitting, development performance, asset handling, environment configuration, and bundle analysis.

## References

- [Vite Documentation](https://vite.dev)
- [React Documentation](https://react.dev)
- [Rollup Documentation](https://rollupjs.org)

---

# Sections

This file defines all sections, their ordering, impact levels, and descriptions.
The section ID (in parentheses) is the filename prefix used to group rules.

---

## 1. Build Optimization (build)

**Impact:** CRITICAL
**Description:** Vite build configuration for production. Manual chunk splitting, minification (OXC default, Terser for max compression), modern browser targets, sourcemap configuration, tree shaking, gzip/Brotli compression, and content-based asset hashing.

## 2. Code Splitting (split)

**Impact:** CRITICAL
**Description:** Route-based and component-level code splitting with React.lazy() and Suspense. Dynamic imports for heavy libraries, strategic Suspense boundary placement, and prefetch hints for anticipated navigation.

## 3. Development (dev)

**Impact:** HIGH
**Description:** Development server performance. Dependency pre-bundling with optimizeDeps, React Fast Refresh patterns for reliable HMR, and server configuration for HMR overlay, Docker, and proxy setups.

## 4. Asset Handling (asset)

**Impact:** HIGH
**Description:** Static asset optimization. Image lazy loading and responsive formats, SVG-as-React-components with SVGR, self-hosted web fonts with preloading, and correct usage of the public directory vs JavaScript imports.

## 5. Environment Config (env)

**Impact:** MEDIUM
**Description:** Environment variable management. The VITE_ prefix for client-side exposure, mode-specific env files (.env.production, .env.staging), and protecting sensitive data from being embedded in the client bundle.

## 6. Bundle Analysis (bundle)

**Impact:** MEDIUM
**Description:** Bundle size analysis and monitoring. Using rollup-plugin-visualizer to identify large dependencies and optimization opportunities.


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


---

## Configure Optimal Minification Settings

**Impact: CRITICAL (30-50% smaller bundles)**

Configure optimal minification settings in Vite to reduce bundle size while maintaining debugging capabilities when needed.

## Incorrect

```tsx
// vite.config.ts - Disabled or suboptimal minification
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  build: {
    // Minification disabled
    minify: false,
  },
});
```

```tsx
// Or using terser without configuration
export default defineConfig({
  plugins: [react()],
  build: {
    minify: 'terser',
    // No terser options configured - uses defaults
  },
});
```

```tsx
// Code patterns that prevent effective minification
// constants.ts
export const CONFIG = {
  API_URL: 'https://api.example.com',
  TIMEOUT: 5000,
  RETRY_COUNT: 3,
};

// component.tsx - Property access prevents minification
function Component() {
  // These property names won't be minified
  return (
    <div>
      <span data-testid="user-name">{user.firstName}</span>
      <span data-testid="user-email">{user.emailAddress}</span>
    </div>
  );
}
```

**Problems:**
- Disabled minification ships bloated bundles to production
- Unconfigured terser uses suboptimal defaults and is slower than OXC
- String property access patterns prevent effective mangling
- Console and debugger statements leak into production

## Correct

```tsx
// vite.config.ts - Using OXC minification (Vite default)
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  build: {
    // OXC is the default minifier — fastest option, no config needed
    // minify: 'oxc',
    // Remove console and debugger in production
    esbuild: {
      drop: ['console', 'debugger'],
      legalComments: 'none',
    },
  },
});
```

```tsx
// vite.config.ts - Terser for maximum compression (slower builds)
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  build: {
    minify: 'terser',
    terserOptions: {
      compress: {
        drop_console: true,
        drop_debugger: true,
        inline: 2,
        dead_code: true,
        booleans_as_integers: true,
        passes: 2,
      },
      mangle: {
        properties: {
          // Only mangle properties starting with underscore
          regex: /^_/,
        },
      },
      format: {
        comments: false,
        ascii_only: true,
      },
    },
  },
});
```

```tsx
// Code patterns that support effective minification
// Use private class fields for better mangling
class UserService {
  #apiClient;
  #cache = new Map();

  constructor(apiClient: ApiClient) {
    this.#apiClient = apiClient;
  }

  async #fetchUser(id: string) {
    if (this.#cache.has(id)) {
      return this.#cache.get(id);
    }
    const user = await this.#apiClient.get(`/users/${id}`);
    this.#cache.set(id, user);
    return user;
  }

  getUser(id: string) {
    return this.#fetchUser(id);
  }
}
```

```tsx
// Environment-aware console removal
// logger.ts
const isDev = import.meta.env.DEV;

export const logger = {
  log: isDev ? console.log.bind(console) : () => {},
  warn: isDev ? console.warn.bind(console) : () => {},
  error: console.error.bind(console), // Keep errors in production
};

// Usage - logs are removed in production
import { logger } from './logger';

function processData(data: Data) {
  logger.log('Processing:', data);
  // ...
  return result;
}
```

**Benefits:**
- OXC (default) provides the fastest minification with excellent compression
- Terser produces 2-5% smaller bundles when every KB matters
- Removing console/debugger prevents information leakage in production
- Private class fields (`#`) enable better property mangling
- Environment-aware logging keeps errors visible while stripping debug logs

Reference: [Vite Build Options - minify](https://vitejs.dev/config/build-options.html#build-minify)


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


---

## Configure Source Maps for Production Debugging

**Impact: CRITICAL (Better error tracking without exposing source)**

Configure source maps appropriately for debugging in development and error tracking in production without exposing source code.

## Incorrect

```tsx
// vite.config.ts - Source maps disabled
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  build: {
    // ❌ Bad: Makes debugging production issues impossible
    sourcemap: false,
  },
});
```

```tsx
// ❌ Bad: Exposing full source maps in production
export default defineConfig({
  plugins: [react()],
  build: {
    sourcemap: true, // Creates .map files served publicly
  },
});
```

**Problems:**
- Disabled source maps make production debugging impossible
- Full source maps expose your original source code publicly
- No integration with error tracking services like Sentry
- Missing CSS source maps in development slows styling work

## Correct

```tsx
// vite.config.ts - Environment-appropriate source map configuration
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig(({ mode }) => ({
  plugins: [react()],
  build: {
    // ✅ Good: 'hidden' for production, full maps for staging
    sourcemap: mode === 'production' ? 'hidden' : true,
    rollupOptions: {
      output: {
        sourcemapExcludeSources: mode === 'production',
      },
    },
  },
  css: {
    devSourcemap: true,
  },
}));
```

```tsx
// vite.config.ts - Integration with Sentry plugin
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import { sentryVitePlugin } from '@sentry/vite-plugin';

export default defineConfig(({ mode }) => ({
  plugins: [
    react(),
    mode === 'production' && sentryVitePlugin({
      org: process.env.SENTRY_ORG,
      project: process.env.SENTRY_PROJECT,
      authToken: process.env.SENTRY_AUTH_TOKEN,
      release: {
        name: process.env.RELEASE_VERSION,
      },
      sourcemaps: {
        assets: './dist/**',
        filesToDeleteAfterUpload: './dist/**/*.map',
      },
    }),
  ].filter(Boolean),
  build: {
    sourcemap: true, // Required for Sentry plugin
  },
}));
```

```nginx
# nginx.conf - Block access to source maps
server {
    listen 80;
    root /var/www/app/dist;

    location ~* \.map$ {
        allow 10.0.0.0/8;
        allow 192.168.0.0/16;
        deny all;
    }
}
```

**Benefits:**
- Hidden source maps enable error tracking without exposing source code
- Sentry integration provides detailed production error reports with original file names
- CSS source maps in development speed up styling work
- Server-level blocking adds a second layer of source map protection

| Option | Description | Use Case |
|--------|-------------|----------|
| `false` | No source maps | Not recommended |
| `true` | Generates and links .map files | Development/Staging |
| `'inline'` | Embeds maps in bundles | Development only |
| `'hidden'` | Generates .map files without link | Production |

Reference: [Vite Build Options - sourcemap](https://vitejs.dev/config/build-options.html#build-sourcemap)


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


---

## Configure Build-Time Compression

**Impact: CRITICAL (60-80% smaller asset size)**

Configure build-time compression to serve pre-compressed assets, reducing server load and improving delivery speed.

## Incorrect

```tsx
// ❌ Bad: No compression configured
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  build: {
    // Relying only on server-side compression
    // which adds CPU overhead on every request
  },
});
```

```tsx
// ❌ Bad: Runtime compression adds latency
import express from 'express';
import compression from 'compression';

const app = express();

// Compresses every response on-the-fly
app.use(compression());
app.use(express.static('dist'));
```

**Problems:**
- Server-side runtime compression adds CPU overhead and latency to every request
- Lower compression levels used at runtime to keep latency acceptable
- No Brotli support in most runtime compression middleware
- Compression work repeated for every request instead of done once at build time

## Correct

```tsx
// ✅ Good: Pre-compress assets during build
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import viteCompression from 'vite-plugin-compression';

export default defineConfig({
  plugins: [
    react(),
    // Generate gzip compressed files
    viteCompression({
      algorithm: 'gzip',
      ext: '.gz',
      threshold: 1024, // Only compress files > 1KB
      deleteOriginFile: false,
    }),
    // Also generate Brotli compressed files for modern browsers
    viteCompression({
      algorithm: 'brotliCompress',
      ext: '.br',
      threshold: 1024,
    }),
  ],
  build: {
    cssMinify: true,
  },
});
```

```tsx
// ✅ Good: Advanced compression with maximum quality
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import viteCompression from 'vite-plugin-compression';
import { constants as zlibConstants } from 'zlib';

export default defineConfig({
  plugins: [
    react(),
    viteCompression({
      algorithm: 'gzip',
      ext: '.gz',
      threshold: 1024,
      compressionOptions: {
        level: 9, // Maximum compression
      },
      filter: /\.(js|css|html|json|svg|txt|xml|wasm)$/i,
    }),
    viteCompression({
      algorithm: 'brotliCompress',
      ext: '.br',
      threshold: 1024,
      compressionOptions: {
        params: {
          [zlibConstants.BROTLI_PARAM_QUALITY]: 11, // Maximum quality
        },
      },
      filter: /\.(js|css|html|json|svg|txt|xml|wasm)$/i,
    }),
  ],
});
```

```nginx
# nginx.conf - Serve pre-compressed files
server {
    listen 80;
    root /var/www/app/dist;

    gzip_static on;
    brotli_static on;

    location ~* \.(js|css|html|json|svg|txt|xml|wasm)$ {
        gzip_static on;
        brotli_static on;
        try_files $uri $uri/ =404;
        add_header Cache-Control "public, max-age=31536000, immutable";
        add_header Vary "Accept-Encoding";
    }
}
```

```tsx
// ✅ Good: Express server with pre-compressed file serving
import express from 'express';
import expressStaticGzip from 'express-static-gzip';

const app = express();

app.use('/', expressStaticGzip('dist', {
  enableBrotli: true,
  orderPreference: ['br', 'gzip'],
  serveStatic: {
    maxAge: '1y',
    immutable: true,
  },
}));

app.listen(3000);
```

**Benefits:**
- Pre-compressed files eliminate on-the-fly compression overhead
- Maximum compression levels achievable without impacting response latency
- Brotli offers 15-25% better compression than gzip for text-based content
- Faster Time to First Byte with no compression overhead per request
- Both gzip and Brotli versions provide maximum browser compatibility

| Format | Browser Support | Typical Ratio | Best For |
|--------|-----------------|---------------|----------|
| Gzip | 95%+ | 70-80% | Universal fallback |
| Brotli | 90%+ | 80-90% | Modern browsers |

Reference: [vite-plugin-compression](https://github.com/vbenjs/vite-plugin-compression)


---

## Configure Asset Hashing for Cache Busting

**Impact: CRITICAL (Ensures latest version delivery)**

Configure content-based asset hashing to enable aggressive caching while ensuring users always receive the latest version after deployments.

## Incorrect

```tsx
// ❌ Bad: No hash - files get cached indefinitely
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  build: {
    rollupOptions: {
      output: {
        entryFileNames: 'assets/[name].js',
        chunkFileNames: 'assets/[name].js',
        assetFileNames: 'assets/[name].[ext]',
      },
    },
  },
});
```

```tsx
// ❌ Bad: Version-based hashing - all files invalidated on any change
output: {
  entryFileNames: `assets/[name].${packageJson.version}.js`,
  chunkFileNames: `assets/[name].${packageJson.version}.js`,
  assetFileNames: `assets/[name].${packageJson.version}.[ext]`,
}
```

**Problems:**
- Without hashes, users see stale content after deployments
- Version-based hashes invalidate all files even when only one changed
- No way to set aggressive cache headers without risking stale content
- CDNs and browser caches serve outdated files

## Correct

```tsx
// ✅ Good: Content-based hashing with organized asset directories
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  build: {
    rollupOptions: {
      output: {
        entryFileNames: 'assets/js/[name]-[hash].js',
        chunkFileNames: 'assets/js/[name]-[hash].js',
        assetFileNames: (assetInfo) => {
          const info = assetInfo.name?.split('.') || [];
          const ext = info[info.length - 1];

          if (/png|jpe?g|gif|svg|webp|avif|ico/i.test(ext)) {
            return 'assets/images/[name]-[hash][extname]';
          }
          if (/woff2?|eot|ttf|otf/i.test(ext)) {
            return 'assets/fonts/[name]-[hash][extname]';
          }
          if (/css/i.test(ext)) {
            return 'assets/css/[name]-[hash][extname]';
          }
          return 'assets/[name]-[hash][extname]';
        },
      },
    },
  },
});
```

```tsx
// ✅ Good: Server caching configuration
import express from 'express';
import path from 'path';

const app = express();

// Immutable caching for hashed assets (1 year)
app.use('/assets', express.static(path.join(__dirname, 'dist/assets'), {
  maxAge: '1y',
  immutable: true,
}));

// Short cache for index.html (always check for updates)
app.use(express.static(path.join(__dirname, 'dist'), {
  maxAge: '5m',
  setHeaders: (res, path) => {
    if (path.endsWith('.html')) {
      res.setHeader('Cache-Control', 'no-cache, must-revalidate');
    }
  },
}));
```

```nginx
# nginx.conf - Optimal caching strategy
server {
    listen 80;
    root /var/www/app/dist;

    location ~* \.html$ {
        add_header Cache-Control "no-cache, must-revalidate";
        add_header Vary "Accept-Encoding";
        try_files $uri /index.html;
    }

    location /assets/ {
        add_header Cache-Control "public, max-age=31536000, immutable";
        add_header Vary "Accept-Encoding";
        try_files $uri =404;
    }

    location = /sw.js {
        add_header Cache-Control "no-cache, must-revalidate";
        try_files $uri =404;
    }
}
```

**Benefits:**
- Content hashes create new URLs when files change, bypassing cached versions automatically
- Hashed files can be cached indefinitely with the `immutable` directive
- Users receive new code immediately after deployment without clearing cache
- Unchanged files remain cached while only updated files are downloaded
- Works seamlessly with CDNs and edge caching strategies

| Cache-Control | Target |
|--------------|--------|
| `public, max-age=31536000, immutable` | Hashed assets |
| `no-cache, must-revalidate` | HTML files, service workers |

Reference: [Vite Build Options - rollupOptions](https://vitejs.dev/config/build-options.html#build-rollupoptions)


---

## Use React.lazy() for Route-Based Splitting

**Impact: CRITICAL (50-80% smaller initial bundle)**

Loading all route components upfront delays initial page load. Users download code for pages they may never visit. Route-based code splitting ensures users only download code for the current route.

## Incorrect

```typescript
// ❌ Bad: All imports are eager - loaded immediately
import { BrowserRouter, Routes, Route } from 'react-router-dom'
import Home from './pages/Home'
import Dashboard from './pages/Dashboard'
import Settings from './pages/Settings'
import Profile from './pages/Profile'
import Admin from './pages/Admin'

function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<Home />} />
        <Route path="/dashboard" element={<Dashboard />} />
        <Route path="/settings" element={<Settings />} />
        <Route path="/profile" element={<Profile />} />
        <Route path="/admin" element={<Admin />} />
      </Routes>
    </BrowserRouter>
  )
}
```

**Problems:**
- All 5 page components are bundled together and loaded on initial page load
- Users download code for pages they may never visit
- Larger initial bundle means slower Time to Interactive
- No benefit from caching individual route chunks

## Correct

```typescript
// ✅ Good: Lazy load route components
import { lazy, Suspense } from 'react'
import { BrowserRouter, Routes, Route } from 'react-router-dom'

const Home = lazy(() => import('./pages/Home'))
const Dashboard = lazy(() => import('./pages/Dashboard'))
const Settings = lazy(() => import('./pages/Settings'))
const Profile = lazy(() => import('./pages/Profile'))
const Admin = lazy(() => import('./pages/Admin'))

function PageLoader() {
  return (
    <div className="flex items-center justify-center min-h-screen">
      <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600" />
    </div>
  )
}

function App() {
  return (
    <BrowserRouter>
      <Suspense fallback={<PageLoader />}>
        <Routes>
          <Route path="/" element={<Home />} />
          <Route path="/dashboard" element={<Dashboard />} />
          <Route path="/settings" element={<Settings />} />
          <Route path="/profile" element={<Profile />} />
          <Route path="/admin" element={<Admin />} />
        </Routes>
      </Suspense>
    </BrowserRouter>
  )
}
```

```typescript
// ✅ Good: Preload on hover for instant navigation
const Dashboard = lazy(() => import('./pages/Dashboard'))

function NavLink() {
  const preloadDashboard = () => {
    import('./pages/Dashboard')
  }

  return (
    <Link
      to="/dashboard"
      onMouseEnter={preloadDashboard}
      onFocus={preloadDashboard}
    >
      Dashboard
    </Link>
  )
}
```

**Benefits:**
- Initial bundle reduced by 50-80% since only the current route is loaded
- Time to Interactive significantly improved
- Each route loads only when navigated to
- Vite automatically names chunks based on file path — no magic comments needed
- Preloading on hover makes navigation feel instant

Reference: [React lazy](https://react.dev/reference/react/lazy) | [Vite Code Splitting](https://vitejs.dev/guide/build.html#chunking-strategy)


---

## Strategic Suspense Boundaries for Lazy Loading

**Impact: CRITICAL (Progressive loading, better UX)**

Without proper Suspense boundaries, a single lazy component can block the entire UI. Strategic placement of Suspense boundaries allows parts of the UI to load independently.

## Incorrect

```typescript
// ❌ Bad: Single Suspense at root - entire app shows loading state
function App() {
  return (
    <Suspense fallback={<FullPageLoader />}>
      <Header />
      <Sidebar />
      <MainContent />
      <Footer />
    </Suspense>
  )
}
```

**Problems:**
- If any lazy component is loading, the entire app shows the loading state
- No progressive rendering — users see nothing until everything loads
- Poor perceived performance even on fast connections
- No granular control over loading fallbacks per section

## Correct

```typescript
// ✅ Good: Strategic Suspense boundaries per section
function App() {
  return (
    <div className="app-layout">
      {/* Header loads immediately - not lazy */}
      <Header />

      <div className="main-layout">
        {/* Sidebar has its own boundary */}
        <Suspense fallback={<SidebarSkeleton />}>
          <Sidebar />
        </Suspense>

        {/* Main content independent */}
        <Suspense fallback={<ContentSkeleton />}>
          <MainContent />
        </Suspense>
      </div>

      {/* Footer loads immediately */}
      <Footer />
    </div>
  )
}
```

```typescript
// ✅ Good: Nested Suspense for complex UIs
function Dashboard() {
  return (
    <div className="dashboard">
      <h1>Dashboard</h1>

      <div className="dashboard-grid">
        <Suspense fallback={<WidgetSkeleton />}>
          <StatsWidget />
        </Suspense>

        <Suspense fallback={<WidgetSkeleton />}>
          <ChartWidget />
        </Suspense>

        <Suspense fallback={<WidgetSkeleton />}>
          <RecentActivityWidget />
        </Suspense>
      </div>
    </div>
  )
}
```

```typescript
// ✅ Good: Error Boundaries with Suspense
import { ErrorBoundary } from 'react-error-boundary'

function App() {
  return (
    <ErrorBoundary fallback={<ErrorFallback />}>
      <Suspense fallback={<PageLoader />}>
        <Routes>
          <Route path="/" element={<Home />} />
          <Route path="/dashboard" element={<Dashboard />} />
        </Routes>
      </Suspense>
    </ErrorBoundary>
  )
}

function ErrorFallback({ error, resetErrorBoundary }) {
  return (
    <div className="error-container">
      <h2>Something went wrong</h2>
      <pre>{error.message}</pre>
      <button onClick={resetErrorBoundary}>Try again</button>
    </div>
  )
}
```

```typescript
// ✅ Good: Skeleton components match actual content layout
function ContentSkeleton() {
  return (
    <div className="animate-pulse">
      <div className="h-8 bg-gray-200 rounded w-1/4 mb-4" />
      <div className="h-4 bg-gray-200 rounded w-full mb-2" />
      <div className="h-4 bg-gray-200 rounded w-full mb-2" />
      <div className="h-4 bg-gray-200 rounded w-3/4" />
    </div>
  )
}
```

**Benefits:**
- Parts of UI render independently without blocking each other
- Better perceived performance with skeleton loading states
- Graceful degradation on slow networks
- Error boundaries catch loading failures per section, not globally

Reference: [React Suspense](https://react.dev/reference/react/Suspense) | [react-error-boundary](https://github.com/bvaughn/react-error-boundary)


---

## Use Dynamic Imports for Heavy Components

**Impact: CRITICAL (30-50% reduction in initial bundle)**

Heavy components like charts, editors, and complex forms should not be loaded until needed. Dynamic imports allow loading code on-demand, reducing initial bundle size.

## Incorrect

```typescript
// ❌ Bad: All heavy libraries loaded upfront
import { Chart } from 'chart.js'
import ReactQuill from 'react-quill'
import { PDFViewer } from '@react-pdf/renderer'
import MonacoEditor from '@monaco-editor/react'

function Dashboard() {
  const [showChart, setShowChart] = useState(false)

  return (
    <div>
      <button onClick={() => setShowChart(true)}>Show Chart</button>
      {showChart && <Chart data={data} />}
    </div>
  )
}
```

**Problems:**
- Chart.js, React Quill, PDF renderer, and Monaco are all loaded even if never used
- Initial bundle bloated with hundreds of KBs of library code
- Slower Time to Interactive for all users regardless of feature usage
- Heavy parsing blocks the main thread on mobile devices

## Correct

```typescript
// ✅ Good: Lazy load heavy components
import { lazy, Suspense, useState } from 'react'

const Chart = lazy(() => import('./components/Chart'))
const Editor = lazy(() => import('./components/Editor'))
const PDFViewer = lazy(() => import('./components/PDFViewer'))

function Dashboard() {
  const [showChart, setShowChart] = useState(false)
  const [showEditor, setShowEditor] = useState(false)

  return (
    <div>
      <button onClick={() => setShowChart(true)}>Show Chart</button>
      <button onClick={() => setShowEditor(true)}>Show Editor</button>

      {showChart && (
        <Suspense fallback={<ChartSkeleton />}>
          <Chart data={data} />
        </Suspense>
      )}

      {showEditor && (
        <Suspense fallback={<EditorSkeleton />}>
          <Editor />
        </Suspense>
      )}
    </div>
  )
}
```

```typescript
// ✅ Good: Conditional dynamic import for libraries
async function exportToPDF() {
  const { PDFDocument } = await import('pdf-lib')
  const pdfDoc = await PDFDocument.create()
  // ... generate PDF
}

function ExportButton() {
  const [loading, setLoading] = useState(false)

  const handleExport = async () => {
    setLoading(true)
    await exportToPDF()
    setLoading(false)
  }

  return (
    <button onClick={handleExport} disabled={loading}>
      {loading ? 'Generating...' : 'Export PDF'}
    </button>
  )
}
```

```typescript
// ✅ Good: Preload on interaction intent
const HeavyModal = lazy(() => import('./HeavyModal'))

function ModalTrigger() {
  const [isOpen, setIsOpen] = useState(false)

  const preload = () => {
    import('./HeavyModal')
  }

  return (
    <>
      <button
        onClick={() => setIsOpen(true)}
        onMouseEnter={preload}
        onFocus={preload}
      >
        Open Settings
      </button>

      {isOpen && (
        <Suspense fallback={<ModalSkeleton />}>
          <HeavyModal onClose={() => setIsOpen(false)} />
        </Suspense>
      )}
    </>
  )
}
```

```typescript
// ✅ Good: Feature flag based loading
function App({ user }) {
  const AdminPanel = user.isAdmin
    ? lazy(() => import('./AdminPanel'))
    : null

  return (
    <div>
      <MainContent />
      {AdminPanel && (
        <Suspense fallback={<Loading />}>
          <AdminPanel />
        </Suspense>
      )}
    </div>
  )
}
```

**Benefits:**
- Initial bundle can be 50%+ smaller by deferring heavy libraries
- Faster Time to Interactive since only critical code is parsed upfront
- Better user experience on slow connections and mobile devices
- Preloading on hover makes subsequent loads feel instant
- Feature-flag loading avoids shipping admin code to regular users

Libraries that should typically be dynamically imported:
- Chart libraries (Chart.js, Recharts, D3)
- Rich text editors (React Quill, TipTap, Slate)
- Code editors (Monaco, CodeMirror)
- PDF libraries (react-pdf, pdf-lib)
- Date pickers with locales
- Map libraries (Mapbox, Google Maps)
- Markdown renderers

Reference: [Vite Dynamic Import](https://vitejs.dev/guide/features.html#dynamic-import) | [React lazy](https://react.dev/reference/react/lazy)


---

## Lazy Load Non-Critical Components

**Impact: CRITICAL (20-40% smaller initial bundle)**

Use React.lazy for component-level code splitting to load non-critical UI components on demand.

## Incorrect

```tsx
// ❌ Bad: All components imported eagerly
import { useState } from 'react';
import Header from './components/Header';
import Sidebar from './components/Sidebar';
import MainContent from './components/MainContent';
import SettingsPanel from './components/SettingsPanel';
import NotificationCenter from './components/NotificationCenter';
import UserProfileModal from './components/UserProfileModal';
import HelpDrawer from './components/HelpDrawer';
import FeedbackForm from './components/FeedbackForm';
import AdvancedFilters from './components/AdvancedFilters';
import ExportDialog from './components/ExportDialog';

function Dashboard() {
  const [showSettings, setShowSettings] = useState(false);
  const [showProfile, setShowProfile] = useState(false);

  return (
    <div>
      <Header />
      <Sidebar />
      <MainContent />
      {showSettings && <SettingsPanel />}
      {showProfile && <UserProfileModal />}
    </div>
  );
}
// All modals, drawers, and dialogs loaded even if never opened
```

**Problems:**
- All modal, drawer, and dialog code is downloaded on initial page load
- Users pay the cost of parsing code they may never use
- Larger initial bundle slows Time to Interactive
- Heavy components block the main thread during parsing on mobile

## Correct

```tsx
// ✅ Good: Component-level lazy loading
import { lazy, Suspense, useState } from 'react';
import Header from './components/Header';
import Sidebar from './components/Sidebar';
import MainContent from './components/MainContent';
import { Skeleton } from './components/ui/Skeleton';

const SettingsPanel = lazy(() => import('./components/SettingsPanel'));
const NotificationCenter = lazy(() => import('./components/NotificationCenter'));
const UserProfileModal = lazy(() => import('./components/UserProfileModal'));
const HelpDrawer = lazy(() => import('./components/HelpDrawer'));
const FeedbackForm = lazy(() => import('./components/FeedbackForm'));
const AdvancedFilters = lazy(() => import('./components/AdvancedFilters'));
const ExportDialog = lazy(() => import('./components/ExportDialog'));

function LazyModal({
  isOpen,
  children
}: {
  isOpen: boolean;
  children: React.ReactNode
}) {
  if (!isOpen) return null;

  return (
    <Suspense fallback={<Skeleton className="modal-skeleton" />}>
      {children}
    </Suspense>
  );
}

function Dashboard() {
  const [showSettings, setShowSettings] = useState(false);
  const [showProfile, setShowProfile] = useState(false);

  return (
    <div>
      <Header
        onSettingsClick={() => setShowSettings(true)}
        onProfileClick={() => setShowProfile(true)}
      />
      <Sidebar />
      <MainContent />

      <LazyModal isOpen={showSettings}>
        <SettingsPanel onClose={() => setShowSettings(false)} />
      </LazyModal>

      <LazyModal isOpen={showProfile}>
        <UserProfileModal onClose={() => setShowProfile(false)} />
      </LazyModal>
    </div>
  );
}
```

```tsx
// ✅ Good: Lazy component with preloading
import { lazy, ComponentType, LazyExoticComponent } from 'react';

interface PreloadableComponent<T extends ComponentType<any>>
  extends LazyExoticComponent<T> {
  preload: () => Promise<{ default: T }>;
}

export function lazyWithPreload<T extends ComponentType<any>>(
  factory: () => Promise<{ default: T }>
): PreloadableComponent<T> {
  const Component = lazy(factory) as PreloadableComponent<T>;
  Component.preload = factory;
  return Component;
}

const SettingsPanel = lazyWithPreload(() => import('./components/SettingsPanel'));

function SettingsButton({ onClick }: { onClick: () => void }) {
  return (
    <button
      onClick={onClick}
      onMouseEnter={() => SettingsPanel.preload()}
      onFocus={() => SettingsPanel.preload()}
    >
      Settings
    </button>
  );
}
```

```tsx
// ✅ Good: Lazy loading below-the-fold content with Intersection Observer
import { lazy, Suspense } from 'react';
import { useInView } from 'react-intersection-observer';

const RelatedProducts = lazy(() => import('./components/RelatedProducts'));
const CustomerReviews = lazy(() => import('./components/CustomerReviews'));

function ProductPage({ productId }: { productId: string }) {
  const { ref: reviewsRef, inView: reviewsInView } = useInView({
    triggerOnce: true,
    rootMargin: '200px',
  });

  return (
    <div>
      <ProductHeader productId={productId} />
      <ProductGallery productId={productId} />

      <section ref={reviewsRef}>
        {reviewsInView && (
          <Suspense fallback={<ReviewsSkeleton />}>
            <CustomerReviews productId={productId} />
          </Suspense>
        )}
      </section>
    </div>
  );
}
```

**Benefits:**
- Modals, drawers, and dialogs only load when actually opened
- Faster First Contentful Paint since critical UI renders immediately
- Below-the-fold content loads as users scroll, not on initial page load
- Preloading on hover eliminates perceived delay when opening components
- Better memory usage since components only occupy memory when rendered

| Component Type | Lazy Load? | Reason |
|---------------|------------|--------|
| Modals/Dialogs | Yes | Only shown on interaction |
| Drawers/Panels | Yes | Hidden by default |
| Below-fold content | Yes | Not in initial viewport |
| Tabs (non-default) | Yes | Hidden until selected |
| Header/Navigation | No | Always visible |
| Above-fold content | No | Critical for FCP |

Reference: [React lazy](https://react.dev/reference/react/lazy) | [react-intersection-observer](https://github.com/thebuilder/react-intersection-observer)


---

## Prefetch Code Chunks on User Intent

**Impact: CRITICAL (Instant navigation perceived speed)**

Use prefetch and preload hints to load code chunks before they are needed, improving perceived navigation speed.

## Incorrect

```tsx
// ❌ Bad: No prefetching - chunks load only when navigation occurs
import { lazy, Suspense } from 'react';
import { Routes, Route, Link } from 'react-router-dom';

const Dashboard = lazy(() => import('./pages/Dashboard'));
const Analytics = lazy(() => import('./pages/Analytics'));
const Settings = lazy(() => import('./pages/Settings'));

function App() {
  return (
    <>
      <nav>
        <Link to="/">Dashboard</Link>
        <Link to="/analytics">Analytics</Link>
        <Link to="/settings">Settings</Link>
      </nav>

      <Suspense fallback={<Loading />}>
        <Routes>
          <Route path="/" element={<Dashboard />} />
          <Route path="/analytics" element={<Analytics />} />
          <Route path="/settings" element={<Settings />} />
        </Routes>
      </Suspense>
    </>
  );
}
// User clicks link -> waits for chunk download -> sees loading -> page renders
```

**Problems:**
- Users see loading spinners on every navigation
- Chunks only start downloading after the user clicks
- No anticipation of user intent leads to perceived slowness
- Wasted idle time that could be used for preloading

## Correct

```tsx
// ✅ Good: Prefetch on hover/focus for instant-feeling navigation
import { lazy, Suspense, useCallback } from 'react';
import { Routes, Route, Link, LinkProps } from 'react-router-dom';

function lazyWithPreload<T extends React.ComponentType<any>>(
  factory: () => Promise<{ default: T }>
) {
  const Component = lazy(factory);
  (Component as any).preload = factory;
  return Component as typeof Component & { preload: typeof factory };
}

const Dashboard = lazyWithPreload(() => import('./pages/Dashboard'));
const Analytics = lazyWithPreload(() => import('./pages/Analytics'));
const Settings = lazyWithPreload(() => import('./pages/Settings'));

interface PrefetchLinkProps extends LinkProps {
  preload?: () => Promise<any>;
}

function PrefetchLink({ preload, onMouseEnter, onFocus, ...props }: PrefetchLinkProps) {
  const handlePreload = useCallback(() => {
    preload?.();
  }, [preload]);

  return (
    <Link
      {...props}
      onMouseEnter={(e) => {
        handlePreload();
        onMouseEnter?.(e);
      }}
      onFocus={(e) => {
        handlePreload();
        onFocus?.(e);
      }}
    />
  );
}

function App() {
  return (
    <>
      <nav>
        <PrefetchLink to="/" preload={Dashboard.preload}>Dashboard</PrefetchLink>
        <PrefetchLink to="/analytics" preload={Analytics.preload}>Analytics</PrefetchLink>
        <PrefetchLink to="/settings" preload={Settings.preload}>Settings</PrefetchLink>
      </nav>

      <Suspense fallback={<Loading />}>
        <Routes>
          <Route path="/" element={<Dashboard />} />
          <Route path="/analytics" element={<Analytics />} />
          <Route path="/settings" element={<Settings />} />
        </Routes>
      </Suspense>
    </>
  );
}
// User hovers link -> chunk downloads -> user clicks -> instant navigation
```

```tsx
// ✅ Good: Prefetch based on viewport visibility
import { useEffect, useRef } from 'react';

interface PrefetchOnVisibleProps {
  children: React.ReactNode;
  preload: () => Promise<any>;
  rootMargin?: string;
}

export function PrefetchOnVisible({
  children,
  preload,
  rootMargin = '200px',
}: PrefetchOnVisibleProps) {
  const ref = useRef<HTMLDivElement>(null);
  const prefetched = useRef(false);

  useEffect(() => {
    if (!ref.current || prefetched.current) return;

    const observer = new IntersectionObserver(
      (entries) => {
        if (entries[0].isIntersecting && !prefetched.current) {
          prefetched.current = true;
          preload();
          observer.disconnect();
        }
      },
      { rootMargin }
    );

    observer.observe(ref.current);
    return () => observer.disconnect();
  }, [preload, rootMargin]);

  return <div ref={ref}>{children}</div>;
}
```

```tsx
// ✅ Good: Prefetch after idle time
import { useEffect, useRef } from 'react';

export function usePrefetchAfterIdle(
  preloadFns: Array<() => Promise<any>>,
  delay: number = 2000
) {
  const prefetched = useRef(false);

  useEffect(() => {
    if (prefetched.current) return;

    const prefetch = () => {
      if (prefetched.current) return;
      prefetched.current = true;

      preloadFns.forEach((fn) => {
        if ('requestIdleCallback' in window) {
          requestIdleCallback(() => fn(), { timeout: 5000 });
        } else {
          setTimeout(fn, 100);
        }
      });
    };

    const timeoutId = setTimeout(prefetch, delay);
    return () => clearTimeout(timeoutId);
  }, [preloadFns, delay]);
}

// Usage
function App() {
  usePrefetchAfterIdle([Analytics.preload, Settings.preload], 2000);
  return (/* ... */);
}
```

**Benefits:**
- Code loads while users decide, making clicks feel instantaneous
- Eliminates loading spinners for common navigation paths
- Prefetching during idle time does not compete with critical resources
- Maintains code splitting benefits with smarter preloading
- Users on slow connections benefit the most from preloading

| Strategy | Trigger | Best For |
|----------|---------|----------|
| Hover/Focus | User intent signal | Navigation links |
| Viewport Entry | Scroll position | Below-fold sections |
| Idle Time | After initial load | Common routes |
| `modulepreload` | Page load | Critical vendors |

Reference: [Vite modulePreload](https://vitejs.dev/config/build-options.html#build-modulepreload) | [React lazy](https://react.dev/reference/react/lazy)


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


---

## Structure Components for Fast Refresh

**Impact: HIGH (Instant updates without losing state)**

Structure components to take full advantage of React Fast Refresh for instant updates during development.

## Incorrect

```tsx
// ❌ Bad: Named exports can break Fast Refresh in some cases
export const App = () => {
  return <div>App</div>;
};

// Multiple component exports in one file
export const Header = () => <header>Header</header>;
export const Footer = () => <footer>Footer</footer>;
export const Sidebar = () => <aside>Sidebar</aside>;
```

```tsx
// ❌ Bad: Module-level side effects break Fast Refresh
import { fetchUser } from './api';

const initialUser = await fetchUser('current');

export default function UserProfile() {
  const [user] = useState(initialUser);
  return <div>{user.name}</div>;
}
```

```tsx
// ❌ Bad: Mixing components with non-component exports
export default function Counter() {
  const [count, setCount] = useState(0);
  return (
    <button onClick={() => setCount(c => c + 1)}>
      Count: {count}
    </button>
  );
}

export const MAX_COUNT = 100;
export const formatCount = (n: number) => n.toLocaleString();
```

```tsx
// ❌ Bad: Anonymous component - Fast Refresh can't identify it
export default () => {
  return <div>Anonymous</div>;
};
```

**Problems:**
- Multiple components per file may cause full page reloads instead of hot updates
- Module-level side effects re-execute on every edit, breaking state
- Non-component exports in component files trigger full module replacement
- Anonymous components cannot be tracked by Fast Refresh

## Correct

```tsx
// ✅ Good: Default export for main component, one per file
export default function App() {
  return (
    <div>
      <Header />
      <main>
        <Outlet />
      </main>
      <Footer />
    </div>
  );
}
```

```tsx
// ✅ Good: Separate file for constants
// constants/counter.ts
export const MAX_COUNT = 100;
export const MIN_COUNT = 0;

// utils/format.ts
export function formatCount(n: number): string {
  return n.toLocaleString();
}

// components/Counter.tsx - Pure component file
import { useState } from 'react';
import { MAX_COUNT, MIN_COUNT } from '../constants/counter';
import { formatCount } from '../utils/format';

export default function Counter() {
  const [count, setCount] = useState(0);

  return (
    <div className="counter">
      <button onClick={() => setCount(c => Math.max(c - 1, MIN_COUNT))}>-</button>
      <span>{formatCount(count)}</span>
      <button onClick={() => setCount(c => Math.min(c + 1, MAX_COUNT))}>+</button>
    </div>
  );
}
```

```tsx
// ✅ Good: Proper data fetching with hooks instead of module-level side effects
import { useQuery } from '@tanstack/react-query';
import { fetchUser } from '../api/users';

export default function UserProfile() {
  const { data: user, isLoading, error } = useQuery({
    queryKey: ['user', 'current'],
    queryFn: () => fetchUser('current'),
  });

  if (isLoading) return <Skeleton />;
  if (error) return <ErrorMessage error={error} />;

  return (
    <div className="user-profile">
      <Avatar src={user.avatar} alt={user.name} />
      <h1>{user.name}</h1>
      <p>{user.email}</p>
    </div>
  );
}
```

```tsx
// ✅ Good: Set displayName on HOCs for Fast Refresh and DevTools
export function withAuth<P extends object>(
  WrappedComponent: ComponentType<P>
) {
  function WithAuth(props: P) {
    const { user, isLoading } = useAuth();
    if (isLoading) return <LoadingSpinner />;
    if (!user) return <Navigate to="/login" />;
    return <WrappedComponent {...props} />;
  }

  WithAuth.displayName = `WithAuth(${
    WrappedComponent.displayName || WrappedComponent.name || 'Component'
  })`;

  return WithAuth;
}
```

**Benefits:**
- State preserved across edits — no losing form inputs or scroll position
- Changes reflect in ~50ms, enabling rapid UI iteration
- Error recovery restores previous state without full reload
- Only changed components re-render, keeping the rest of the app intact

| Pattern | Fast Refresh | Notes |
|---------|--------------|-------|
| Default export function | Works | Recommended |
| Named export function | Usually works | Name must be PascalCase |
| Anonymous function | Fails | Always name components |
| Multiple components/file | May break | One component per file |
| Non-component exports | May break | Separate into utility files |

Reference: [React Fast Refresh](https://react.dev/learn/editor-setup#your-editor) | [@vitejs/plugin-react](https://github.com/vitejs/vite-plugin-react/tree/main/packages/plugin-react)


---

## Configure HMR for Optimal Development

**Impact: HIGH (Fast, reliable hot updates)**

Configure Vite's Hot Module Replacement (HMR) for optimal development experience with fast, reliable updates.

## Incorrect

```tsx
// ❌ Bad: No HMR configuration
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  // HMR works with defaults but may have issues in certain environments
});
```

```tsx
// ❌ Bad: Module-level mutable state breaks HMR
let userCache = {};

export function UserProvider({ children }) {
  const [user, setUser] = useState(() => {
    return userCache.current || null;
  });

  useEffect(() => {
    userCache.current = user;
  }, [user]);

  return (
    <UserContext.Provider value={{ user, setUser }}>
      {children}
    </UserContext.Provider>
  );
}
// HMR causes state loss and unexpected behavior
```

**Problems:**
- Default HMR config fails in Docker, WSL, and network drive environments
- Module-level mutable state persists across HMR updates, causing bugs
- Missing watch configuration leads to undetected file changes
- No error overlay makes debugging harder during development

## Correct

```tsx
// ✅ Good: Properly configured HMR
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [
    react({
      fastRefresh: true,
    }),
  ],
  server: {
    hmr: {
      overlay: true,
      protocol: 'ws',
    },
    watch: {
      usePolling: process.env.USE_POLLING === 'true',
      ignored: ['**/node_modules/**', '**/dist/**'],
    },
  },
});
```

```tsx
// ✅ Good: Docker/WSL optimized HMR
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  server: {
    host: '0.0.0.0',
    hmr: {
      host: 'localhost',
      clientPort: 5173,
    },
    watch: {
      usePolling: true,
      interval: 1000,
    },
  },
});
```

```tsx
// ✅ Good: HMR-compatible state management with Zustand
import { create } from 'zustand';

interface UserState {
  user: User | null;
  setUser: (user: User | null) => void;
}

export const useUserStore = create<UserState>((set) => ({
  user: null,
  setUser: (user) => set({ user }),
}));
// HMR will preserve store state automatically
```

```tsx
// ✅ Good: Custom HMR handling for special cases
import axios from 'axios';

export const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  timeout: 10000,
});

if (import.meta.hot) {
  import.meta.hot.dispose(() => {
    apiClient.interceptors.request.clear();
    apiClient.interceptors.response.clear();
  });
}

apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});
```

```tsx
// ✅ Good: HMR-compatible context with explicit accept
import { createContext, useContext, useState, useCallback } from 'react';

const ThemeContext = createContext<ThemeContextValue | null>(null);

export function ThemeProvider({ children }: { children: React.ReactNode }) {
  const [theme, setTheme] = useState<'light' | 'dark'>('light');

  const toggleTheme = useCallback(() => {
    setTheme((prev) => (prev === 'light' ? 'dark' : 'light'));
  }, []);

  return (
    <ThemeContext.Provider value={{ theme, toggleTheme }}>
      {children}
    </ThemeContext.Provider>
  );
}

if (import.meta.hot) {
  import.meta.hot.accept();
}
```

**Benefits:**
- Changes reflect in the browser in milliseconds, not seconds
- React Fast Refresh maintains component state during updates
- Clear error overlays help quickly identify and fix issues
- Docker/WSL configuration ensures HMR works in containerized environments
- Custom `import.meta.hot` handling prevents memory leaks during long sessions

| Issue | Cause | Solution |
|-------|-------|----------|
| Full page reload | Export not a component | Check default exports |
| State lost | Module-level state | Use state management library |
| Changes not detected | File system events | Enable polling |
| Connection errors | Port/protocol mismatch | Configure hmr.clientPort |
| Slow updates | Large dep chain | Optimize with optimizeDeps |

Reference: [Vite HMR API](https://vitejs.dev/guide/api-hmr.html) | [Vite Server Options](https://vitejs.dev/config/server-options.html)


---

## Optimize Image Loading and Format

**Impact: HIGH (40-70% reduction in image payload)**

Unoptimized images are often the largest assets, significantly impacting page load time. Proper image handling reduces bandwidth and improves Core Web Vitals.

## Incorrect

```typescript
// ❌ Bad: Large images loaded eagerly with no optimization
function Gallery() {
  return (
    <div>
      <img src="/images/hero.png" />
      <img src="/images/feature1.png" />
      <img src="/images/feature2.png" />
      <img src="/images/feature3.png" />
    </div>
  )
}
```

**Problems:**
- No lazy loading — all images downloaded immediately
- No responsive images — oversized images on small screens
- No explicit dimensions — causes Cumulative Layout Shift (CLS)
- Unoptimized PNG format — WebP/AVIF are significantly smaller
- Missing alt attributes — accessibility violation

## Correct

```typescript
// ✅ Good: Optimized image loading
function Gallery() {
  return (
    <div>
      {/* Critical above-fold image */}
      <img
        src="/images/hero.webp"
        alt="Hero banner"
        width={1200}
        height={600}
        fetchPriority="high"
      />

      {/* Below-fold images - lazy load */}
      <img
        src="/images/feature1.webp"
        alt="Feature 1"
        width={400}
        height={300}
        loading="lazy"
        decoding="async"
      />
      <img
        src="/images/feature2.webp"
        alt="Feature 2"
        width={400}
        height={300}
        loading="lazy"
        decoding="async"
      />
    </div>
  )
}
```

```typescript
// ✅ Good: Responsive images with format fallback
function ResponsiveImage() {
  return (
    <picture>
      <source
        srcSet="/images/hero-480.webp 480w,
                /images/hero-768.webp 768w,
                /images/hero-1200.webp 1200w"
        type="image/webp"
        sizes="(max-width: 480px) 480px,
               (max-width: 768px) 768px,
               1200px"
      />
      <img
        src="/images/hero-1200.jpg"
        alt="Hero image"
        width={1200}
        height={600}
        loading="lazy"
      />
    </picture>
  )
}
```

```typescript
// ✅ Good: Vite image optimization plugin
// vite.config.ts
import { defineConfig } from 'vite'
import { ViteImageOptimizer } from 'vite-plugin-image-optimizer'

export default defineConfig({
  plugins: [
    ViteImageOptimizer({
      png: { quality: 80 },
      jpeg: { quality: 80 },
      webp: { lossless: true },
    }),
  ],
})
```

```typescript
// ✅ Good: Reusable Image component
interface ImageProps {
  src: string
  alt: string
  width: number
  height: number
  priority?: boolean
  className?: string
}

export function Image({
  src,
  alt,
  width,
  height,
  priority = false,
  className,
}: ImageProps) {
  return (
    <img
      src={src}
      alt={alt}
      width={width}
      height={height}
      loading={priority ? 'eager' : 'lazy'}
      decoding={priority ? 'sync' : 'async'}
      fetchPriority={priority ? 'high' : 'auto'}
      className={className}
    />
  )
}
```

```typescript
// ✅ Good: Inline small images and use URL imports for backgrounds
// vite.config.ts
export default defineConfig({
  build: {
    assetsInlineLimit: 4096, // Inline images < 4KB as base64
  },
})

// For CSS background images
import heroImage from './images/hero.webp?url'

function Hero() {
  return (
    <div
      className="hero"
      style={{ backgroundImage: `url(${heroImage})` }}
    />
  )
}
```

**Benefits:**
- 40-70% reduction in image payload with modern formats (WebP, AVIF)
- Better LCP (Largest Contentful Paint) with priority loading for hero images
- Reduced CLS (Cumulative Layout Shift) by specifying explicit dimensions
- Lazy loading defers off-screen images, speeding up initial page load
- Automatic inlining of small images eliminates extra HTTP requests

Reference: [Vite Static Asset Handling](https://vitejs.dev/guide/assets.html) | [web.dev Image Optimization](https://web.dev/fast/#optimize-your-images)


---

## Use SVGs as React Components

**Impact: HIGH (Better styling and integration)**

SVGs can be used as images or as React components. Using them as components enables styling with CSS, dynamic colors, and better integration with React.

## Incorrect

```typescript
// ❌ Bad: Using SVG as image - limited styling options
function Logo() {
  return <img src="/logo.svg" alt="Logo" className="w-8 h-8" />
}

// ❌ Bad: Inline SVG everywhere - duplicated code
function Icon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
      <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
    </svg>
  )
}
```

**Problems:**
- SVGs as `<img>` tags cannot be styled with CSS (no color changes, no hover effects)
- Inline SVGs are duplicated across components, bloating the bundle
- No tree shaking — unused icons still included in the build
- Cannot leverage `currentColor` for dynamic theming

## Correct

```bash
npm install vite-plugin-svgr -D
```

```typescript
// ✅ Good: vite.config.ts - Configure SVGR plugin
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import svgr from 'vite-plugin-svgr'

export default defineConfig({
  plugins: [
    react(),
    svgr({
      exportAsDefault: false,
      svgrOptions: {
        plugins: ['@svgr/plugin-svgo', '@svgr/plugin-jsx'],
        svgoConfig: {
          plugins: [
            {
              name: 'removeViewBox',
              active: false, // Keep viewBox for scaling
            },
          ],
        },
      },
    }),
  ],
})
```

```typescript
// ✅ Good: Import as React component for full styling control
import Logo from './assets/logo.svg?react'
import logoUrl from './assets/logo.svg'

function Header() {
  return (
    <header>
      {/* As component - fully styleable */}
      <Logo className="w-8 h-8 text-blue-600 hover:text-blue-700" />

      {/* As image when styling isn't needed */}
      <img src={logoUrl} alt="Logo" className="w-8 h-8" />
    </header>
  )
}
```

```typescript
// ✅ Good: TypeScript support
// src/vite-env.d.ts
/// <reference types="vite/client" />
/// <reference types="vite-plugin-svgr/client" />

declare module '*.svg?react' {
  import type { FunctionComponent, SVGProps } from 'react'
  const content: FunctionComponent<SVGProps<SVGSVGElement>>
  export default content
}

declare module '*.svg' {
  const content: string
  export default content
}
```

```typescript
// ✅ Good: Dynamic SVG colors via currentColor
import SearchIcon from './assets/search.svg?react'

function SearchButton({ active }: { active: boolean }) {
  return (
    <button className={active ? 'text-blue-600' : 'text-gray-400'}>
      <SearchIcon className="w-5 h-5" />
      Search
    </button>
  )
}
```

```typescript
// ✅ Good: Icon component pattern with tree shaking
import type { SVGProps, FunctionComponent } from 'react'

import HomeIcon from '@/assets/icons/home.svg?react'
import SettingsIcon from '@/assets/icons/settings.svg?react'
import UserIcon from '@/assets/icons/user.svg?react'

const icons = {
  home: HomeIcon,
  settings: SettingsIcon,
  user: UserIcon,
} as const

type IconName = keyof typeof icons

interface IconProps extends SVGProps<SVGSVGElement> {
  name: IconName
  size?: number
}

export function Icon({ name, size = 24, className, ...props }: IconProps) {
  const IconComponent = icons[name]
  return (
    <IconComponent
      width={size}
      height={size}
      className={className}
      {...props}
    />
  )
}

// Usage
// <Icon name="home" size={20} className="text-gray-600" />
```

**Benefits:**
- SVGs fully styleable with Tailwind CSS or any CSS framework
- Dynamic colors via `currentColor` without maintaining multiple SVG files
- Better tree shaking — unused icons excluded from the build
- SVGO optimization strips unnecessary metadata, reducing file size
- TypeScript support provides autocompletion for icon names

Reference: [vite-plugin-svgr](https://github.com/pd4d10/vite-plugin-svgr) | [SVGR](https://react-svgr.com/)


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


---

## Use VITE_ Prefix for Environment Variables

**Impact: MEDIUM (Security and proper configuration)**

Vite only exposes environment variables prefixed with `VITE_` to client-side code. This prevents accidental exposure of sensitive server-side variables.

## Incorrect

```env
# ❌ Bad: .env
API_KEY=secret123
DATABASE_URL=postgres://...
APP_TITLE=My App
```

```typescript
// ❌ Bad: Variables not exposed - returns undefined
const apiKey = import.meta.env.API_KEY // undefined
const title = import.meta.env.APP_TITLE // undefined
```

```env
# ❌ Bad: Sensitive data with VITE_ prefix (exposed to browser!)
VITE_DATABASE_URL=postgres://...
VITE_API_SECRET=secret123
VITE_PRIVATE_KEY=...
```

**Problems:**
- Variables without `VITE_` prefix are not available in client code
- Sensitive data with `VITE_` prefix is embedded in the bundle and visible to anyone
- No type safety leads to runtime errors from undefined variables
- No separation between client-safe and server-only configuration

## Correct

```env
# ✅ Good: .env
# Client-side variables (exposed to browser)
VITE_API_URL=https://api.example.com
VITE_APP_TITLE=My App
VITE_ENABLE_ANALYTICS=true

# Server-side only (NOT exposed to browser)
DATABASE_URL=postgres://...
API_SECRET=secret123
```

```typescript
// ✅ Good: Access client-side variables
const apiUrl = import.meta.env.VITE_API_URL
const appTitle = import.meta.env.VITE_APP_TITLE
const enableAnalytics = import.meta.env.VITE_ENABLE_ANALYTICS === 'true'

// Built-in variables
const isDev = import.meta.env.DEV
const isProd = import.meta.env.PROD
const mode = import.meta.env.MODE
const baseUrl = import.meta.env.BASE_URL
```

```typescript
// ✅ Good: Type-safe environment variables
// src/vite-env.d.ts
/// <reference types="vite/client" />

interface ImportMetaEnv {
  readonly VITE_API_URL: string
  readonly VITE_APP_TITLE: string
  readonly VITE_ENABLE_ANALYTICS: string
}

interface ImportMeta {
  readonly env: ImportMetaEnv
}
```

```env
# ✅ Good: Environment-specific files
# .env.development
VITE_API_URL=http://localhost:8000/api

# .env.production
VITE_API_URL=https://api.example.com

# .env.staging
VITE_API_URL=https://staging-api.example.com
```

```typescript
// ✅ Good: Runtime configuration for values that change without rebuild
// public/config.js
window.APP_CONFIG = {
  apiUrl: 'https://api.example.com',
}

// src/config.ts
export const config = {
  apiUrl: window.APP_CONFIG?.apiUrl || import.meta.env.VITE_API_URL,
}
```

**Benefits:**
- Prevents accidental exposure of secrets like database URLs and API keys
- Clear separation between client-safe and server-only configuration
- TypeScript declarations catch undefined variable access at compile time
- Environment-specific files allow different configs per deployment target
- Runtime configuration enables config changes without rebuilding

Reference: [Vite Env Variables](https://vitejs.dev/guide/env-and-mode.html)


---

## Mode-Specific Environment Files

**Impact: MEDIUM (Wrong env config leaks secrets or uses wrong API URLs)**

Vite supports multiple environment files that load based on the current mode. Using a single `.env` file for all environments leads to hardcoded values, manual toggling, and accidental misconfigurations.

## Incorrect

```typescript
// ❌ Bad — hardcoded API URLs toggled by comments
const API_URL = 'https://api.example.com'
// const API_URL = 'http://localhost:8000'     // uncomment for dev
// const API_URL = 'https://staging.example.com' // uncomment for staging
```

```env
# ❌ Bad — single .env with everything
# .env
VITE_API_URL=https://api.example.com
VITE_SENTRY_DSN=https://abc@sentry.io/123
VITE_FEATURE_DEBUG=true
# Must manually change values before deploying to production!
```

**Problems:**
- Manual editing is error-prone — wrong URL can reach production
- No separation between development, staging, and production configs
- Debug flags accidentally left on in production
- Team members override each other's local settings

## Correct

```env
# .env — shared defaults loaded in ALL modes
VITE_APP_NAME=MyApp

# .env.local — local overrides, gitignored (personal settings)
VITE_ENABLE_DEVTOOLS=true

# .env.development — loaded when mode is "development" (vite dev)
VITE_API_URL=http://localhost:8000/api
VITE_FEATURE_DEBUG=true

# .env.production — loaded when mode is "production" (vite build)
VITE_API_URL=https://api.example.com
VITE_FEATURE_DEBUG=false
VITE_SENTRY_DSN=https://abc@sentry.io/123

# .env.staging — loaded with: vite build --mode staging
VITE_API_URL=https://staging-api.example.com
VITE_FEATURE_DEBUG=true
VITE_SENTRY_DSN=https://abc@sentry.io/456
```

```bash
# ✅ Good — use --mode to target specific environment files
npx vite dev                    # loads .env + .env.development
npx vite build                  # loads .env + .env.production
npx vite build --mode staging   # loads .env + .env.staging
```

```
# Priority order (higher overrides lower):
# 1. .env.[mode].local   (e.g., .env.production.local — gitignored)
# 2. .env.[mode]         (e.g., .env.production)
# 3. .env.local          (gitignored)
# 4. .env                (shared defaults)
```

```typescript
// ✅ Good — type-safe config using the loaded environment
// src/config.ts
export const config = {
  appName: import.meta.env.VITE_APP_NAME,
  apiUrl: import.meta.env.VITE_API_URL,
  isDebug: import.meta.env.VITE_FEATURE_DEBUG === 'true',
  sentryDsn: import.meta.env.VITE_SENTRY_DSN ?? null,
  mode: import.meta.env.MODE, // "development" | "production" | "staging"
} as const
```

```gitignore
# .gitignore — always ignore local overrides
*.local
```

**Benefits:**
- Zero manual editing when switching environments
- `.local` files let each developer override without affecting the team
- `--mode` flag makes CI/CD pipelines explicit and auditable
- Priority order provides clear, predictable override behavior
- Debug flags and DSNs are scoped to the correct environment

Reference: [Vite Env Variables and Modes](https://vite.dev/guide/env-and-mode.html)


---

## Never Expose Secrets in Client Code

**Impact: MEDIUM (VITE_ variables are embedded in the client bundle — visible to anyone)**

Any environment variable with the `VITE_` prefix is statically replaced in the client bundle at build time. This means the raw value is embedded in JavaScript files served to the browser, where anyone can read it.

## Incorrect

```env
# ❌ Bad — secrets with VITE_ prefix are EXPOSED in the browser bundle
VITE_DATABASE_URL=postgres://user:password@db.example.com:5432/mydb
VITE_API_SECRET=sk_live_abc123def456
VITE_STRIPE_SECRET_KEY=sk_live_789xyz
VITE_JWT_SIGNING_KEY=super-secret-key-123
VITE_AWS_SECRET_ACCESS_KEY=wJalrXUtnFEMI/K7MDENG
```

```typescript
// ❌ Bad — calling external APIs directly with secrets from client
const response = await fetch('https://api.stripe.com/v1/charges', {
  headers: {
    Authorization: `Bearer ${import.meta.env.VITE_STRIPE_SECRET_KEY}`,
  },
})

// ❌ Bad — database connection string in client code
const db = connect(import.meta.env.VITE_DATABASE_URL)
```

**Problems:**
- Secret keys are visible in the built JavaScript files (open DevTools > Sources)
- Anyone can extract API keys and make unauthorized requests
- Database credentials in the client enable direct database access
- Secrets end up in version control, CDN caches, and browser caches
- A single leaked key can compromise your entire infrastructure

## Correct

```env
# .env
# ✅ SAFE — VITE_ prefix only for truly public values
VITE_API_URL=https://api.example.com
VITE_APP_NAME=MyApp
VITE_STRIPE_PUBLISHABLE_KEY=pk_live_abc123
VITE_SENTRY_DSN=https://abc@sentry.io/123

# ✅ SAFE — no VITE_ prefix means NOT exposed to the browser
DB_PASSWORD=super-secret-password
STRIPE_SECRET_KEY=sk_live_abc123def456
JWT_SIGNING_KEY=super-secret-key-123
AWS_SECRET_ACCESS_KEY=wJalrXUtnFEMI/K7MDENG
API_INTERNAL_TOKEN=tok_internal_xyz
```

```typescript
// ✅ Good — call your own backend, which holds the secret keys
// src/api/payments.ts
export async function createCharge(amount: number) {
  const response = await fetch(`${import.meta.env.VITE_API_URL}/payments/charge`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ amount }),
  })
  return response.json()
}

// The backend proxy holds the Stripe secret key server-side
// and forwards the request to Stripe — the secret never reaches the browser
```

```typescript
// ✅ Good — validate that no secrets leak through at build time
// src/config.ts
if (import.meta.env.DEV) {
  const envKeys = Object.keys(import.meta.env)
  const suspicious = envKeys.filter(
    (key) =>
      key.startsWith('VITE_') &&
      /secret|password|private|token/i.test(key)
  )

  if (suspicious.length > 0) {
    console.warn(
      `Potentially sensitive VITE_ variables detected: ${suspicious.join(', ')}`
    )
  }
}
```

**Benefits:**
- Secrets stay on the server, never reaching the browser
- Backend proxy pattern keeps API keys safe while still calling third-party services
- Only publishable/public keys use the `VITE_` prefix
- Dev-time warning catches accidental secret exposure early
- Clear naming convention makes security audits straightforward

Reference: [Vite Env Variables](https://vite.dev/guide/env-and-mode.html#env-files)


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


---

