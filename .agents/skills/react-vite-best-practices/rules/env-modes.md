---
title: Mode-Specific Environment Files
impact: MEDIUM
impactDescription: "Wrong env config leaks secrets or uses wrong API URLs"
tags: environment, modes, env-files, configuration
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
