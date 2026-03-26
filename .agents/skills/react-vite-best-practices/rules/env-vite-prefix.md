---
title: Use VITE_ Prefix for Environment Variables
impact: MEDIUM
impactDescription: "Security and proper configuration"
tags: env, configuration, security, vite, environment-variables
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
