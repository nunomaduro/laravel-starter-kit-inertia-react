---
title: Don't Over-Mock
impact: MEDIUM
impactDescription: "prevents 50% of false-green tests caused by testing implementation details"
tags: mocking, over-mocking, brittle-tests
---

## Don't Over-Mock

**Impact: MEDIUM (prevents 50% of false-green tests caused by testing implementation details)**

Only mock things that are slow, unpredictable, or external. Use real implementations for everything else so refactoring internals doesn't break tests.

## Incorrect

```typescript
// ❌ Bad: mocking every dependency, including simple utilities
import { OrderProcessor } from './order-processor';

vi.mock('./tax-calculator', () => ({
  calculateTax: vi.fn().mockReturnValue(8.5),
}));

vi.mock('./discount-engine', () => ({
  applyDiscount: vi.fn().mockReturnValue(85),
}));

vi.mock('./validators', () => ({
  validateOrder: vi.fn().mockReturnValue(true),
}));

vi.mock('./formatters', () => ({
  formatReceipt: vi.fn().mockReturnValue('Receipt: $93.50'),
}));

describe('OrderProcessor', () => {
  test('processes a valid order', () => {
    const processor = new OrderProcessor();
    const result = processor.process({ items: [{ price: 100 }], discountCode: 'SAVE15' });

    // These tests pass even if the real logic is completely broken
    expect(result.tax).toBe(8.5);
    expect(result.total).toBe(93.5);
    expect(result.receipt).toBe('Receipt: $93.50');
  });

  // After refactoring: merge tax-calculator into discount-engine
  // Result: test breaks because vi.mock('./tax-calculator') fails
  // The behavior is identical, but the test is coupled to file structure
});
```

**Problems:**
- Tests verify mock return values, not real behavior — they always pass regardless of bugs
- Refactoring internal modules (renaming, merging, splitting files) breaks tests
- Every mock is a point where the test diverges from production behavior
- Maintenance burden grows with each unnecessary mock

## Correct

```typescript
// ✅ Good: use real implementations, only mock the external payment API
import { OrderProcessor } from './order-processor';
import { createMockPaymentClient } from '../test-helpers/mock-payment';

describe('OrderProcessor', () => {
  // Only the external payment gateway is mocked
  const mockPaymentClient = createMockPaymentClient();
  const processor = new OrderProcessor(mockPaymentClient);

  test('calculates correct total with tax and discount', () => {
    const result = processor.process({
      items: [{ name: 'Widget', price: 100 }],
      discountCode: 'SAVE15',
      taxRate: 0.085,
    });

    // Real tax calculator, real discount engine, real formatter
    expect(result.subtotal).toBe(100);
    expect(result.discount).toBe(15);
    expect(result.tax).toBe(7.23); // tax on discounted price
    expect(result.total).toBe(92.23);
    expect(result.receipt).toContain('$92.23');
  });

  test('charges payment gateway with final amount', async () => {
    mockPaymentClient.charge.mockResolvedValue({ id: 'txn_1', status: 'paid' });

    const result = await processor.processAndPay({
      items: [{ name: 'Widget', price: 100 }],
      discountCode: 'SAVE15',
      taxRate: 0.085,
    });

    // Only the boundary interaction is verified via mock
    expect(mockPaymentClient.charge).toHaveBeenCalledWith(92.23);
    expect(result.transactionId).toBe('txn_1');
  });

  // After refactoring: merge tax-calculator into discount-engine
  // Result: test still passes because it tests behavior, not file structure
});
```

**Benefits:**
- Real business logic executes, so bugs are caught immediately
- Refactoring internals (renaming files, extracting functions) never breaks tests
- Only one mock to maintain instead of four
- Tests act as a safety net during refactoring, not a barrier

Reference: [Testing Implementation Details](https://kentcdodds.com/blog/testing-implementation-details)
