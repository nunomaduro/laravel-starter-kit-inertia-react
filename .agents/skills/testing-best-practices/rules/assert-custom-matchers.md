---
title: Custom Matchers
impact: HIGH
impactDescription: "assertion readability and reusability"
tags: assertions, custom-matchers, domain-specific
---

## Custom Matchers

**Impact: HIGH (assertion readability and reusability)**

Create custom assertion matchers for domain-specific validations to improve readability and reusability. Custom matchers are valuable when you have repeated assertion patterns, complex multi-property checks, or assertions that need better error messages.

## Incorrect

```typescript
// ❌ Bad: Repeated complex assertions without abstraction
describe('OrderService', () => {
  test('creates pending order', () => {
    const order = orderService.create({ items: [{ id: 1 }] });

    expect(order.status).toBe('pending');
    expect(order.paidAt).toBeNull();
    expect(order.shippedAt).toBeNull();
    expect(order.items.length).toBeGreaterThan(0);
  });

  test('creates another pending order', () => {
    const order = orderService.create({ items: [{ id: 2 }, { id: 3 }] });

    // Same assertions duplicated
    expect(order.status).toBe('pending');
    expect(order.paidAt).toBeNull();
    expect(order.shippedAt).toBeNull();
    expect(order.items.length).toBeGreaterThan(0);
  });
});

describe('DateUtils', () => {
  test('returns date within business hours', () => {
    const date = dateUtils.nextBusinessHour();

    const hours = date.getHours();
    expect(hours).toBeGreaterThanOrEqual(9);
    expect(hours).toBeLessThanOrEqual(17);
    expect(date.getDay()).not.toBe(0);
    expect(date.getDay()).not.toBe(6);
  });
});
```

**Problems:**
- Complex assertion patterns are duplicated across tests
- Error messages are generic and do not explain domain context
- Updating validation logic requires changes in multiple places
- Tests read like implementation details rather than domain language

## Correct

```typescript
// ✅ Good: Custom matchers encapsulate domain-specific assertions
expect.extend({
  toBePendingOrder(received: Order) {
    const isPending = received.status === 'pending';
    const isUnpaid = received.paidAt === null;
    const isUnshipped = received.shippedAt === null;
    const hasItems = received.items.length > 0;

    const pass = isPending && isUnpaid && isUnshipped && hasItems;

    const failures: string[] = [];
    if (!isPending) failures.push(`status is "${received.status}" (expected "pending")`);
    if (!isUnpaid) failures.push(`paidAt is ${received.paidAt} (expected null)`);
    if (!isUnshipped) failures.push(`shippedAt is ${received.shippedAt} (expected null)`);
    if (!hasItems) failures.push('order has no items');

    return {
      pass,
      message: () =>
        pass
          ? `Expected order not to be a valid pending order`
          : `Expected order to be a valid pending order, but: ${failures.join(', ')}`
    };
  },

  toBeWithinBusinessHours(received: Date) {
    const hours = received.getHours();
    const dayOfWeek = received.getDay();
    const isWeekday = dayOfWeek !== 0 && dayOfWeek !== 6;
    const isBusinessHours = hours >= 9 && hours <= 17;

    const pass = isWeekday && isBusinessHours;

    return {
      pass,
      message: () =>
        pass
          ? `Expected ${received} not to be within business hours`
          : `Expected ${received} to be within business hours (Mon-Fri 9AM-5PM), ` +
            `but was ${['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'][dayOfWeek]} at ${hours}:00`
    };
  },

  toHaveValidationError(received: ValidationResult, field: string, message?: string) {
    const error = received.errors.find(e => e.field === field);
    const hasError = !!error;
    const messageMatches = message ? error?.message.includes(message) : true;

    const pass = hasError && messageMatches;

    return {
      pass,
      message: () =>
        pass
          ? `Expected no validation error for field "${field}"`
          : hasError
            ? `Expected error message to include "${message}", but got "${error?.message}"`
            : `Expected validation error for field "${field}", but none found. Errors: ${JSON.stringify(received.errors)}`
    };
  }
});

// TypeScript declarations for custom matchers
declare global {
  namespace jest {
    interface Matchers<R> {
      toBePendingOrder(): R;
      toBeWithinBusinessHours(): R;
      toHaveValidationError(field: string, message?: string): R;
    }
  }
}

// Clean tests using custom matchers
describe('OrderService', () => {
  test('creates pending order', () => {
    const order = orderService.create({ items: [{ id: 1 }] });

    expect(order).toBePendingOrder();
  });
});

describe('DateUtils', () => {
  test('returns date within business hours', () => {
    const date = dateUtils.nextBusinessHour();

    expect(date).toBeWithinBusinessHours();
  });
});

describe('FormValidation', () => {
  test('validates required fields', () => {
    const result = validator.validate({ email: '', password: '' });

    expect(result).toHaveValidationError('email', 'required');
    expect(result).toHaveValidationError('password', 'required');
  });
});
```

**Benefits:**
- Tests read like specifications using domain language
- Complex validations are defined once and reused everywhere
- Custom error messages explain failures in business terms
- Validation logic is maintained in a single place
- Consistent validation rules applied uniformly across tests

Reference: [Jest Docs — Custom Matchers](https://jestjs.io/docs/expect#expectextendmatchers)
