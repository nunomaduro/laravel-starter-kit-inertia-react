---
title: Run Tests in Parallel
impact: LOW
impactDescription: "reduces CI test time by 40-70% on multi-core runners"
tags: performance, parallel, concurrent
---

## Run Tests in Parallel

**Impact: LOW (reduces CI test time by 40-70% on multi-core runners)**

Configure your test runner to execute test files in parallel and use `concurrent` for independent tests within a file. Ensure tests are isolated so they don't share mutable state.

## Incorrect

```typescript
// ❌ Bad: sequential execution with shared state preventing parallelism
// vitest.config.ts
import { defineConfig } from 'vitest/config';

export default defineConfig({
  test: {
    // Forces all tests to run one at a time
    pool: 'forks',
    poolOptions: {
      forks: { singleFork: true },
    },
    // Needed because tests share a database and write to the same tables
    sequence: { concurrent: false },
  },
});

// --- tests/user.test.ts ---
// Shared mutable state — can't run in parallel with other test files
let sharedDb: Database;

beforeAll(async () => {
  sharedDb = await connectToSharedDatabase();
  await sharedDb.query('DELETE FROM users'); // Wipes data other tests might need
});

describe('UserService', () => {
  test('creates user', async () => {
    await sharedDb.query("INSERT INTO users VALUES ('1', 'Alice')");
    const user = await sharedDb.query("SELECT * FROM users WHERE id = '1'");
    expect(user.name).toBe('Alice');
  });

  test('lists all users', async () => {
    // Depends on previous test's INSERT — order-dependent
    const users = await sharedDb.query('SELECT * FROM users');
    expect(users).toHaveLength(1);
  });
});
```

**Problems:**
- `singleFork: true` forces all test files to run sequentially — wasting CPU cores
- Shared database means tests can't run in parallel without stomping on each other
- Order-dependent tests fail when execution order changes
- CI takes minutes instead of seconds on multi-core runners

## Correct

```typescript
// ✅ Good: parallel execution with isolated tests
// vitest.config.ts
import { defineConfig } from 'vitest/config';

export default defineConfig({
  test: {
    // Run test files in parallel using threads (fastest for I/O-light tests)
    pool: 'threads',
    poolOptions: {
      threads: {
        minThreads: 2,
        maxThreads: 8,
      },
    },
  },
});

// --- tests/user.test.ts ---
// Each test file gets its own isolated mocks — no shared mutable state
const createIsolatedRepo = () => {
  const store = new Map<string, User>();
  return {
    save: vi.fn(async (user: User) => {
      store.set(user.id, user);
      return user;
    }),
    findById: vi.fn(async (id: string) => store.get(id) ?? null),
    findAll: vi.fn(async () => [...store.values()]),
    clear: () => store.clear(),
  };
};

describe('UserService', () => {
  // Each concurrent test gets its own isolated repo — no shared state
  test.concurrent('creates a user', async () => {
    const repo = createIsolatedRepo();
    const service = new UserService(repo);
    const user = await service.createUser({ name: 'Alice', email: 'alice@test.com' });
    expect(user.id).toBeDefined();
  });

  test.concurrent('validates email format', async () => {
    const repo = createIsolatedRepo();
    const service = new UserService(repo);
    await expect(
      service.createUser({ name: 'Bob', email: 'invalid' })
    ).rejects.toThrow('Invalid email');
  });

  test.concurrent('normalizes email to lowercase', async () => {
    const repo = createIsolatedRepo();
    const service = new UserService(repo);
    const user = await service.createUser({ name: 'Carol', email: 'CAROL@TEST.COM' });
    expect(user.email).toBe('carol@test.com');
  });
});

// --- For integration tests that need a real database, use isolated schemas ---
describe('UserService (integration)', () => {
  let db: Database;

  beforeAll(async () => {
    // Each test file gets its own schema — enables parallel execution
    const schemaName = `test_${randomUUID().slice(0, 8)}`;
    db = await createDatabase({ schema: schemaName });
    await db.migrate();
  });

  afterAll(async () => {
    await db.dropSchema();
    await db.close();
  });

  test('persists and retrieves user', async () => {
    const service = new UserService(new PostgresUserRepo(db));
    const created = await service.createUser({ name: 'Dave', email: 'dave@test.com' });
    const found = await service.findById(created.id);
    expect(found).toMatchObject({ name: 'Dave' });
  });
});
```

**Benefits:**
- `pool: 'threads'` distributes test files across CPU cores automatically
- `test.concurrent` runs independent tests within a file simultaneously
- In-memory stores eliminate database contention between parallel test files
- Isolated database schemas allow even integration tests to run in parallel

Reference: [Vitest - Pool Configuration](https://vitest.dev/config/#pool)
