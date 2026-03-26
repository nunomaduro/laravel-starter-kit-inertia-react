---
title: Never Expose Secrets in Client Code
impact: MEDIUM
impactDescription: "VITE_ variables are embedded in the client bundle — visible to anyone"
tags: security, environment, secrets, client-side
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
