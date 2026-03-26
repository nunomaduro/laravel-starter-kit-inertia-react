---
title: No Order Dependency
impact: CRITICAL
impactDescription: "parallel execution and test stability"
tags: test-isolation, order-independence, parallel
---

## No Order Dependency

**Impact: CRITICAL (parallel execution and test stability)**

Tests should pass regardless of the order in which they run. Many test runners shuffle test order to catch dependencies, and tests running in parallel have no guaranteed order.

## Incorrect

```typescript
// ❌ Bad: Tests that depend on execution order
describe('Database Integration', () => {
  test('inserts user into database', async () => {
    await db.query('INSERT INTO users (id, name) VALUES (1, "Alice")');
    const result = await db.query('SELECT * FROM users WHERE id = 1');
    expect(result.rows[0].name).toBe('Alice');
  });

  test('updates user in database', async () => {
    // Depends on previous test's insert
    await db.query('UPDATE users SET name = "Alicia" WHERE id = 1');
    const result = await db.query('SELECT * FROM users WHERE id = 1');
    expect(result.rows[0].name).toBe('Alicia');
  });

  test('verifies user state', async () => {
    // Depends on update from previous test
    const result = await db.query('SELECT * FROM users WHERE id = 1');
    expect(result.rows[0].name).toBe('Alicia');
  });

  test('deletes user from database', async () => {
    // Must run last or will break other tests
    await db.query('DELETE FROM users WHERE id = 1');
    const result = await db.query('SELECT * FROM users WHERE id = 1');
    expect(result.rows.length).toBe(0);
  });
});

// Static counter creates order dependency
let testSequence = 0;

test('first operation', () => {
  testSequence = 1;
  expect(processStep(testSequence)).toBe('step-1');
});

test('second operation', () => {
  // Fails if run before 'first operation'
  expect(testSequence).toBe(1);
  testSequence = 2;
  expect(processStep(testSequence)).toBe('step-2');
});
```

**Problems:**
- Each test depends on state left behind by the previous test
- Running tests in random order or in isolation will fail
- Static counters and module-level variables create hidden coupling
- Cannot safely parallelize or selectively run tests

## Correct

```typescript
// ✅ Good: Each test is self-contained
describe('Database Integration', () => {
  beforeEach(async () => {
    await db.query('DELETE FROM users');
  });

  test('inserts user into database', async () => {
    await db.query('INSERT INTO users (id, name) VALUES (1, "Alice")');

    const result = await db.query('SELECT * FROM users WHERE id = 1');

    expect(result.rows[0].name).toBe('Alice');
  });

  test('updates existing user in database', async () => {
    // Arrange: Create the user this test needs
    await db.query('INSERT INTO users (id, name) VALUES (1, "Alice")');

    // Act
    await db.query('UPDATE users SET name = "Alicia" WHERE id = 1');

    // Assert
    const result = await db.query('SELECT * FROM users WHERE id = 1');
    expect(result.rows[0].name).toBe('Alicia');
  });

  test('deletes user from database', async () => {
    // Arrange: Create the user this test needs
    await db.query('INSERT INTO users (id, name) VALUES (1, "Alice")');

    // Act
    await db.query('DELETE FROM users WHERE id = 1');

    // Assert
    const result = await db.query('SELECT * FROM users WHERE id = 1');
    expect(result.rows.length).toBe(0);
  });
});

// Each test is independent
describe('Step Processing', () => {
  test('processes first step correctly', () => {
    expect(processStep(1)).toBe('step-1');
  });

  test('processes second step correctly', () => {
    expect(processStep(2)).toBe('step-2');
  });

  test('processes steps in sequence', () => {
    const sequence = [1, 2, 3];
    const results = sequence.map(step => processStep(step));

    expect(results).toEqual(['step-1', 'step-2', 'step-3']);
  });
});
```

**Benefits:**
- Tests can be shuffled with `jest --randomize` to detect hidden dependencies
- Parallel execution is safe because no test depends on another
- Developers can run single tests or subsets during development
- Watch mode correctly re-runs only changed tests
- Individual tests are easy to debug in isolation

Reference: [Jest CLI — Randomize](https://jestjs.io/docs/cli#--randomize)
