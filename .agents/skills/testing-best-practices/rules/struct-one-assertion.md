---
title: Single Logical Assertion
impact: CRITICAL
impactDescription: "failure precision and test granularity"
tags: test-structure, assertion, single-responsibility
---

## Single Logical Assertion

**Impact: CRITICAL (failure precision and test granularity)**

Each test should verify one specific behavior or logical concept. Note that "single assertion" means testing one logical concept — using multiple `expect` statements is fine when they all verify different aspects of the same concept.

## Incorrect

```typescript
// ❌ Bad: Testing multiple unrelated behaviors
test('user registration', () => {
  const user = registerUser({
    email: 'test@example.com',
    password: 'SecurePass123!',
    name: 'John Doe'
  });

  expect(user.id).toBeDefined();
  expect(user.email).toBe('test@example.com');
  expect(user.name).toBe('John Doe');
  expect(user.password).toBeUndefined();
  expect(user.createdAt).toBeInstanceOf(Date);
  expect(user.isActive).toBe(false);
  expect(user.role).toBe('user');
  expect(sendWelcomeEmail).toHaveBeenCalledWith('test@example.com');
  expect(auditLog.entries).toHaveLength(1);
});
```

**Problems:**
- When the test fails, it is unclear which specific behavior broke
- Multiple unrelated behaviors are coupled into one test
- Changing one behavior forces updates to an unrelated test
- CI/CD reports cannot show granular pass/fail per behavior

## Correct

```typescript
// ✅ Good: Each test verifies one logical concept
describe('registerUser', () => {
  const validInput = {
    email: 'test@example.com',
    password: 'SecurePass123!',
    name: 'John Doe'
  };

  test('returns user with generated id', () => {
    const user = registerUser(validInput);
    expect(user.id).toBeDefined();
  });

  test('stores provided email and name', () => {
    const user = registerUser(validInput);
    expect(user).toMatchObject({
      email: 'test@example.com',
      name: 'John Doe'
    });
  });

  test('does not expose password in returned user object', () => {
    const user = registerUser(validInput);
    expect(user.password).toBeUndefined();
  });

  test('sets user as inactive pending email verification', () => {
    const user = registerUser(validInput);
    expect(user.isActive).toBe(false);
  });

  test('assigns default user role', () => {
    const user = registerUser(validInput);
    expect(user.role).toBe('user');
  });

  test('sends welcome email to registered user', () => {
    registerUser(validInput);
    expect(sendWelcomeEmail).toHaveBeenCalledWith('test@example.com');
  });

  test('creates audit log entry for registration', () => {
    registerUser(validInput);
    expect(auditLog.entries).toHaveLength(1);
  });
});
```

**Benefits:**
- Precise failure identification pinpoints exactly which behavior broke
- Each behavior is verified independently
- Test names clearly map to specific behaviors
- Easier maintenance when individual behaviors change
- Granular CI/CD feedback shows exactly what is working and what is not

Reference: [Unit Testing Best Practices — Single Assert](https://learn.microsoft.com/en-us/dotnet/core/testing/unit-testing-best-practices#avoid-multiple-asserts)
