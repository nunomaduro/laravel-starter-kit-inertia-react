---
title: Configure Build-Time Compression
impact: CRITICAL
impactDescription: "60-80% smaller asset size"
tags: build, compression, gzip, brotli, optimization
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
