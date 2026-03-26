---
title: Keep Unit Tests Fast
impact: LOW
impactDescription: "maintains developer feedback loop under 5 seconds for unit suites"
tags: performance, speed, unit-tests
---

## Keep Unit Tests Fast

**Impact: LOW (maintains developer feedback loop under 5 seconds for unit suites)**

Unit tests should use pure functions, mocked dependencies, and in-memory data. Each test should complete in under 50ms so the entire unit suite runs in seconds, not minutes.

## Incorrect

```typescript
// ❌ Bad: unit tests hitting real database and network
import { Pool } from 'pg';
import axios from 'axios';

describe('UserService', () => {
  let pool: Pool;

  beforeAll(async () => {
    // Connects to real database — adds 500ms+ startup
    pool = new Pool({ connectionString: 'postgresql://localhost:5432/testdb' });
    await pool.query('DELETE FROM users');
  });

  afterAll(async () => {
    await pool.end();
  });

  test('creates a user', async () => {
    // ~100ms: real database INSERT
    const result = await pool.query(
      "INSERT INTO users (name, email) VALUES ('Alice', 'alice@example.com') RETURNING *"
    );
    expect(result.rows[0].name).toBe('Alice');
  });

  test('fetches user avatar from external service', async () => {
    // ~300ms: real HTTP request — also flaky if service is down
    const response = await axios.get('https://api.example.com/avatars/alice');
    expect(response.status).toBe(200);
  });

  // Total suite time: 1-3 seconds for just 2 tests
  // With 200 tests like this: 2-5 minutes
});
```

**Problems:**
- Database connections add hundreds of milliseconds per test
- Network calls are slow and introduce flakiness from external dependencies
- File system I/O makes tests order-of-magnitude slower than in-memory operations
- Slow tests discourage running them frequently, reducing their value

## Correct

```typescript
// ✅ Good: fast unit tests with pure logic and mocked boundaries
// Each test runs in < 5ms

interface UserRepository {
  save(user: User): Promise<User>;
  findById(id: string): Promise<User | null>;
}

// In-memory implementation for unit tests
const createMockRepo = (): UserRepository => ({
  save: vi.fn().mockImplementation(async (user) => ({ ...user, id: 'user_1' })),
  findById: vi.fn().mockImplementation(async (id) => ({
    id,
    name: 'Alice',
    email: 'alice@example.com',
  })),
});

describe('UserService', () => {
  // ~1ms: no database connection, no network setup
  const repo = createMockRepo();
  const service = new UserService(repo);

  test('creates user with normalized email', async () => {
    // ~2ms: pure logic + mock call
    const user = await service.createUser({
      name: 'Alice',
      email: '  ALICE@Example.COM  ',
    });

    expect(repo.save).toHaveBeenCalledWith(
      expect.objectContaining({ email: 'alice@example.com' }),
    );
  });

  test('rejects duplicate email', async () => {
    // ~1ms: mock returns existing user
    repo.findById = vi.fn().mockResolvedValue({ id: '1', email: 'alice@example.com' });

    await expect(
      service.createUser({ name: 'Alice', email: 'alice@example.com' })
    ).rejects.toThrow('Email already in use');
  });

  // Total suite time: < 50ms for all tests
});

// Pure function tests — fastest possible, no mocks needed
describe('validateEmail', () => {
  // Each test: < 1ms
  test('accepts valid email', () => {
    expect(validateEmail('user@example.com')).toBe(true);
  });

  test('rejects email without @', () => {
    expect(validateEmail('invalid')).toBe(false);
  });

  test('rejects empty string', () => {
    expect(validateEmail('')).toBe(false);
  });
});

// Timing comparison:
// With real DB/network:  2 tests  = ~1,500ms
// With mocks/pure:       5 tests  = ~10ms
// Factor:                150x faster
```

**Benefits:**
- Entire unit suite runs in seconds, enabling continuous feedback during development
- No external dependencies means zero flakiness from network or database issues
- Pure function tests need no setup/teardown, making them trivial to write and maintain
- Fast tests get run frequently, catching bugs earlier in the development cycle

Reference: [Unit Testing Best Practices](https://martinfowler.com/bliki/UnitTest.html)
