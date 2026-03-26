---
title: Specific Assertions
impact: HIGH
impactDescription: "error message quality and test clarity"
tags: assertions, specific, matchers
---

## Specific Assertions

**Impact: HIGH (error message quality and test clarity)**

Use the most specific assertion available for clearer tests and better error messages. Specific matchers express intent more clearly and produce more informative failure output than generic boolean comparisons.

## Incorrect

```typescript
// ❌ Bad: Generic assertions hide intent and produce poor error messages
describe('UserService', () => {
  test('returns user by id', async () => {
    const user = await userService.findById('user-123');

    // Too generic — doesn't express what we're checking
    expect(user !== null).toBe(true);
    expect(user.name !== undefined).toBe(true);
  });

  test('validates email format', () => {
    const result = validateEmail('invalid-email');

    // Loses context about what failed
    expect(result).toBe(false);
  });

  test('returns list of users', async () => {
    const users = await userService.findAll();

    // Doesn't verify structure
    expect(users.length > 0).toBe(true);
  });

  test('user has required properties', () => {
    const user = createUser({ name: 'John', email: 'john@example.com' });

    // Testing everything loosely
    expect(JSON.stringify(user).includes('John')).toBe(true);
  });

  test('throws error for invalid id', async () => {
    try {
      await userService.findById('');
      expect(true).toBe(false); // Force fail if no error
    } catch (error) {
      expect(error).toBeDefined();
    }
  });
});
```

**Problems:**
- Boolean comparisons like `expect(x !== null).toBe(true)` produce unhelpful "expected true, got false" messages
- `JSON.stringify` checks are fragile and do not verify structure
- Try/catch for error testing misses the case where no error is thrown
- Generic assertions do not communicate intent to readers

## Correct

```typescript
// ✅ Good: Specific assertions with clear intent
describe('UserService', () => {
  test('returns user by id', async () => {
    const user = await userService.findById('user-123');

    expect(user).not.toBeNull();
    expect(user).toHaveProperty('name');
  });

  test('returns null for non-existent user', async () => {
    const user = await userService.findById('non-existent');

    expect(user).toBeNull();
  });

  test('validates correct email format', () => {
    expect(validateEmail('valid@example.com')).toBe(true);
  });

  test('rejects email without @ symbol', () => {
    expect(validateEmail('invalid-email')).toBe(false);
  });

  test('returns array of users', async () => {
    const users = await userService.findAll();

    expect(users).toBeInstanceOf(Array);
    expect(users).not.toHaveLength(0);
  });

  test('returns users with expected structure', async () => {
    const users = await userService.findAll();

    expect(users).toEqual(
      expect.arrayContaining([
        expect.objectContaining({
          id: expect.any(String),
          name: expect.any(String),
          email: expect.stringContaining('@')
        })
      ])
    );
  });

  test('user contains all required properties', () => {
    const user = createUser({ name: 'John', email: 'john@example.com' });

    expect(user).toMatchObject({
      name: 'John',
      email: 'john@example.com'
    });
  });

  test('user has generated id', () => {
    const user = createUser({ name: 'John', email: 'john@example.com' });

    expect(user.id).toMatch(/^user-[a-z0-9]+$/);
  });

  test('throws ValidationError for empty id', async () => {
    await expect(userService.findById('')).rejects.toThrow(ValidationError);
  });

  test('throws error with descriptive message for empty id', async () => {
    await expect(userService.findById('')).rejects.toThrow('ID cannot be empty');
  });
});
```

**Benefits:**
- When tests fail, the error output immediately explains what went wrong
- Anyone reading the test knows exactly what is being verified
- Assertions serve as executable specifications
- Specific matchers work better with TypeScript for type safety
- Easier to update tests when requirements change

Reference: [Jest Docs — Using Matchers](https://jestjs.io/docs/using-matchers)
