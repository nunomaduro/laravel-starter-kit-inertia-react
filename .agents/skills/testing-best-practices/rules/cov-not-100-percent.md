---
title: 100% Coverage Isn't the Goal
impact: MEDIUM
impactDescription: "saves 20% of test development time by avoiding diminishing-return tests"
tags: coverage, strategy, diminishing-returns
---

## 100% Coverage Isn't the Goal

**Impact: MEDIUM (saves 20% of test development time by avoiding diminishing-return tests)**

Aim for around 80% coverage as a baseline. Use coverage reports to find untested risk areas, not as a number to maximize at all costs.

## Incorrect

```typescript
// ❌ Bad: writing meaningless tests to hit 100% coverage
// --- src/logger.ts ---
export class Logger {
  private static instance: Logger;

  private constructor() {}

  static getInstance(): Logger {
    if (!Logger.instance) {
      Logger.instance = new Logger();
    }
    return Logger.instance;
  }

  debug(msg: string): void { console.debug(msg); }
  info(msg: string): void { console.info(msg); }
  warn(msg: string): void { console.warn(msg); }
  error(msg: string): void { console.error(msg); }
}

// --- tests written just to reach 100% ---
describe('Logger', () => {
  test('getInstance returns a Logger', () => {
    expect(Logger.getInstance()).toBeInstanceOf(Logger);
  });

  test('getInstance returns same instance', () => {
    expect(Logger.getInstance()).toBe(Logger.getInstance());
  });

  test('debug calls console.debug', () => {
    const spy = vi.spyOn(console, 'debug').mockImplementation(() => {});
    Logger.getInstance().debug('test');
    expect(spy).toHaveBeenCalledWith('test');
  });

  test('info calls console.info', () => {
    const spy = vi.spyOn(console, 'info').mockImplementation(() => {});
    Logger.getInstance().info('test');
    expect(spy).toHaveBeenCalledWith('test');
  });

  // ...repeated for warn and error — four tests that verify console wrappers
});
```

**Problems:**
- Tests verify that `console.info` is called when you call `info()` — trivially obvious
- Time spent writing and maintaining these tests could go toward testing business logic
- 100% coverage creates pressure to test every line, even generated code and framework glue
- Diminishing returns — the last 20% of coverage takes 80% of the effort

## Correct

```typescript
// ✅ Good: set reasonable thresholds and focus coverage on critical paths
// --- vitest.config.ts ---
import { defineConfig } from 'vitest/config';

export default defineConfig({
  test: {
    coverage: {
      provider: 'v8',
      reporter: ['text', 'html', 'lcov'],
      thresholds: {
        statements: 80,
        branches: 80,
        functions: 80,
        lines: 80,
      },
      exclude: [
        'node_modules/',
        'test/',
        '**/*.d.ts',
        '**/*.config.ts',
        '**/types/**',
        '**/generated/**',
      ],
    },
  },
});

// --- Focus tests on business-critical code ---
describe('InvoiceCalculator', () => {
  // This is worth testing — complex business rules with many branches
  test('applies early payment discount within 10 days', () => {
    const invoice = createInvoice({ amount: 1000, issuedAt: daysAgo(5) });
    expect(calculateTotal(invoice)).toBe(980); // 2% early discount
  });

  test('applies no discount after 10 days', () => {
    const invoice = createInvoice({ amount: 1000, issuedAt: daysAgo(15) });
    expect(calculateTotal(invoice)).toBe(1000);
  });

  test('adds late fee after 30 days', () => {
    const invoice = createInvoice({ amount: 1000, issuedAt: daysAgo(45) });
    expect(calculateTotal(invoice)).toBe(1050); // 5% late fee
  });

  test('caps late fee at maximum penalty', () => {
    const invoice = createInvoice({ amount: 1000, issuedAt: daysAgo(365) });
    expect(calculateTotal(invoice)).toBe(1200); // max 20% penalty
  });
});

// Skip testing: Logger wrapper, type definitions, config constants, generated code
```

**Benefits:**
- 80% threshold catches missing tests for important code without busywork
- Excluding generated code, types, and config prevents false coverage pressure
- Coverage reports become useful — uncovered lines point to genuinely untested risk areas
- Team velocity improves because effort targets high-value tests

Reference: [Test Coverage - Martin Fowler](https://martinfowler.com/bliki/TestCoverage.html)
