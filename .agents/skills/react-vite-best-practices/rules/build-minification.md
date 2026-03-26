---
title: Configure Optimal Minification Settings
impact: CRITICAL
impactDescription: "30-50% smaller bundles"
tags: build, minification, optimization, compression, vite
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
