---
title: Setup and Teardown
impact: CRITICAL
impactDescription: "test reliability and resource management"
tags: test-structure, setup, teardown, hooks
---

## Setup and Teardown

**Impact: CRITICAL (test reliability and resource management)**

Use setup and teardown hooks appropriately to prepare and clean up test environments. Use `beforeAll` for expensive one-time setup, `beforeEach` for per-test setup, `afterEach` to clean up test-specific data, and `afterAll` to close shared resources.

## Incorrect

```typescript
// ❌ Bad: Duplicated setup and missing cleanup
describe('UserService', () => {
  test('creates user successfully', async () => {
    const db = await Database.connect();
    const userService = new UserService(db);

    const user = await userService.create({ name: 'John' });

    expect(user.name).toBe('John');
    // Missing cleanup — database connection left open
  });

  test('finds user by id', async () => {
    const db = await Database.connect();
    const userService = new UserService(db);

    const created = await userService.create({ name: 'Jane' });
    const found = await userService.findById(created.id);

    expect(found.name).toBe('Jane');
    // Missing cleanup — test data left in database
  });

  test('updates user name', async () => {
    const db = await Database.connect();
    const userService = new UserService(db);

    const user = await userService.create({ name: 'Bob' });
    await userService.update(user.id, { name: 'Robert' });

    expect(await userService.findById(user.id)).toHaveProperty('name', 'Robert');
  });
});
```

**Problems:**
- Database connection is duplicated in every test
- No cleanup of test data or resource handles
- Connection leaks accumulate across tests
- Tests may interfere with each other through leftover data

## Correct

```typescript
// ✅ Good: Proper setup and teardown with hooks
describe('UserService', () => {
  let db: Database;
  let userService: UserService;

  beforeAll(async () => {
    db = await Database.connect();
  });

  beforeEach(() => {
    userService = new UserService(db);
  });

  afterEach(async () => {
    await db.query('DELETE FROM users WHERE email LIKE $1', ['%@test.com']);
  });

  afterAll(async () => {
    await db.disconnect();
  });

  test('creates user successfully', async () => {
    const user = await userService.create({
      name: 'John',
      email: 'john@test.com'
    });

    expect(user.name).toBe('John');
  });

  test('finds user by id', async () => {
    const created = await userService.create({
      name: 'Jane',
      email: 'jane@test.com'
    });

    const found = await userService.findById(created.id);

    expect(found.name).toBe('Jane');
  });

  describe('update operations', () => {
    let existingUser: User;

    beforeEach(async () => {
      existingUser = await userService.create({
        name: 'Bob',
        email: 'bob@test.com'
      });
    });

    test('updates user name', async () => {
      await userService.update(existingUser.id, { name: 'Robert' });

      const updated = await userService.findById(existingUser.id);
      expect(updated.name).toBe('Robert');
    });

    test('updates user email', async () => {
      await userService.update(existingUser.id, { email: 'robert@test.com' });

      const updated = await userService.findById(existingUser.id);
      expect(updated.email).toBe('robert@test.com');
    });
  });
});
```

**Benefits:**
- No resource leaks — connections and handles are properly closed
- Each test starts with a clean state via `afterEach` cleanup
- Common setup logic is not duplicated across tests (DRY)
- Expensive setup like database connections is done once with `beforeAll`
- Nested `beforeEach` provides context-specific setup for related tests

Reference: [Jest Docs — Setup and Teardown](https://jestjs.io/docs/setup-teardown)
