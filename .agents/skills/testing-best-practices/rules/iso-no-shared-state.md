---
title: No Shared Mutable State
impact: CRITICAL
impactDescription: "test predictability and flakiness prevention"
tags: test-isolation, shared-state, mutable-state
---

## No Shared Mutable State

**Impact: CRITICAL (test predictability and flakiness prevention)**

Avoid sharing mutable state between tests to prevent interference and flakiness. Shared mutable state is one of the most common causes of flaky tests. Use `beforeEach` to create fresh instances and reset any necessary state.

## Incorrect

```typescript
// ❌ Bad: Shared mutable state causes test interference
let counter = 0;
const cache: Map<string, User> = new Map();
const users: User[] = [];

describe('UserCache', () => {
  test('caches user on first access', () => {
    const user = { id: ++counter, name: 'Alice' };
    users.push(user);
    cache.set(user.id.toString(), user);

    expect(cache.get('1')).toEqual(user);
  });

  test('retrieves cached user', () => {
    // Relies on cache populated by previous test
    expect(cache.has('1')).toBe(true);
  });

  test('adds second user to cache', () => {
    const user = { id: ++counter, name: 'Bob' };
    users.push(user);
    cache.set(user.id.toString(), user);

    // This assertion depends on test execution order
    expect(users.length).toBe(2);
    expect(counter).toBe(2);
  });
});

// Global mock that persists between tests
jest.mock('./emailService', () => ({
  send: jest.fn()
}));
```

**Problems:**
- Module-level variables are mutated across tests
- Cache and array state depend on prior test execution
- Counter values are only correct if tests run in a specific order
- Global mocks retain call counts from previous tests

## Correct

```typescript
// ✅ Good: Each test has its own isolated state
describe('UserCache', () => {
  let cache: UserCache;
  let userIdCounter: number;

  beforeEach(() => {
    cache = new UserCache();
    userIdCounter = 0;
  });

  const createUser = (name: string): User => ({
    id: ++userIdCounter,
    name
  });

  test('caches user on first access', () => {
    const user = createUser('Alice');

    cache.set(user);

    expect(cache.get(user.id)).toEqual(user);
  });

  test('returns undefined for uncached user', () => {
    expect(cache.get(999)).toBeUndefined();
  });

  test('stores multiple users independently', () => {
    const alice = createUser('Alice');
    const bob = createUser('Bob');

    cache.set(alice);
    cache.set(bob);

    expect(cache.size()).toBe(2);
    expect(cache.get(alice.id)).toEqual(alice);
    expect(cache.get(bob.id)).toEqual(bob);
  });
});

describe('EmailNotificationService', () => {
  let emailService: jest.Mocked<EmailService>;
  let notificationService: NotificationService;

  beforeEach(() => {
    // Fresh mock for each test
    emailService = {
      send: jest.fn().mockResolvedValue(true)
    };
    notificationService = new NotificationService(emailService);
  });

  test('sends welcome email to new user', async () => {
    await notificationService.welcomeUser('alice@example.com');

    expect(emailService.send).toHaveBeenCalledTimes(1);
  });

  test('sends password reset email', async () => {
    await notificationService.sendPasswordReset('bob@example.com');

    // Not affected by previous test
    expect(emailService.send).toHaveBeenCalledTimes(1);
  });
});
```

**Benefits:**
- Each test starts with a known, predictable state
- One test cannot corrupt another test's data
- Tests can run concurrently without conflicts
- State is clearly defined within each test for easier debugging
- Results are consistent whether running one test or the full suite

Reference: [Jest Docs — Setup and Teardown](https://jestjs.io/docs/setup-teardown)
