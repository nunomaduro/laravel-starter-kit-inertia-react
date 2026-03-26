---
title: Expected vs Actual Order
impact: HIGH
impactDescription: "error message clarity and debugging speed"
tags: assertions, expected-actual, convention
---

## Expected vs Actual Order

**Impact: HIGH (error message clarity and debugging speed)**

Follow the convention of placing expected values first in assertions for consistent, readable error messages. In Jest/Vitest the convention is `expect(actual).toBe(expected)`. Always follow your framework's convention and be consistent throughout the codebase.

## Incorrect

```typescript
// ❌ Bad: Inconsistent or reversed order causes confusing error messages
describe('Calculator', () => {
  test('adds numbers', () => {
    const result = calculator.add(2, 3);

    // Reversed: actual first, expected second
    expect(5).toBe(result);
  });

  test('multiplies numbers', () => {
    // Mixed conventions in same test
    expect(calculator.multiply(3, 4)).toBe(12);  // Correct
    expect(24).toBe(calculator.multiply(4, 6)); // Reversed
  });
});

// Wrong order produces confusing error messages
describe('StringUtils', () => {
  test('capitalizes string', () => {
    const result = capitalize('hello');

    // Wrong order — error message says the opposite of reality
    assertEquals(result, 'Hello');
  });
});
```

**Problems:**
- Reversed assertion arguments produce misleading error messages
- Mixed conventions within the same test suite confuse readers
- Error messages say "expected X but got Y" with X and Y swapped
- Code reviewers cannot quickly spot assertion issues

## Correct

```typescript
// ✅ Good: Consistent order — actual value being tested first
describe('Calculator', () => {
  test('adds numbers correctly', () => {
    const result = calculator.add(2, 3);

    // Jest/Vitest convention: expect(actual).toBe(expected)
    expect(result).toBe(5);
  });

  test('multiplies numbers correctly', () => {
    expect(calculator.multiply(3, 4)).toBe(12);
    expect(calculator.multiply(4, 6)).toBe(24);
  });

  test('divides numbers correctly', () => {
    const result = calculator.divide(10, 2);

    expect(result).toBe(5);
  });
});

describe('StringUtils', () => {
  test('capitalizes first letter', () => {
    const result = capitalize('hello');

    expect(result).toBe('Hello');
  });

  test('handles already capitalized string', () => {
    expect(capitalize('Hello')).toBe('Hello');
  });
});

// Object comparison follows same principle
describe('UserFactory', () => {
  test('creates user with defaults', () => {
    const user = UserFactory.create({ name: 'John' });

    expect(user).toMatchObject({
      name: 'John',
      role: 'user',
      isActive: true
    });
  });

  test('overrides defaults when specified', () => {
    const user = UserFactory.create({ name: 'Admin', role: 'admin' });

    expect(user).toEqual(expect.objectContaining({
      name: 'Admin',
      role: 'admin'
    }));
  });
});
```

**Benefits:**
- When tests fail, the error message clearly shows what was expected vs what was received
- Consistent patterns make tests easier to scan and understand
- Aligns with framework conventions (`expect(actual).toBe(expected)`)
- Code reviewers can quickly spot assertion issues
- No mental translation needed when reading failure output

Reference: [Jest Docs — Expect](https://jestjs.io/docs/expect)
