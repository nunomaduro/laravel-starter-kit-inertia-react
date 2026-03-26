---
title: Deterministic Tests
impact: CRITICAL
impactDescription: "CI/CD reliability and developer trust"
tags: test-isolation, deterministic, flaky-tests
---

## Deterministic Tests

**Impact: CRITICAL (CI/CD reliability and developer trust)**

Tests should produce the same result every time they run, regardless of environment or timing. Common sources of non-determinism include time, random values, external services, file systems, databases, and concurrency.

## Incorrect

```typescript
// ❌ Bad: Non-deterministic tests with random/time dependencies
describe('OrderService', () => {
  test('generates unique order id', () => {
    const order = createOrder({ items: [{ id: 1 }] });

    // Flaky: depends on random number generation
    expect(order.id).toMatch(/^ORD-\d{6}$/);
  });

  test('sets order date to today', () => {
    const order = createOrder({ items: [{ id: 1 }] });

    // Flaky: will fail at midnight or on different days
    expect(order.date.toDateString()).toBe(new Date().toDateString());
  });

  test('expires order after 24 hours', async () => {
    const order = createOrder({ items: [{ id: 1 }] });

    // Terrible: actually waits 24 hours!
    await new Promise(resolve => setTimeout(resolve, 86400000));

    expect(order.isExpired()).toBe(true);
  });

  test('handles concurrent orders', async () => {
    // Flaky: race condition may or may not manifest
    const orders = await Promise.all([
      createOrder({ items: [{ id: 1 }] }),
      createOrder({ items: [{ id: 2 }] })
    ]);

    expect(orders[0].id).not.toBe(orders[1].id);
  });
});
```

**Problems:**
- Random value assertions can fail unpredictably
- Time-based tests break at midnight or across time zones
- Real `setTimeout` delays make tests impossibly slow
- Race conditions produce intermittent failures

## Correct

```typescript
// ✅ Good: Deterministic tests with controlled dependencies
describe('OrderService', () => {
  let orderService: OrderService;
  let mockIdGenerator: jest.Mocked<IdGenerator>;
  let mockClock: jest.Mocked<Clock>;

  beforeEach(() => {
    mockIdGenerator = {
      generate: jest.fn()
    };
    mockClock = {
      now: jest.fn()
    };
    orderService = new OrderService(mockIdGenerator, mockClock);
  });

  test('generates order id using id generator', () => {
    mockIdGenerator.generate.mockReturnValue('ORD-123456');

    const order = orderService.createOrder({ items: [{ id: 1 }] });

    expect(order.id).toBe('ORD-123456');
  });

  test('sets order date from clock', () => {
    const fixedDate = new Date('2024-01-15T10:00:00Z');
    mockClock.now.mockReturnValue(fixedDate);
    mockIdGenerator.generate.mockReturnValue('ORD-123456');

    const order = orderService.createOrder({ items: [{ id: 1 }] });

    expect(order.date).toEqual(fixedDate);
  });

  test('expires order after 24 hours from creation', () => {
    const creationTime = new Date('2024-01-15T10:00:00Z');
    const afterExpiration = new Date('2024-01-16T10:00:01Z');

    mockClock.now.mockReturnValue(creationTime);
    mockIdGenerator.generate.mockReturnValue('ORD-123456');

    const order = orderService.createOrder({ items: [{ id: 1 }] });

    mockClock.now.mockReturnValue(afterExpiration);

    expect(order.isExpired(mockClock)).toBe(true);
  });

  test('order is not expired within 24 hours', () => {
    const creationTime = new Date('2024-01-15T10:00:00Z');
    const beforeExpiration = new Date('2024-01-16T09:59:59Z');

    mockClock.now.mockReturnValue(creationTime);
    mockIdGenerator.generate.mockReturnValue('ORD-123456');

    const order = orderService.createOrder({ items: [{ id: 1 }] });

    mockClock.now.mockReturnValue(beforeExpiration);

    expect(order.isExpired(mockClock)).toBe(false);
  });

  test('generates unique ids for concurrent orders', async () => {
    let callCount = 0;
    mockIdGenerator.generate.mockImplementation(() => `ORD-${++callCount}`);
    mockClock.now.mockReturnValue(new Date('2024-01-15T10:00:00Z'));

    const orders = await Promise.all([
      orderService.createOrder({ items: [{ id: 1 }] }),
      orderService.createOrder({ items: [{ id: 2 }] })
    ]);

    expect(orders[0].id).toBe('ORD-1');
    expect(orders[1].id).toBe('ORD-2');
  });
});
```

**Benefits:**
- Failed tests can be debugged consistently with reproducible results
- Developers trust the test suite because it is reliable
- No wasted time investigating false alarms
- Tests can safely run in parallel
- Results are the same locally and in CI

Reference: [Martin Fowler — Eradicating Non-Determinism in Tests](https://martinfowler.com/articles/nonDeterminism.html)
