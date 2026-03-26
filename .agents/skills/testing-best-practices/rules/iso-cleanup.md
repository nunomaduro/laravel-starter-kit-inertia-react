---
title: Test Cleanup
impact: CRITICAL
impactDescription: "resource management and test isolation"
tags: test-isolation, cleanup, resource-management
---

## Test Cleanup

**Impact: CRITICAL (resource management and test isolation)**

Always clean up resources and side effects created during tests. Resources that commonly need cleanup include database connections, file system changes, network servers, timers, event listeners, environment variables, global state, and mocks.

## Incorrect

```typescript
// ❌ Bad: Missing cleanup leads to resource leaks and test pollution
describe('FileProcessor', () => {
  test('creates output file', async () => {
    const processor = new FileProcessor();
    await processor.writeOutput('/tmp/test-output.txt', 'content');

    const exists = await fs.pathExists('/tmp/test-output.txt');
    expect(exists).toBe(true);
    // File left on disk — pollutes filesystem
  });

  test('opens database connection', async () => {
    const db = await Database.connect();
    const users = await db.query('SELECT * FROM users');

    expect(users).toBeDefined();
    // Connection never closed — resource leak
  });

  test('starts server', async () => {
    const server = await startServer({ port: 3000 });

    const response = await fetch('http://localhost:3000/health');
    expect(response.ok).toBe(true);
    // Server never stopped — port remains occupied
  });

  test('modifies environment', () => {
    process.env.API_KEY = 'test-key';
    const config = loadConfig();

    expect(config.apiKey).toBe('test-key');
    // Environment variable persists to other tests
  });

  test('adds global event listener', () => {
    const handler = jest.fn();
    window.addEventListener('resize', handler);

    window.dispatchEvent(new Event('resize'));
    expect(handler).toHaveBeenCalled();
    // Event listener accumulates across tests
  });
});
```

**Problems:**
- Files left on disk pollute the filesystem
- Database connections are leaked, exhausting the connection pool
- Servers keep ports occupied, causing subsequent tests to fail
- Environment variables and event listeners persist across tests

## Correct

```typescript
// ✅ Good: Proper cleanup ensures isolation and prevents resource leaks
describe('FileProcessor', () => {
  const testFiles: string[] = [];

  afterEach(async () => {
    await Promise.all(testFiles.map(file => fs.remove(file)));
    testFiles.length = 0;
  });

  test('creates output file', async () => {
    const outputPath = '/tmp/test-output.txt';
    testFiles.push(outputPath);

    const processor = new FileProcessor();
    await processor.writeOutput(outputPath, 'content');

    const exists = await fs.pathExists(outputPath);
    expect(exists).toBe(true);
  });
});

describe('DatabaseOperations', () => {
  let db: Database;

  beforeAll(async () => {
    db = await Database.connect();
  });

  afterAll(async () => {
    await db.close();
  });

  afterEach(async () => {
    await db.query('DELETE FROM users WHERE email LIKE $1', ['%@test.com']);
  });

  test('queries users', async () => {
    await db.query('INSERT INTO users (email) VALUES ($1)', ['user@test.com']);

    const users = await db.query('SELECT * FROM users WHERE email = $1', ['user@test.com']);

    expect(users.rows).toHaveLength(1);
  });
});

describe('ServerTests', () => {
  let server: Server;

  beforeEach(async () => {
    server = await startServer({ port: 0 }); // Random port
  });

  afterEach(async () => {
    await server.close();
  });

  test('responds to health check', async () => {
    const address = server.address();
    const response = await fetch(`http://localhost:${address.port}/health`);

    expect(response.ok).toBe(true);
  });
});

describe('Configuration', () => {
  const originalEnv = { ...process.env };

  afterEach(() => {
    process.env = { ...originalEnv };
  });

  test('reads API key from environment', () => {
    process.env.API_KEY = 'test-key';

    const config = loadConfig();

    expect(config.apiKey).toBe('test-key');
  });
});

describe('EventHandling', () => {
  let handler: jest.Mock;

  beforeEach(() => {
    handler = jest.fn();
    window.addEventListener('resize', handler);
  });

  afterEach(() => {
    window.removeEventListener('resize', handler);
  });

  test('handles resize event', () => {
    window.dispatchEvent(new Event('resize'));

    expect(handler).toHaveBeenCalled();
  });
});
```

**Benefits:**
- Prevents memory leaks, file handle exhaustion, and connection pool depletion
- Side effects do not leak between tests
- Long-running CI test suites remain stable
- Tests behave the same on clean and dirty environments

Reference: [Jest Docs — Setup and Teardown](https://jestjs.io/docs/setup-teardown)
