---
title: Independent Tests
impact: CRITICAL
impactDescription: "test reliability and parallel execution"
tags: test-isolation, independence, parallel
---

## Independent Tests

**Impact: CRITICAL (test reliability and parallel execution)**

Each test should be completely independent and not rely on other tests. Signs of dependent tests include tests that pass individually but fail together, tests that fail in different orders, and tests that only pass when the full suite runs.

## Incorrect

```typescript
// ❌ Bad: Tests depend on each other and share state
describe('ShoppingCart', () => {
  const cart = new ShoppingCart(); // Shared instance!

  test('adds first item to cart', () => {
    cart.addItem({ id: 1, name: 'Apple', price: 1.00 });
    expect(cart.getItemCount()).toBe(1);
  });

  test('adds second item to cart', () => {
    // Depends on previous test having added an item
    cart.addItem({ id: 2, name: 'Banana', price: 0.50 });
    expect(cart.getItemCount()).toBe(2);
  });

  test('calculates total', () => {
    // Depends on both previous tests
    expect(cart.getTotal()).toBe(1.50);
  });

  test('removes item', () => {
    // Depends on all previous tests
    cart.removeItem(1);
    expect(cart.getItemCount()).toBe(1);
    expect(cart.getTotal()).toBe(0.50);
  });
});
```

**Problems:**
- A shared mutable instance couples all tests together
- Tests cannot be run individually or in a different order
- A failure in one test cascades to all subsequent tests
- Parallelization is impossible because tests share state

## Correct

```typescript
// ✅ Good: Each test is independent with its own setup
describe('ShoppingCart', () => {
  let cart: ShoppingCart;

  beforeEach(() => {
    cart = new ShoppingCart(); // Fresh instance for each test
  });

  test('adds item to empty cart', () => {
    cart.addItem({ id: 1, name: 'Apple', price: 1.00 });

    expect(cart.getItemCount()).toBe(1);
  });

  test('adds multiple items to cart', () => {
    cart.addItem({ id: 1, name: 'Apple', price: 1.00 });
    cart.addItem({ id: 2, name: 'Banana', price: 0.50 });

    expect(cart.getItemCount()).toBe(2);
  });

  test('calculates total for multiple items', () => {
    cart.addItem({ id: 1, name: 'Apple', price: 1.00 });
    cart.addItem({ id: 2, name: 'Banana', price: 0.50 });

    expect(cart.getTotal()).toBe(1.50);
  });

  test('removes item from cart with multiple items', () => {
    cart.addItem({ id: 1, name: 'Apple', price: 1.00 });
    cart.addItem({ id: 2, name: 'Banana', price: 0.50 });

    cart.removeItem(1);

    expect(cart.getItemCount()).toBe(1);
    expect(cart.getTotal()).toBe(0.50);
  });

  test('returns zero total for empty cart', () => {
    expect(cart.getTotal()).toBe(0);
  });
});
```

**Benefits:**
- Tests can be run in any order, shuffled, parallelized, or run individually
- A failure in one test does not cascade to others
- Failed tests can be reproduced in isolation for easier debugging
- Independent tests can run concurrently for faster execution
- Changes to one test do not affect others

Reference: [Jest Docs — Test Isolation](https://jestjs.io/docs/setup-teardown#general-advice)
