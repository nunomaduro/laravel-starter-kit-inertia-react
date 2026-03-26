---
title: Organize Tests for Fast Feedback
impact: LOW
impactDescription: "reduces developer wait time by running only relevant tests during development"
tags: performance, organization, ci, watch-mode
---

## Organize Tests for Fast Feedback

**Impact: LOW (reduces developer wait time by running only relevant tests during development)**

Organize tests by speed tier (unit/integration/e2e). Run fast tests in watch mode during development and the full suite in CI.

## Incorrect

```typescript
// ❌ Bad: all tests in one flat directory, no way to run subsets
// tests/
//   user.test.ts          (unit — 10ms)
//   order.test.ts         (unit — 15ms)
//   checkout.test.ts      (integration — 2s, hits DB)
//   payment.test.ts       (integration — 3s, hits API)
//   full-purchase.test.ts (e2e — 15s, launches browser)
//   signup-flow.test.ts   (e2e — 12s, launches browser)

// vitest.config.ts
import { defineConfig } from 'vitest/config';

export default defineConfig({
  test: {
    include: ['tests/**/*.test.ts'],
    // Every file change runs ALL tests — unit, integration, and e2e
    // Developer waits 30+ seconds for feedback on a one-line change
  },
});
```

**Problems:**
- Changing a utility function triggers 30+ seconds of e2e tests
- No way to run just unit tests during development
- Watch mode becomes useless when every save triggers slow integration tests
- Developers stop running tests locally because the feedback loop is too slow

## Correct

```typescript
// ✅ Good: organized by speed tier with separate configs
// src/
//   services/
//     user-service.ts
//     user-service.unit.test.ts       (fast — pure logic)
//     user-service.integration.test.ts (medium — hits DB)
// e2e/
//   signup.e2e.test.ts                (slow — browser)

// vitest.config.ts — default runs only unit tests (fast feedback)
import { defineConfig } from 'vitest/config';

export default defineConfig({
  test: {
    include: ['src/**/*.unit.test.ts'],
    // Watch mode only runs affected unit tests — feedback in < 2 seconds
  },
});

// vitest.config.integration.ts — for integration tests
export default defineConfig({
  test: {
    include: ['src/**/*.integration.test.ts'],
    pool: 'forks', // Isolation for database tests
    testTimeout: 10000,
    hookTimeout: 15000,
  },
});

// vitest.config.e2e.ts — for end-to-end tests
export default defineConfig({
  test: {
    include: ['e2e/**/*.e2e.test.ts'],
    testTimeout: 30000,
    hookTimeout: 30000,
  },
});

// package.json scripts for each tier
// {
//   "scripts": {
//     "test": "vitest",
//     "test:unit": "vitest --config vitest.config.ts",
//     "test:integration": "vitest --config vitest.config.integration.ts",
//     "test:e2e": "vitest --config vitest.config.e2e.ts",
//     "test:all": "vitest --config vitest.config.ts && vitest run --config vitest.config.integration.ts && vitest run --config vitest.config.e2e.ts",
//     "test:ci": "vitest run --config vitest.config.ts --coverage && vitest run --config vitest.config.integration.ts && vitest run --config vitest.config.e2e.ts"
//   }
// }

// --- Use Vitest's built-in filtering for quick targeted runs ---

// Run tests related to changed files only (watch mode default)
// $ vitest

// Run tests matching a pattern
// $ vitest user-service

// Run a specific test file
// $ vitest src/services/user-service.unit.test.ts

// --- CI pipeline runs tiers in order: fast failures first ---
// .github/workflows/test.yml
// jobs:
//   unit:        runs test:unit      (~10s)
//   integration: runs test:integration (~60s, needs unit to pass)
//   e2e:         runs test:e2e       (~5min, needs integration to pass)
```

**Benefits:**
- Watch mode runs only fast unit tests, giving sub-second feedback during development
- `vitest user-service` instantly filters to relevant tests without configuration
- CI runs tiers in order — if unit tests fail, slow integration/e2e tests are skipped
- Developers can run the right level of testing for their current task

Reference: [Vitest - Filtering Tests](https://vitest.dev/guide/filtering)
