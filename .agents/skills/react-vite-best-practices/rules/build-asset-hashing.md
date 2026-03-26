---
title: Configure Asset Hashing for Cache Busting
impact: CRITICAL
impactDescription: "Ensures latest version delivery"
tags: build, hashing, caching, assets, vite
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
