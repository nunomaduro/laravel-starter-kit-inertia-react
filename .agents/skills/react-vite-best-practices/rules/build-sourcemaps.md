---
title: Configure Source Maps for Production Debugging
impact: CRITICAL
impactDescription: "Better error tracking without exposing source"
tags: build, sourcemaps, debugging, production, vite
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
