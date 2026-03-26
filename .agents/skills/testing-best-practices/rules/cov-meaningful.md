---
title: Focus on Meaningful Coverage
impact: MEDIUM
impactDescription: "eliminates wasted test effort on trivial code, focuses on high-risk logic"
tags: coverage, meaningful, quality
---

## Focus on Meaningful Coverage

**Impact: MEDIUM (eliminates wasted test effort on trivial code, focuses on high-risk logic)**

Write tests that verify business logic, decision branches, and error handling. Use coverage as a guide to find untested areas, not as a score to maximize.

## Incorrect

```typescript
// ❌ Bad: testing getters/setters and framework boilerplate to inflate coverage
interface UserDTO {
  id: string;
  name: string;
  email: string;
}

class User {
  constructor(
    public id: string,
    public name: string,
    public email: string,
  ) {}

  getId(): string { return this.id; }
  getName(): string { return this.name; }
  getEmail(): string { return this.email; }
  setName(name: string): void { this.name = name; }
  setEmail(email: string): void { this.email = email; }
}

describe('User', () => {
  // These tests add coverage but verify nothing meaningful
  test('getId returns id', () => {
    const user = new User('1', 'Alice', 'alice@example.com');
    expect(user.getId()).toBe('1');
  });

  test('getName returns name', () => {
    const user = new User('1', 'Alice', 'alice@example.com');
    expect(user.getName()).toBe('Alice');
  });

  test('setName sets name', () => {
    const user = new User('1', 'Alice', 'alice@example.com');
    user.setName('Bob');
    expect(user.getName()).toBe('Bob');
  });

  test('setEmail sets email', () => {
    const user = new User('1', 'Alice', 'alice@example.com');
    user.setEmail('bob@example.com');
    expect(user.getEmail()).toBe('bob@example.com');
  });
});
```

**Problems:**
- Testing trivial getters/setters wastes time and clutters the test suite
- Coverage percentage increases but confidence in the system does not
- Critical business logic (pricing, permissions, validation) remains untested
- Creates a false sense of security — 90% coverage with zero meaningful tests

## Correct

```typescript
// ✅ Good: test the logic that actually matters
describe('PricingEngine', () => {
  test('applies tiered discount for bulk orders', () => {
    const engine = new PricingEngine();

    // Business rule: 10+ items get 10% off, 50+ get 20% off
    expect(engine.calculateTotal(9, 100)).toBe(900);
    expect(engine.calculateTotal(10, 100)).toBe(900);  // 10% discount kicks in
    expect(engine.calculateTotal(50, 100)).toBe(4000); // 20% discount kicks in
  });

  test('never discounts below cost price', () => {
    const engine = new PricingEngine();

    // Critical invariant: price * quantity should never go below cost
    const result = engine.calculateTotal(100, 10, { costPerUnit: 8 });
    expect(result).toBeGreaterThanOrEqual(800);
  });

  test('rejects negative quantities', () => {
    const engine = new PricingEngine();

    expect(() => engine.calculateTotal(-1, 100)).toThrow('Quantity must be positive');
  });
});

describe('PermissionService', () => {
  test('admin can delete any resource', () => {
    const service = new PermissionService();

    expect(service.canDelete({ role: 'admin' }, { ownerId: 'other_user' })).toBe(true);
  });

  test('regular user can only delete own resources', () => {
    const service = new PermissionService();

    expect(service.canDelete({ role: 'user', id: 'u1' }, { ownerId: 'u1' })).toBe(true);
    expect(service.canDelete({ role: 'user', id: 'u1' }, { ownerId: 'u2' })).toBe(false);
  });

  test('suspended user cannot delete anything', () => {
    const service = new PermissionService();

    expect(service.canDelete({ role: 'user', id: 'u1', suspended: true }, { ownerId: 'u1' })).toBe(false);
  });
});
```

**Benefits:**
- Every test verifies a business rule or decision branch that could break in production
- Coverage reflects real risk areas, not trivial code
- Tests serve as documentation for important domain rules
- Refactoring getters/setters or data structures won't break meaningful tests

Reference: [Write Tests, Not Too Many, Mostly Integration](https://kentcdodds.com/blog/write-tests)
