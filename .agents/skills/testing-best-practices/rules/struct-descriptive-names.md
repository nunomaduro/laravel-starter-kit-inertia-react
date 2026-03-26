---
title: Descriptive Test Names
impact: CRITICAL
impactDescription: "test documentation and debugging speed"
tags: test-structure, naming, readability
---

## Descriptive Test Names

**Impact: CRITICAL (test documentation and debugging speed)**

Write test names that clearly describe the scenario being tested and the expected outcome. Good naming patterns include `[method/feature] [expected behavior] [under condition]`, `should [expected behavior] when [condition]`, or `returns [value] for [input description]`.

## Incorrect

```typescript
// ❌ Bad: Vague and uninformative names
test('test1', () => {
  expect(validateEmail('test@example.com')).toBe(true);
});

test('email validation', () => {
  expect(validateEmail('invalid')).toBe(false);
});

test('it works', () => {
  const user = createUser({ name: 'John' });
  expect(user.name).toBe('John');
});

test('user test', () => {
  expect(() => createUser({ name: '' })).toThrow();
});
```

**Problems:**
- Names like `test1` and `it works` provide no insight into what is tested
- When a test fails, the name gives no clue about what behavior broke
- Cannot assess test coverage by reading test names alone
- Inconsistent naming patterns across tests

## Correct

```typescript
// ✅ Good: Clear, descriptive names following a consistent pattern
describe('validateEmail', () => {
  test('returns true for valid email with standard format', () => {
    expect(validateEmail('user@example.com')).toBe(true);
  });

  test('returns false when email is missing @ symbol', () => {
    expect(validateEmail('userexample.com')).toBe(false);
  });

  test('returns false when email has multiple @ symbols', () => {
    expect(validateEmail('user@@example.com')).toBe(false);
  });
});

describe('createUser', () => {
  test('creates user with provided name', () => {
    const user = createUser({ name: 'John' });
    expect(user.name).toBe('John');
  });

  test('throws ValidationError when name is empty string', () => {
    expect(() => createUser({ name: '' })).toThrow(ValidationError);
  });
});
```

**Benefits:**
- Test names serve as self-documenting specifications
- Failed tests immediately reveal which behavior broke
- Reviewing test names exposes gaps in coverage
- Team members understand tested behaviors at a glance

Reference: [Better Specs — Naming Conventions](https://www.betterspecs.org/)
