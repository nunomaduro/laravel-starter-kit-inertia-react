# Testing Best Practices - Complete Reference

**Version:** 2.0.0
**Date:** March 2026
**License:** MIT

## Abstract

Unit testing, integration testing, and TDD principles for reliable, maintainable test suites. Contains 34 rules across 7 categories using TypeScript with Jest/Vitest.

## References

- [Vitest Documentation](https://vitest.dev)
- [Jest Documentation](https://jestjs.io)
- [Testing Library](https://testing-library.com)
- [MSW (Mock Service Worker)](https://mswjs.io)

---

# Sections

This file defines all sections, their ordering, impact levels, and descriptions.
The section ID (in parentheses) is the filename prefix used to group rules.

---

## 1. Test Structure (struct)

**Impact:** CRITICAL
**Description:** Fundamental patterns for organizing test code. AAA pattern for clear test flow, descriptive naming for test documentation, one assertion per test for precise failure messages, and proper setup/teardown for shared test context.

## 2. Test Isolation (iso)

**Impact:** CRITICAL
**Description:** Tests must run independently without shared mutable state. Independent execution, deterministic results, no order dependency, proper cleanup, and strategic use of test doubles ensure reliable test suites that can run in any order and in parallel.

## 3. Assertions (assert)

**Impact:** HIGH
**Description:** Effective assertions catch bugs and communicate intent. Specific matchers (toBe, toEqual, toContain), meaningful failure messages, consistent expected-first ordering, named constants over magic numbers, and custom matchers for domain logic.

## 4. Test Data (data)

**Impact:** HIGH
**Description:** Well-managed test data makes tests readable and maintainable. Factories for consistent object creation, builder pattern for complex objects, faker for realistic data, minimal data focused on what matters, and proper fixture management.

## 5. Mocking (mock)

**Impact:** MEDIUM
**Description:** Strategic mocking isolates code under test from external dependencies. Mock only at boundaries (APIs, databases, file system), verify important interactions, avoid over-mocking that tests implementation details, and use realistic mock behavior with tools like MSW.

## 6. Coverage (cov)

**Impact:** MEDIUM
**Description:** Coverage strategy guides testing effort. Focus on meaningful coverage of business logic and decision branches, cover edge cases and boundary values, test error scenarios and unhappy paths, and treat 80% as a baseline guide not a rigid target.

## 7. Performance (perf)

**Impact:** LOW
**Description:** Fast tests enable short feedback loops. Unit tests should run under 50ms each, parallel execution reduces total suite time, and organizing tests by speed tier (unit/integration/e2e) enables fast local feedback with comprehensive CI runs.


---

## Arrange-Act-Assert Pattern

**Impact: CRITICAL (test readability and maintainability)**

Structure every test using the AAA pattern to ensure clarity and consistency. The three phases — Arrange, Act, Assert — provide a clear narrative flow, making it immediately obvious what is being tested and what the expected behavior is.

## Incorrect

```typescript
// ❌ Bad: Mixed arrangement and assertions — confusing
test('calculates total price', () => {
  const cart = new ShoppingCart();
  expect(cart.isEmpty()).toBe(true);
  cart.addItem({ name: 'Apple', price: 1.50 });
  cart.addItem({ name: 'Banana', price: 0.75 });
  const discount = new Discount(10);
  cart.applyDiscount(discount);
  expect(cart.getTotal()).toBe(2.025);
  expect(cart.getItemCount()).toBe(2);
});
```

**Problems:**
- Setup and assertions are interleaved, making it hard to follow
- Multiple unrelated behaviors verified in a single test
- No clear separation between setup, action, and verification
- Difficult to identify the root cause when the test fails

## Correct

```typescript
// ✅ Good: Clear AAA structure
test('calculates total price with discount applied', () => {
  // Arrange
  const cart = new ShoppingCart();
  const discount = new Discount(10);
  cart.addItem({ name: 'Apple', price: 1.50 });
  cart.addItem({ name: 'Banana', price: 0.75 });

  // Act
  cart.applyDiscount(discount);

  // Assert
  expect(cart.getTotal()).toBe(2.025);
});

test('tracks item count correctly', () => {
  // Arrange
  const cart = new ShoppingCart();

  // Act
  cart.addItem({ name: 'Apple', price: 1.50 });
  cart.addItem({ name: 'Banana', price: 0.75 });

  // Assert
  expect(cart.getItemCount()).toBe(2);
});
```

**Benefits:**
- Each phase is clearly separated with comments
- Tests are focused on a single behavior
- Easy to identify setup, action, and verification at a glance
- When tests fail, the issue is quickly localized to one of the three phases

Reference: [Arrange-Act-Assert Pattern](https://automationpanda.com/2020/07/07/arrange-act-assert-a-pattern-for-writing-good-tests/)


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


---

## Describe-It Block Structure

**Impact: CRITICAL (test organization and readability)**

Organize tests using nested describe blocks and it/test functions for clear hierarchy and context. The hierarchy should mirror the structure of what you are testing: class > method > scenario.

## Incorrect

```typescript
// ❌ Bad: Flat structure without organization
test('calculator add positive numbers', () => {
  expect(calculator.add(2, 3)).toBe(5);
});

test('calculator add negative numbers', () => {
  expect(calculator.add(-2, -3)).toBe(-5);
});

test('calculator subtract', () => {
  expect(calculator.subtract(5, 3)).toBe(2);
});

test('calculator divide', () => {
  expect(calculator.divide(10, 2)).toBe(5);
});

test('calculator divide by zero', () => {
  expect(() => calculator.divide(10, 0)).toThrow();
});

test('calculator multiply', () => {
  expect(calculator.multiply(3, 4)).toBe(12);
});
```

**Problems:**
- No logical grouping of related tests
- No shared setup via `beforeEach`/`afterEach`
- Flat test output makes it hard to scan results
- Test names are long because they must include full context

## Correct

```typescript
// ✅ Good: Well-organized hierarchical structure
describe('Calculator', () => {
  let calculator: Calculator;

  beforeEach(() => {
    calculator = new Calculator();
  });

  describe('add', () => {
    it('returns sum of two positive numbers', () => {
      expect(calculator.add(2, 3)).toBe(5);
    });

    it('returns sum of two negative numbers', () => {
      expect(calculator.add(-2, -3)).toBe(-5);
    });

    it('returns sum of positive and negative numbers', () => {
      expect(calculator.add(5, -3)).toBe(2);
    });
  });

  describe('subtract', () => {
    it('returns difference between two numbers', () => {
      expect(calculator.subtract(5, 3)).toBe(2);
    });

    it('returns negative when subtracting larger from smaller', () => {
      expect(calculator.subtract(3, 5)).toBe(-2);
    });
  });

  describe('divide', () => {
    it('returns quotient of two numbers', () => {
      expect(calculator.divide(10, 2)).toBe(5);
    });

    it('returns decimal for non-even division', () => {
      expect(calculator.divide(5, 2)).toBe(2.5);
    });

    it('throws DivisionByZeroError when dividing by zero', () => {
      expect(() => calculator.divide(10, 0)).toThrow(DivisionByZeroError);
    });
  });

  describe('multiply', () => {
    it('returns product of two numbers', () => {
      expect(calculator.multiply(3, 4)).toBe(12);
    });

    it('returns zero when multiplying by zero', () => {
      expect(calculator.multiply(5, 0)).toBe(0);
    });
  });
});
```

**Benefits:**
- Related tests are logically grouped under meaningful contexts
- Shared setup is scoped to specific groups via `beforeEach`/`afterEach`
- Test runner displays results in a clear hierarchy
- Shorter test names because context is provided by describe blocks
- Easier navigation and filtering by describe block names

Reference: [Jest Docs — Organizing Tests](https://jestjs.io/docs/api#describename-fn)


---

## Given-When-Then Pattern

**Impact: CRITICAL (behavior-driven test clarity)**

Use behavior-driven development (BDD) style for tests that describe user-facing behavior. The pattern maps directly to user stories: "Given [context], when [action], then [outcome]". This is especially valuable for business logic, acceptance tests, and integration tests.

## Incorrect

```typescript
// ❌ Bad: Technical, implementation-focused test
test('order processing', () => {
  const order = new Order();
  order.items = [{ id: 1, price: 100 }];
  order.customerId = 'cust-123';
  const result = order.process();
  expect(result.status).toBe('processed');
  expect(result.total).toBe(100);
  expect(emailService.send).toHaveBeenCalled();
});
```

**Problems:**
- No separation between preconditions, action, and outcome
- Verifies multiple behaviors in a single test
- Reads like implementation details rather than a specification
- Non-developers cannot review or understand the test scenarios

## Correct

```typescript
// ✅ Good: BDD-style with Given-When-Then structure
describe('Order Processing', () => {
  describe('given a customer with items in their cart', () => {
    let order: Order;
    let customer: Customer;

    beforeEach(() => {
      // Given
      customer = CustomerFactory.create({ id: 'cust-123' });
      order = OrderFactory.createWithItems([
        { name: 'Widget', price: 100 }
      ]);
      order.assignToCustomer(customer);
    });

    describe('when the order is submitted', () => {
      let result: OrderResult;

      beforeEach(() => {
        // When
        result = order.submit();
      });

      it('then the order status is confirmed', () => {
        expect(result.status).toBe('confirmed');
      });

      it('then the order total reflects item prices', () => {
        expect(result.total).toBe(100);
      });

      it('then a confirmation email is sent to the customer', () => {
        expect(emailService.send).toHaveBeenCalledWith(
          expect.objectContaining({
            to: customer.email,
            template: 'order-confirmation'
          })
        );
      });
    });

    describe('when the order is submitted with an expired promotion', () => {
      beforeEach(() => {
        order.applyPromotion(ExpiredPromotion.create());
      });

      it('then the order is rejected with promotion error', () => {
        expect(() => order.submit()).toThrow(PromotionExpiredError);
      });
    });
  });

  describe('given a customer with an empty cart', () => {
    it('then submitting order throws EmptyCartError', () => {
      const order = OrderFactory.createEmpty();
      expect(() => order.submit()).toThrow(EmptyCartError);
    });
  });
});
```

**Benefits:**
- Tests read like specifications that non-developers can understand
- Each scenario is explicit about its preconditions
- Behavior focus over implementation details
- Tests serve as living, executable documentation
- Naturally leads to thinking about different scenarios and edge cases

Reference: [Cucumber — Given When Then](https://cucumber.io/docs/gherkin/reference/)


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


---

## Independent Tests

**Impact: CRITICAL (test reliability and parallel execution)**

Each test should be completely independent and not rely on other tests. Signs of dependent tests include tests that pass individually but fail together, tests that fail in different orders, and tests that only pass when the full suite runs.

## Incorrect

```typescript
// ❌ Bad: Tests depend on each other and share state
describe('ShoppingCart', () => {
  const cart = new ShoppingCart(); // Shared instance!

  test('adds first item to cart', () => {
    cart.addItem({ id: 1, name: 'Apple', price: 1.00 });
    expect(cart.getItemCount()).toBe(1);
  });

  test('adds second item to cart', () => {
    // Depends on previous test having added an item
    cart.addItem({ id: 2, name: 'Banana', price: 0.50 });
    expect(cart.getItemCount()).toBe(2);
  });

  test('calculates total', () => {
    // Depends on both previous tests
    expect(cart.getTotal()).toBe(1.50);
  });

  test('removes item', () => {
    // Depends on all previous tests
    cart.removeItem(1);
    expect(cart.getItemCount()).toBe(1);
    expect(cart.getTotal()).toBe(0.50);
  });
});
```

**Problems:**
- A shared mutable instance couples all tests together
- Tests cannot be run individually or in a different order
- A failure in one test cascades to all subsequent tests
- Parallelization is impossible because tests share state

## Correct

```typescript
// ✅ Good: Each test is independent with its own setup
describe('ShoppingCart', () => {
  let cart: ShoppingCart;

  beforeEach(() => {
    cart = new ShoppingCart(); // Fresh instance for each test
  });

  test('adds item to empty cart', () => {
    cart.addItem({ id: 1, name: 'Apple', price: 1.00 });

    expect(cart.getItemCount()).toBe(1);
  });

  test('adds multiple items to cart', () => {
    cart.addItem({ id: 1, name: 'Apple', price: 1.00 });
    cart.addItem({ id: 2, name: 'Banana', price: 0.50 });

    expect(cart.getItemCount()).toBe(2);
  });

  test('calculates total for multiple items', () => {
    cart.addItem({ id: 1, name: 'Apple', price: 1.00 });
    cart.addItem({ id: 2, name: 'Banana', price: 0.50 });

    expect(cart.getTotal()).toBe(1.50);
  });

  test('removes item from cart with multiple items', () => {
    cart.addItem({ id: 1, name: 'Apple', price: 1.00 });
    cart.addItem({ id: 2, name: 'Banana', price: 0.50 });

    cart.removeItem(1);

    expect(cart.getItemCount()).toBe(1);
    expect(cart.getTotal()).toBe(0.50);
  });

  test('returns zero total for empty cart', () => {
    expect(cart.getTotal()).toBe(0);
  });
});
```

**Benefits:**
- Tests can be run in any order, shuffled, parallelized, or run individually
- A failure in one test does not cascade to others
- Failed tests can be reproduced in isolation for easier debugging
- Independent tests can run concurrently for faster execution
- Changes to one test do not affect others

Reference: [Jest Docs — Test Isolation](https://jestjs.io/docs/setup-teardown#general-advice)


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


---

## Deterministic Tests

**Impact: CRITICAL (CI/CD reliability and developer trust)**

Tests should produce the same result every time they run, regardless of environment or timing. Common sources of non-determinism include time, random values, external services, file systems, databases, and concurrency.

## Incorrect

```typescript
// ❌ Bad: Non-deterministic tests with random/time dependencies
describe('OrderService', () => {
  test('generates unique order id', () => {
    const order = createOrder({ items: [{ id: 1 }] });

    // Flaky: depends on random number generation
    expect(order.id).toMatch(/^ORD-\d{6}$/);
  });

  test('sets order date to today', () => {
    const order = createOrder({ items: [{ id: 1 }] });

    // Flaky: will fail at midnight or on different days
    expect(order.date.toDateString()).toBe(new Date().toDateString());
  });

  test('expires order after 24 hours', async () => {
    const order = createOrder({ items: [{ id: 1 }] });

    // Terrible: actually waits 24 hours!
    await new Promise(resolve => setTimeout(resolve, 86400000));

    expect(order.isExpired()).toBe(true);
  });

  test('handles concurrent orders', async () => {
    // Flaky: race condition may or may not manifest
    const orders = await Promise.all([
      createOrder({ items: [{ id: 1 }] }),
      createOrder({ items: [{ id: 2 }] })
    ]);

    expect(orders[0].id).not.toBe(orders[1].id);
  });
});
```

**Problems:**
- Random value assertions can fail unpredictably
- Time-based tests break at midnight or across time zones
- Real `setTimeout` delays make tests impossibly slow
- Race conditions produce intermittent failures

## Correct

```typescript
// ✅ Good: Deterministic tests with controlled dependencies
describe('OrderService', () => {
  let orderService: OrderService;
  let mockIdGenerator: jest.Mocked<IdGenerator>;
  let mockClock: jest.Mocked<Clock>;

  beforeEach(() => {
    mockIdGenerator = {
      generate: jest.fn()
    };
    mockClock = {
      now: jest.fn()
    };
    orderService = new OrderService(mockIdGenerator, mockClock);
  });

  test('generates order id using id generator', () => {
    mockIdGenerator.generate.mockReturnValue('ORD-123456');

    const order = orderService.createOrder({ items: [{ id: 1 }] });

    expect(order.id).toBe('ORD-123456');
  });

  test('sets order date from clock', () => {
    const fixedDate = new Date('2024-01-15T10:00:00Z');
    mockClock.now.mockReturnValue(fixedDate);
    mockIdGenerator.generate.mockReturnValue('ORD-123456');

    const order = orderService.createOrder({ items: [{ id: 1 }] });

    expect(order.date).toEqual(fixedDate);
  });

  test('expires order after 24 hours from creation', () => {
    const creationTime = new Date('2024-01-15T10:00:00Z');
    const afterExpiration = new Date('2024-01-16T10:00:01Z');

    mockClock.now.mockReturnValue(creationTime);
    mockIdGenerator.generate.mockReturnValue('ORD-123456');

    const order = orderService.createOrder({ items: [{ id: 1 }] });

    mockClock.now.mockReturnValue(afterExpiration);

    expect(order.isExpired(mockClock)).toBe(true);
  });

  test('order is not expired within 24 hours', () => {
    const creationTime = new Date('2024-01-15T10:00:00Z');
    const beforeExpiration = new Date('2024-01-16T09:59:59Z');

    mockClock.now.mockReturnValue(creationTime);
    mockIdGenerator.generate.mockReturnValue('ORD-123456');

    const order = orderService.createOrder({ items: [{ id: 1 }] });

    mockClock.now.mockReturnValue(beforeExpiration);

    expect(order.isExpired(mockClock)).toBe(false);
  });

  test('generates unique ids for concurrent orders', async () => {
    let callCount = 0;
    mockIdGenerator.generate.mockImplementation(() => `ORD-${++callCount}`);
    mockClock.now.mockReturnValue(new Date('2024-01-15T10:00:00Z'));

    const orders = await Promise.all([
      orderService.createOrder({ items: [{ id: 1 }] }),
      orderService.createOrder({ items: [{ id: 2 }] })
    ]);

    expect(orders[0].id).toBe('ORD-1');
    expect(orders[1].id).toBe('ORD-2');
  });
});
```

**Benefits:**
- Failed tests can be debugged consistently with reproducible results
- Developers trust the test suite because it is reliable
- No wasted time investigating false alarms
- Tests can safely run in parallel
- Results are the same locally and in CI

Reference: [Martin Fowler — Eradicating Non-Determinism in Tests](https://martinfowler.com/articles/nonDeterminism.html)


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


---

## Test Doubles for Isolation

**Impact: CRITICAL (unit test speed and reliability)**

Use test doubles (mocks, stubs, spies, fakes) to isolate the unit under test from its dependencies. Types include mocks (record interactions), stubs (return predetermined responses), spies (wrap real objects), fakes (simplified implementations), and dummies (unused placeholders).

## Incorrect

```typescript
// ❌ Bad: Tests tightly coupled to real implementations
describe('OrderService', () => {
  test('sends order confirmation email', async () => {
    // Using real email service — slow, unreliable, sends real emails!
    const emailService = new EmailService();
    const orderService = new OrderService(emailService);

    await orderService.placeOrder({
      customerId: 'cust-123',
      items: [{ id: 1, quantity: 2 }]
    });

    // No way to verify email was sent without checking real inbox
  });

  test('saves order to database', async () => {
    // Using real database — slow, stateful, needs setup
    const database = new PostgresDatabase();
    const orderService = new OrderService(new EmailService(), database);

    const order = await orderService.placeOrder({
      customerId: 'cust-123',
      items: [{ id: 1, quantity: 2 }]
    });

    const saved = await database.query('SELECT * FROM orders WHERE id = $1', [order.id]);
    expect(saved.rows[0]).toBeDefined();
  });

  test('handles payment processing', async () => {
    // Using real payment gateway — charges real money!
    const paymentGateway = new StripeGateway(process.env.STRIPE_KEY);
    const orderService = new OrderService(
      new EmailService(),
      new PostgresDatabase(),
      paymentGateway
    );

    await orderService.placeOrder({ /* ... */ });
  });
});
```

**Problems:**
- Real email service sends actual emails during tests
- Real database makes tests slow and stateful
- Real payment gateway charges actual money
- No way to simulate error scenarios reliably
- Tests are flaky due to network and external service dependencies

## Correct

```typescript
// ✅ Good: Using test doubles for isolation
describe('OrderService', () => {
  let orderService: OrderService;
  let emailService: jest.Mocked<EmailService>;
  let orderRepository: jest.Mocked<OrderRepository>;
  let paymentGateway: jest.Mocked<PaymentGateway>;
  let inventoryService: jest.Mocked<InventoryService>;

  beforeEach(() => {
    emailService = {
      send: jest.fn().mockResolvedValue({ messageId: 'msg-123' })
    };

    orderRepository = {
      save: jest.fn().mockImplementation(order =>
        Promise.resolve({ ...order, id: 'order-123' })
      ),
      findById: jest.fn()
    };

    paymentGateway = {
      charge: jest.fn().mockResolvedValue({
        transactionId: 'txn-456',
        status: 'success'
      })
    };

    inventoryService = {
      reserve: jest.fn().mockResolvedValue(true),
      release: jest.fn().mockResolvedValue(true)
    };

    orderService = new OrderService(
      emailService,
      orderRepository,
      paymentGateway,
      inventoryService
    );
  });

  describe('placeOrder', () => {
    const validOrder = {
      customerId: 'cust-123',
      customerEmail: 'customer@example.com',
      items: [{ productId: 'prod-1', quantity: 2, price: 29.99 }]
    };

    test('saves order to repository', async () => {
      await orderService.placeOrder(validOrder);

      expect(orderRepository.save).toHaveBeenCalledWith(
        expect.objectContaining({
          customerId: 'cust-123',
          items: validOrder.items
        })
      );
    });

    test('sends confirmation email after successful order', async () => {
      await orderService.placeOrder(validOrder);

      expect(emailService.send).toHaveBeenCalledWith({
        to: 'customer@example.com',
        template: 'order-confirmation',
        data: expect.objectContaining({
          orderId: 'order-123'
        })
      });
    });

    test('charges payment gateway with correct amount', async () => {
      await orderService.placeOrder(validOrder);

      expect(paymentGateway.charge).toHaveBeenCalledWith({
        customerId: 'cust-123',
        amount: 59.98, // 2 * 29.99
        currency: 'USD'
      });
    });

    test('reserves inventory before processing payment', async () => {
      await orderService.placeOrder(validOrder);

      const reserveCall = inventoryService.reserve.mock.invocationCallOrder[0];
      const chargeCall = paymentGateway.charge.mock.invocationCallOrder[0];
      expect(reserveCall).toBeLessThan(chargeCall);
    });

    test('releases inventory when payment fails', async () => {
      paymentGateway.charge.mockRejectedValue(new PaymentError('Card declined'));

      await expect(orderService.placeOrder(validOrder)).rejects.toThrow('Card declined');

      expect(inventoryService.release).toHaveBeenCalled();
    });

    test('does not send email when payment fails', async () => {
      paymentGateway.charge.mockRejectedValue(new PaymentError('Card declined'));

      await expect(orderService.placeOrder(validOrder)).rejects.toThrow();

      expect(emailService.send).not.toHaveBeenCalled();
    });
  });
});

// Using a Fake for more complex scenarios
class FakeOrderRepository implements OrderRepository {
  private orders: Map<string, Order> = new Map();
  private idCounter = 0;

  async save(order: Omit<Order, 'id'>): Promise<Order> {
    const id = `order-${++this.idCounter}`;
    const saved = { ...order, id };
    this.orders.set(id, saved);
    return saved;
  }

  async findById(id: string): Promise<Order | null> {
    return this.orders.get(id) || null;
  }

  getAll(): Order[] {
    return Array.from(this.orders.values());
  }
}
```

**Benefits:**
- No network calls, database queries, or I/O — tests run fast
- Only the unit's logic is tested, not its dependencies
- Any scenario including errors and edge cases can be simulated
- Assertions verify exactly how dependencies were called
- No real emails sent or real payments charged
- No flakiness from external services

Reference: [Martin Fowler — Mocks Aren't Stubs](https://martinfowler.com/articles/mocksArentStubs.html)


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


---

## Meaningful Assertion Messages

**Impact: HIGH (debugging speed and CI/CD clarity)**

Add descriptive messages to assertions to clarify intent and improve debugging. Messages are especially valuable for complex assertions, numeric comparisons with business meaning, loop-based assertions, and any case where the failure reason is not obvious.

## Incorrect

```typescript
// ❌ Bad: No messages — unclear what failed when tests break
describe('PaymentProcessor', () => {
  test('processes valid payment', async () => {
    const result = await processor.processPayment({
      amount: 100,
      currency: 'USD',
      cardNumber: '4111111111111111'
    });

    expect(result.status).toBe('success');
    expect(result.transactionId).toBeDefined();
    expect(result.amount).toBe(100);
    expect(result.fee).toBeLessThan(5);
  });

  test('validates payment data', () => {
    const errors = validator.validate({
      amount: -50,
      currency: 'INVALID',
      cardNumber: '1234'
    });

    expect(errors.length).toBe(3);
    expect(errors[0].field).toBe('amount');
    expect(errors[1].field).toBe('currency');
    expect(errors[2].field).toBe('cardNumber');
  });

  test('handles currency conversion', () => {
    const result = converter.convert(100, 'USD', 'EUR');

    // When this fails, what was the actual value?
    expect(result).toBeGreaterThan(80);
    expect(result).toBeLessThan(120);
  });
});
```

**Problems:**
- When a test fails, the error output gives no context about what was expected
- Numeric comparisons do not explain the business reasoning behind thresholds
- Loop-based assertions do not indicate which iteration failed
- Build logs show cryptic comparisons instead of meaningful failure descriptions

## Correct

```typescript
// ✅ Good: Descriptive messages explain intent and help debugging
describe('PaymentProcessor', () => {
  test('processes valid payment', async () => {
    const result = await processor.processPayment({
      amount: 100,
      currency: 'USD',
      cardNumber: '4111111111111111'
    });

    expect(result.status).toBe('success');
    expect(result.transactionId).toBeDefined();
    expect(result.amount).toBe(100);

    expect(result.fee).toBeLessThan(
      5,
      `Processing fee ${result.fee} exceeds maximum allowed fee of 5`
    );
  });

  test('validates payment data', () => {
    const errors = validator.validate({
      amount: -50,
      currency: 'INVALID',
      cardNumber: '1234'
    });

    expect(errors).toHaveLength(3);

    const expectedErrors = [
      { field: 'amount', reason: 'Amount must be positive' },
      { field: 'currency', reason: 'Invalid currency code' },
      { field: 'cardNumber', reason: 'Card number too short' }
    ];

    expectedErrors.forEach(({ field, reason }, index) => {
      expect(errors[index]).toMatchObject(
        { field },
        `Expected error at index ${index} to be for field "${field}" (${reason})`
      );
    });
  });

  test('handles currency conversion within expected range', () => {
    const result = converter.convert(100, 'USD', 'EUR');

    expect(result).toBeGreaterThan(
      80,
      `Converted amount ${result} EUR is below minimum expected (80 EUR for 100 USD)`
    );
    expect(result).toBeLessThan(
      120,
      `Converted amount ${result} EUR exceeds maximum expected (120 EUR for 100 USD)`
    );
  });
});

// Using custom matchers for domain-specific assertions
expect.extend({
  toBeWithinRange(received: number, floor: number, ceiling: number) {
    const pass = received >= floor && received <= ceiling;

    return {
      pass,
      message: () =>
        pass
          ? `Expected ${received} not to be within range ${floor} - ${ceiling}`
          : `Expected ${received} to be within range ${floor} - ${ceiling}, but it was ${
              received < floor ? `${floor - received} below minimum` : `${received - ceiling} above maximum`
            }`
    };
  }
});

describe('with custom matchers', () => {
  test('conversion rate within expected range', () => {
    const result = converter.convert(100, 'USD', 'EUR');
    expect(result).toBeWithinRange(80, 120);
  });
});
```

**Benefits:**
- Faster debugging because failure messages immediately explain what went wrong
- Context is preserved in the error output showing actual values involved
- Messages document the business reasoning behind assertions
- CI/CD build logs show meaningful failures instead of cryptic comparisons
- Reviewers understand the intent of assertions without reading surrounding code

Reference: [Jest Docs — Expect](https://jestjs.io/docs/expect)


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


---

## No Magic Numbers in Assertions

**Impact: HIGH (test self-documentation and maintainability)**

Use named constants or explanatory variables instead of unexplained numeric literals. Name constants after their business meaning, not their value, and show derivation of expected values through calculation.

## Incorrect

```typescript
// ❌ Bad: Magic numbers obscure the meaning of tests
describe('DiscountCalculator', () => {
  test('calculates discount correctly', () => {
    const total = calculator.calculateTotal(100, 'SUMMER2024');

    expect(total).toBe(85); // Why 85? What's the discount?
  });

  test('applies maximum discount cap', () => {
    const discount = calculator.getDiscount(1000, 'MEGASALE');

    expect(discount).toBe(150); // Why is max 150?
  });

  test('handles loyalty points', () => {
    const points = loyaltyService.calculatePoints(250);

    expect(points).toBe(25); // What's the conversion rate?
  });
});

describe('PaginationService', () => {
  test('returns correct page', () => {
    const result = service.paginate(items, 2, 10);

    expect(result.items).toHaveLength(10);
    expect(result.totalPages).toBe(5); // Why 5?
    expect(result.startIndex).toBe(10); // Why 10?
    expect(result.endIndex).toBe(19);   // Why 19?
  });
});
```

**Problems:**
- Numeric literals give no indication of what they represent
- Reviewers cannot verify correctness without understanding the formula
- Changing a business rule requires finding and updating scattered numbers
- Debugging a failure requires reverse-engineering the expected value

## Correct

```typescript
// ✅ Good: Named constants explain the meaning of values
describe('DiscountCalculator', () => {
  const ORIGINAL_PRICE = 100;
  const SUMMER_DISCOUNT_PERCENT = 15;
  const EXPECTED_DISCOUNTED_PRICE = ORIGINAL_PRICE * (1 - SUMMER_DISCOUNT_PERCENT / 100);

  test('applies summer discount percentage to order total', () => {
    const total = calculator.calculateTotal(ORIGINAL_PRICE, 'SUMMER2024');

    expect(total).toBe(EXPECTED_DISCOUNTED_PRICE); // 85
  });

  const LARGE_ORDER_TOTAL = 1000;
  const MAXIMUM_DISCOUNT_CAP = 150;

  test('caps discount at maximum allowed amount', () => {
    const discount = calculator.getDiscount(LARGE_ORDER_TOTAL, 'MEGASALE');

    expect(discount).toBe(MAXIMUM_DISCOUNT_CAP);
  });

  const PURCHASE_AMOUNT = 250;
  const POINTS_PER_DOLLAR = 0.1;
  const EXPECTED_POINTS = PURCHASE_AMOUNT * POINTS_PER_DOLLAR;

  test('converts purchase amount to loyalty points', () => {
    const points = loyaltyService.calculatePoints(PURCHASE_AMOUNT);

    expect(points).toBe(EXPECTED_POINTS); // 25 points
  });
});

describe('PaginationService', () => {
  const TOTAL_ITEMS = 50;
  const PAGE_SIZE = 10;
  const CURRENT_PAGE = 2;

  const EXPECTED_TOTAL_PAGES = Math.ceil(TOTAL_ITEMS / PAGE_SIZE);
  const EXPECTED_START_INDEX = CURRENT_PAGE * PAGE_SIZE;
  const EXPECTED_END_INDEX = EXPECTED_START_INDEX + PAGE_SIZE - 1;

  let items: Item[];

  beforeEach(() => {
    items = Array.from({ length: TOTAL_ITEMS }, (_, i) => ({
      id: i + 1,
      name: `Item ${i + 1}`
    }));
  });

  test('returns correct page metadata', () => {
    const result = service.paginate(items, CURRENT_PAGE, PAGE_SIZE);

    expect(result.items).toHaveLength(PAGE_SIZE);
    expect(result.totalPages).toBe(EXPECTED_TOTAL_PAGES);
    expect(result.startIndex).toBe(EXPECTED_START_INDEX);
    expect(result.endIndex).toBe(EXPECTED_END_INDEX);
  });
});

describe('RateLimiter', () => {
  const RATE_LIMIT_PER_MINUTE = 100;
  const REQUESTS_AT_LIMIT = RATE_LIMIT_PER_MINUTE;
  const REQUESTS_EXCEEDING_LIMIT = RATE_LIMIT_PER_MINUTE + 1;

  test('allows requests up to the rate limit', async () => {
    for (let i = 0; i < REQUESTS_AT_LIMIT; i++) {
      await expect(rateLimiter.checkLimit('user-1')).resolves.not.toThrow();
    }
  });

  test('blocks requests exceeding the rate limit', async () => {
    for (let i = 0; i < REQUESTS_AT_LIMIT; i++) {
      await rateLimiter.checkLimit('user-2');
    }

    await expect(rateLimiter.checkLimit('user-2'))
      .rejects.toThrow(`Rate limit of ${RATE_LIMIT_PER_MINUTE} requests exceeded`);
  });
});
```

**Benefits:**
- Constants are self-documenting and explain what values represent
- Changing a value requires an update in only one place
- Calculations show how expected values are derived so reviewers can verify logic
- When tests fail, the constant names help understand what the numbers mean
- Domain terminology in constant names improves readability

Reference: [Clean Code — Chapter 17: Smells and Heuristics (Magic Numbers)](https://www.oreilly.com/library/view/clean-code-a/9780136083238/)


---

## Custom Matchers

**Impact: HIGH (assertion readability and reusability)**

Create custom assertion matchers for domain-specific validations to improve readability and reusability. Custom matchers are valuable when you have repeated assertion patterns, complex multi-property checks, or assertions that need better error messages.

## Incorrect

```typescript
// ❌ Bad: Repeated complex assertions without abstraction
describe('OrderService', () => {
  test('creates pending order', () => {
    const order = orderService.create({ items: [{ id: 1 }] });

    expect(order.status).toBe('pending');
    expect(order.paidAt).toBeNull();
    expect(order.shippedAt).toBeNull();
    expect(order.items.length).toBeGreaterThan(0);
  });

  test('creates another pending order', () => {
    const order = orderService.create({ items: [{ id: 2 }, { id: 3 }] });

    // Same assertions duplicated
    expect(order.status).toBe('pending');
    expect(order.paidAt).toBeNull();
    expect(order.shippedAt).toBeNull();
    expect(order.items.length).toBeGreaterThan(0);
  });
});

describe('DateUtils', () => {
  test('returns date within business hours', () => {
    const date = dateUtils.nextBusinessHour();

    const hours = date.getHours();
    expect(hours).toBeGreaterThanOrEqual(9);
    expect(hours).toBeLessThanOrEqual(17);
    expect(date.getDay()).not.toBe(0);
    expect(date.getDay()).not.toBe(6);
  });
});
```

**Problems:**
- Complex assertion patterns are duplicated across tests
- Error messages are generic and do not explain domain context
- Updating validation logic requires changes in multiple places
- Tests read like implementation details rather than domain language

## Correct

```typescript
// ✅ Good: Custom matchers encapsulate domain-specific assertions
expect.extend({
  toBePendingOrder(received: Order) {
    const isPending = received.status === 'pending';
    const isUnpaid = received.paidAt === null;
    const isUnshipped = received.shippedAt === null;
    const hasItems = received.items.length > 0;

    const pass = isPending && isUnpaid && isUnshipped && hasItems;

    const failures: string[] = [];
    if (!isPending) failures.push(`status is "${received.status}" (expected "pending")`);
    if (!isUnpaid) failures.push(`paidAt is ${received.paidAt} (expected null)`);
    if (!isUnshipped) failures.push(`shippedAt is ${received.shippedAt} (expected null)`);
    if (!hasItems) failures.push('order has no items');

    return {
      pass,
      message: () =>
        pass
          ? `Expected order not to be a valid pending order`
          : `Expected order to be a valid pending order, but: ${failures.join(', ')}`
    };
  },

  toBeWithinBusinessHours(received: Date) {
    const hours = received.getHours();
    const dayOfWeek = received.getDay();
    const isWeekday = dayOfWeek !== 0 && dayOfWeek !== 6;
    const isBusinessHours = hours >= 9 && hours <= 17;

    const pass = isWeekday && isBusinessHours;

    return {
      pass,
      message: () =>
        pass
          ? `Expected ${received} not to be within business hours`
          : `Expected ${received} to be within business hours (Mon-Fri 9AM-5PM), ` +
            `but was ${['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'][dayOfWeek]} at ${hours}:00`
    };
  },

  toHaveValidationError(received: ValidationResult, field: string, message?: string) {
    const error = received.errors.find(e => e.field === field);
    const hasError = !!error;
    const messageMatches = message ? error?.message.includes(message) : true;

    const pass = hasError && messageMatches;

    return {
      pass,
      message: () =>
        pass
          ? `Expected no validation error for field "${field}"`
          : hasError
            ? `Expected error message to include "${message}", but got "${error?.message}"`
            : `Expected validation error for field "${field}", but none found. Errors: ${JSON.stringify(received.errors)}`
    };
  }
});

// TypeScript declarations for custom matchers
declare global {
  namespace jest {
    interface Matchers<R> {
      toBePendingOrder(): R;
      toBeWithinBusinessHours(): R;
      toHaveValidationError(field: string, message?: string): R;
    }
  }
}

// Clean tests using custom matchers
describe('OrderService', () => {
  test('creates pending order', () => {
    const order = orderService.create({ items: [{ id: 1 }] });

    expect(order).toBePendingOrder();
  });
});

describe('DateUtils', () => {
  test('returns date within business hours', () => {
    const date = dateUtils.nextBusinessHour();

    expect(date).toBeWithinBusinessHours();
  });
});

describe('FormValidation', () => {
  test('validates required fields', () => {
    const result = validator.validate({ email: '', password: '' });

    expect(result).toHaveValidationError('email', 'required');
    expect(result).toHaveValidationError('password', 'required');
  });
});
```

**Benefits:**
- Tests read like specifications using domain language
- Complex validations are defined once and reused everywhere
- Custom error messages explain failures in business terms
- Validation logic is maintained in a single place
- Consistent validation rules applied uniformly across tests

Reference: [Jest Docs — Custom Matchers](https://jestjs.io/docs/expect#expectextendmatchers)


---

## Test Data Factories

**Impact: HIGH (test data consistency and reduced duplication)**

Use factory functions to create test data with sensible defaults and easy customization. Factories define the object structure once and let tests override only the properties that matter for each scenario.

## Incorrect

```typescript
// ❌ Bad: Duplicated object creation with inconsistent defaults
describe('OrderService', () => {
  test('calculates total for single item', () => {
    const order = {
      id: 'order-1',
      customerId: 'cust-123',
      items: [{ id: 'item-1', name: 'Widget', price: 29.99, quantity: 1 }],
      status: 'pending',
      createdAt: new Date(),
      updatedAt: new Date(),
      shippingAddress: {
        street: '123 Main St',
        city: 'Springfield',
        state: 'IL',
        zip: '62701',
        country: 'USA'
      },
      billingAddress: {
        street: '123 Main St',
        city: 'Springfield',
        state: 'IL',
        zip: '62701',
        country: 'USA'
      }
    };

    expect(orderService.calculateTotal(order)).toBe(29.99);
  });

  test('applies discount', () => {
    // Copy-pasted with slight modifications — hard to spot differences
    const order = {
      id: 'order-2',
      customerId: 'cust-456',
      items: [{ id: 'item-2', name: 'Gadget', price: 49.99, quantity: 2 }],
      status: 'pending',
      discount: 10,
      createdAt: new Date(),
      updatedAt: new Date(),
      shippingAddress: {
        street: '456 Oak Ave',
        city: 'Chicago',
        state: 'IL',
        zip: '60601',
        country: 'USA'
      },
      billingAddress: {
        street: '456 Oak Ave',
        city: 'Chicago',
        state: 'IL',
        zip: '60601',
        country: 'USA'
      }
    };

    expect(orderService.calculateTotal(order)).toBe(89.982);
  });
});
```

**Problems:**
- Full object construction is duplicated across tests
- Differences between test objects are hard to spot in walls of data
- Schema changes require updates in every test
- Inconsistent default values across tests cause confusion

## Correct

```typescript
// ✅ Good: Factory functions with sensible defaults
interface OrderFactoryOptions {
  id?: string;
  customerId?: string;
  items?: OrderItem[];
  status?: OrderStatus;
  discount?: number;
  shippingAddress?: Partial<Address>;
  billingAddress?: Partial<Address>;
}

const defaultAddress: Address = {
  street: '123 Test St',
  city: 'Test City',
  state: 'TS',
  zip: '12345',
  country: 'USA'
};

let orderIdCounter = 0;

export const OrderFactory = {
  create(options: OrderFactoryOptions = {}): Order {
    const id = options.id ?? `order-${++orderIdCounter}`;
    const now = new Date();

    return {
      id,
      customerId: options.customerId ?? 'default-customer',
      items: options.items ?? [OrderItemFactory.create()],
      status: options.status ?? 'pending',
      discount: options.discount ?? 0,
      createdAt: now,
      updatedAt: now,
      shippingAddress: { ...defaultAddress, ...options.shippingAddress },
      billingAddress: { ...defaultAddress, ...options.billingAddress }
    };
  },

  createPending(options: OrderFactoryOptions = {}): Order {
    return this.create({ ...options, status: 'pending' });
  },

  createPaid(options: OrderFactoryOptions = {}): Order {
    return this.create({ ...options, status: 'paid' });
  },

  createShipped(options: OrderFactoryOptions = {}): Order {
    return this.create({ ...options, status: 'shipped' });
  },

  reset(): void {
    orderIdCounter = 0;
  }
};

export const OrderItemFactory = {
  create(options: Partial<OrderItem> = {}): OrderItem {
    return {
      id: options.id ?? `item-${Math.random().toString(36).slice(2)}`,
      name: options.name ?? 'Test Product',
      price: options.price ?? 9.99,
      quantity: options.quantity ?? 1
    };
  },

  createMany(count: number, options: Partial<OrderItem> = {}): OrderItem[] {
    return Array.from({ length: count }, (_, i) =>
      this.create({ ...options, name: `${options.name ?? 'Product'} ${i + 1}` })
    );
  }
};

// Clean tests using factories
describe('OrderService', () => {
  beforeEach(() => {
    OrderFactory.reset();
  });

  test('calculates total for single item', () => {
    const order = OrderFactory.create({
      items: [OrderItemFactory.create({ price: 29.99 })]
    });

    expect(orderService.calculateTotal(order)).toBe(29.99);
  });

  test('calculates total for multiple items', () => {
    const order = OrderFactory.create({
      items: [
        OrderItemFactory.create({ price: 10, quantity: 2 }),
        OrderItemFactory.create({ price: 5, quantity: 3 }),
      ],
    });

    expect(orderService.calculateTotal(order)).toBe(35);
  });

  test('applies percentage discount to total', () => {
    const order = OrderFactory.create({
      items: [OrderItemFactory.create({ price: 100 })],
      discount: 10
    });

    expect(orderService.calculateTotal(order)).toBe(90);
  });

  test('processes payment for pending order', async () => {
    const order = OrderFactory.createPending();

    await orderService.processPayment(order.id);

    expect(order.status).toBe('paid');
  });
});
```

**Benefits:**
- Object structure is defined once, used everywhere
- Sensible defaults minimize setup code in each test
- Tests highlight only the properties that matter for each scenario
- Schema changes require updates in a single place
- Preset factory methods like `createPaid` improve test readability
- Consistent test data patterns across the entire test suite

Reference: [Thoughtbot — Factory Patterns](https://thoughtbot.com/blog/factory_bot)


---

## Test Data Builders

**Impact: HIGH (complex test data readability and maintainability)**

Use the builder pattern for complex test data with fluent, chainable configuration. Builders are ideal for objects with many optional configurations, while factories work better for simpler objects with few variations.

## Incorrect

```typescript
// ❌ Bad: Complex nested object construction is hard to read and maintain
describe('ReportGenerator', () => {
  test('generates monthly sales report', () => {
    const report = {
      id: 'report-1',
      type: 'monthly-sales',
      dateRange: {
        start: new Date('2024-01-01'),
        end: new Date('2024-01-31')
      },
      filters: {
        regions: ['US', 'EU'],
        categories: ['electronics'],
        minAmount: 100,
        maxAmount: null,
        includeRefunds: false,
        excludeTestOrders: true
      },
      groupBy: ['region', 'category'],
      sortBy: { field: 'totalSales', direction: 'desc' },
      format: {
        type: 'pdf',
        includeCharts: true,
        chartTypes: ['bar', 'pie'],
        pageSize: 'A4',
        orientation: 'landscape'
      },
      recipients: [
        { email: 'manager@example.com', name: 'Manager' }
      ],
      schedule: {
        frequency: 'monthly',
        dayOfMonth: 1,
        time: '09:00'
      }
    };

    expect(generator.canGenerate(report)).toBe(true);
  });
});
```

**Problems:**
- Large inline object literals obscure what the test is actually verifying
- Irrelevant properties clutter the test making it hard to spot the key data
- Duplicating the same structure across tests is error-prone
- Changes to the object shape require updates in every test

## Correct

```typescript
// ✅ Good: Builder pattern for fluent test data construction
class ReportBuilder {
  private report: Partial<Report> = {
    id: `report-${Date.now()}`,
    type: 'basic',
    filters: {},
    format: { type: 'pdf' }
  };

  static create(): ReportBuilder {
    return new ReportBuilder();
  }

  ofType(type: ReportType): this {
    this.report.type = type;
    return this;
  }

  forMonth(year: number, month: number): this {
    const start = new Date(year, month - 1, 1);
    const end = new Date(year, month, 0);
    this.report.dateRange = { start, end };
    return this;
  }

  forRegions(...regions: string[]): this {
    this.report.filters = { ...this.report.filters, regions };
    return this;
  }

  withCharts(...chartTypes: ChartType[]): this {
    this.report.format = {
      ...this.report.format,
      includeCharts: true,
      chartTypes
    };
    return this;
  }

  sendTo(email: string, name?: string): this {
    this.report.recipients = [
      ...(this.report.recipients || []),
      { email, name: name ?? email.split('@')[0] }
    ];
    return this;
  }

  scheduledMonthly(dayOfMonth: number, time: string): this {
    this.report.schedule = {
      frequency: 'monthly',
      dayOfMonth,
      time
    };
    return this;
  }

  build(): Report {
    return this.report as Report;
  }
}

// Preset builders for common scenarios
class MonthlySalesReportBuilder extends ReportBuilder {
  constructor() {
    super();
    this.ofType('monthly-sales')
      .groupedBy('region', 'category')
      .sortedBy('totalSales', 'desc')
      .asPdf({ orientation: 'landscape' });
  }

  static create(): MonthlySalesReportBuilder {
    return new MonthlySalesReportBuilder();
  }
}

// Clean, readable tests using builders
describe('ReportGenerator', () => {
  test('generates basic monthly sales report', () => {
    const report = ReportBuilder.create()
      .ofType('monthly-sales')
      .forMonth(2024, 1)
      .build();

    expect(generator.canGenerate(report)).toBe(true);
  });

  test('generates regional sales report with charts', () => {
    const report = MonthlySalesReportBuilder.create()
      .forMonth(2024, 1)
      .forRegions('US', 'EU')
      .withCharts('bar', 'pie')
      .build();

    const result = generator.generate(report);

    expect(result.pages).toHaveLength(2);
    expect(result.charts).toHaveLength(2);
  });

  test('schedules and emails monthly report', () => {
    const report = MonthlySalesReportBuilder.create()
      .forMonth(2024, 1)
      .sendTo('manager@example.com', 'Sales Manager')
      .sendTo('ceo@example.com', 'CEO')
      .scheduledMonthly(1, '09:00')
      .build();

    expect(report.recipients).toHaveLength(2);
    expect(report.schedule?.frequency).toBe('monthly');
  });
});
```

**Benefits:**
- Fluent API with chainable methods reads like natural language
- Method names explain what each configuration does (self-documenting)
- Tests build only the data they need, nothing more
- IDE autocomplete shows available configuration options
- Base builders can be extended for common scenarios
- Changes to data structure are isolated to the builder class

Reference: [Test Data Builder Pattern](https://wiki.c2.com/?TestDataBuilder)


---

## Faker Libraries for Test Data

**Impact: HIGH (test data variety and edge case discovery)**

Use faker libraries to generate realistic, varied test data programmatically. Seed faker for reproducible tests, combine it with custom factories, and override specific values when testing specific behaviors.

## Incorrect

```typescript
// ❌ Bad: Static, repetitive test data lacks variety and realism
describe('UserService', () => {
  test('creates user', async () => {
    const user = await userService.create({
      email: 'test@test.com',
      name: 'Test User',
      phone: '123-456-7890'
    });
    expect(user.id).toBeDefined();
  });

  test('creates another user', async () => {
    const user = await userService.create({
      email: 'test2@test.com',
      name: 'Test User 2',
      phone: '123-456-7891'
    });
    expect(user.id).toBeDefined();
  });
});

// Generating many records is painful
describe('BulkImport', () => {
  test('imports 100 users', async () => {
    const users = [];
    for (let i = 0; i < 100; i++) {
      users.push({
        email: `user${i}@test.com`,
        name: `User ${i}`,
        age: 25
      });
    }
    // All users look identical except for number suffix
    const result = await importService.bulkImport(users);
    expect(result.imported).toBe(100);
  });
});
```

**Problems:**
- Static placeholder data does not reveal real-world edge cases
- Manually numbered data (`User 1`, `User 2`) is tedious and unrealistic
- Bulk generation requires verbose loops with repetitive construction
- All records look identical, missing issues that varied data would catch

## Correct

```typescript
// ✅ Good: Faker generates realistic, varied test data
import { faker } from '@faker-js/faker';

// Configure faker for reproducible tests
beforeAll(() => {
  faker.seed(12345); // Same seed = same sequence of values
});

// Custom factories using faker
const UserDataFactory = {
  create(overrides: Partial<UserInput> = {}): UserInput {
    return {
      email: faker.internet.email(),
      name: faker.person.fullName(),
      phone: faker.phone.number(),
      dateOfBirth: faker.date.birthdate({ min: 18, max: 80, mode: 'age' }),
      address: {
        street: faker.location.streetAddress(),
        city: faker.location.city(),
        state: faker.location.state({ abbreviated: true }),
        zip: faker.location.zipCode(),
        country: faker.location.country()
      },
      ...overrides
    };
  },

  createMany(count: number, overrides: Partial<UserInput> = {}): UserInput[] {
    return Array.from({ length: count }, () => this.create(overrides));
  }
};

describe('UserService', () => {
  test('creates user with valid data', async () => {
    const userData = UserDataFactory.create();

    const user = await userService.create(userData);

    expect(user.id).toBeDefined();
    expect(user.email).toBe(userData.email);
  });

  test('validates unique email constraint', async () => {
    const email = faker.internet.email();
    await userService.create(UserDataFactory.create({ email }));

    await expect(
      userService.create(UserDataFactory.create({ email }))
    ).rejects.toThrow('Email already exists');
  });

  test('rejects invalid email format', async () => {
    const userData = UserDataFactory.create({
      email: faker.string.alpha(10) // No @ symbol
    });

    await expect(userService.create(userData)).rejects.toThrow('Invalid email');
  });
});

describe('BulkImport', () => {
  test('imports 100 users with varied data', async () => {
    const users = UserDataFactory.createMany(100);

    const result = await importService.bulkImport(users);

    expect(result.imported).toBe(100);
  });

  test('handles duplicate emails in batch', async () => {
    const duplicateEmail = faker.internet.email();
    const users = [
      UserDataFactory.create({ email: duplicateEmail }),
      ...UserDataFactory.createMany(8),
      UserDataFactory.create({ email: duplicateEmail })
    ];

    const result = await importService.bulkImport(users);

    expect(result.imported).toBe(9);
    expect(result.duplicates).toBe(1);
  });
});

describe('SearchService', () => {
  test('finds products by name', async () => {
    const targetName = faker.commerce.productName();
    const products = [
      ProductDataFactory.create({ name: targetName }),
      ...ProductDataFactory.createMany(9)
    ];
    await productService.bulkCreate(products);

    const results = await searchService.search(targetName.split(' ')[0]);

    expect(results).toContainEqual(
      expect.objectContaining({ name: targetName })
    );
  });
});
```

**Benefits:**
- Each test run uses different realistic data, revealing hidden edge cases
- Random data avoids author bias and catches issues static data misses
- Locale-specific data generation supports internationalization testing
- Bulk record creation is a single line with `createMany`
- Seeded faker produces consistent results for reproducible tests
- Combining faker with factories gives the best of both approaches

Reference: [Faker.js Documentation](https://fakerjs.dev/)


---

## Minimal Test Data

**Impact: HIGH (test clarity and focus)**

Use only the data necessary to test the specific behavior being verified. If removing data does not break the test, remove it. Use factories with defaults for required but irrelevant fields.

## Incorrect

```typescript
// ❌ Bad: Excessive data obscures what's actually being tested
describe('EmailValidator', () => {
  test('validates email format', () => {
    // Most of this data is irrelevant to email validation
    const user = {
      id: 'user-123',
      firstName: 'John',
      lastName: 'Doe',
      email: 'john.doe@example.com',
      phone: '+1-555-123-4567',
      dateOfBirth: new Date('1990-05-15'),
      address: {
        street: '123 Main St',
        city: 'Springfield',
        state: 'IL',
        zip: '62701',
        country: 'USA'
      },
      preferences: {
        newsletter: true,
        notifications: { email: true, sms: false, push: true },
        theme: 'dark',
        language: 'en-US'
      },
      subscription: {
        plan: 'premium',
        startDate: new Date('2023-01-01'),
        renewalDate: new Date('2024-01-01'),
        features: ['analytics', 'priority-support', 'api-access']
      },
      createdAt: new Date('2022-06-15'),
      updatedAt: new Date('2023-12-01'),
      lastLoginAt: new Date('2024-01-10')
    };

    expect(emailValidator.isValid(user.email)).toBe(true);
  });
});

describe('DiscountCalculator', () => {
  test('applies percentage discount', () => {
    // Excessive item details when only price matters
    const items = [
      {
        id: 'item-001',
        sku: 'WIDGET-BLU-LRG',
        name: 'Large Blue Widget',
        description: 'A high-quality widget in blue color, large size',
        category: 'widgets',
        subcategory: 'colored-widgets',
        brand: 'WidgetCo',
        manufacturer: 'WidgetCo Inc.',
        price: 100,
        cost: 45,
        weight: 2.5,
        dimensions: { length: 10, width: 8, height: 5 },
        images: ['widget-1.jpg', 'widget-2.jpg'],
        tags: ['popular', 'bestseller'],
        inventory: { available: 150, reserved: 10, warehouse: 'A1' },
        rating: { average: 4.5, count: 234 }
      }
    ];

    const total = calculator.applyDiscount(items, 10);
    expect(total).toBe(90);
  });
});
```

**Problems:**
- Walls of irrelevant data make it impossible to see what matters
- Readers waste time determining which properties affect the behavior
- More data means more things to update when schemas change
- Excessive memory allocation slows test execution

## Correct

```typescript
// ✅ Good: Minimal data focuses attention on what matters
describe('EmailValidator', () => {
  test('returns true for valid email format', () => {
    const email = 'user@example.com';

    expect(emailValidator.isValid(email)).toBe(true);
  });

  test('returns false for email without domain', () => {
    const email = 'user@';

    expect(emailValidator.isValid(email)).toBe(false);
  });

  test('returns false for email without @ symbol', () => {
    const email = 'userexample.com';

    expect(emailValidator.isValid(email)).toBe(false);
  });
});

describe('DiscountCalculator', () => {
  test('applies percentage discount to item price', () => {
    const items = [{ price: 100 }];
    const discountPercent = 10;

    const total = calculator.applyDiscount(items, discountPercent);

    expect(total).toBe(90);
  });

  test('applies discount to multiple items', () => {
    const items = [
      { price: 100 },
      { price: 50 }
    ];

    const total = calculator.applyDiscount(items, 20);

    expect(total).toBe(120); // 150 - 20%
  });
});

// When more context is needed, use only relevant properties
describe('ShippingCalculator', () => {
  test('calculates shipping based on weight', () => {
    const item = {
      weight: 2.5,
      price: 100
    };
    const destination = { country: 'USA' };

    const shipping = calculator.calculate(item, destination);

    expect(shipping).toBe(12.50);
  });
});

// For complex objects, use factories with minimal overrides
describe('OrderProcessor', () => {
  test('rejects order with invalid status', () => {
    const order = OrderFactory.create({
      status: 'cancelled' // Only the property that matters
    });

    expect(() => processor.process(order)).toThrow(InvalidStatusError);
  });

  test('processes pending order', () => {
    const order = OrderFactory.create({
      status: 'pending'
    });

    const result = processor.process(order);

    expect(result.status).toBe('processing');
  });
});
```

**Benefits:**
- Immediately obvious which data affects the behavior under test
- Less data to update when requirements change
- Tests clearly document which inputs matter for each scenario
- Less memory allocation and faster test execution
- Irrelevant data does not distract readers or reviewers

Reference: [xUnit Patterns — Minimal Fixture](http://xunitpatterns.com/Minimal%20Fixture.html)


---

## Realistic Test Data

**Impact: HIGH (edge case discovery and validation accuracy)**

Use data that resembles real-world inputs to catch edge cases and validation issues. Include international characters, actual format patterns, realistic amounts, and known test values like Stripe test card numbers.

## Incorrect

```typescript
// ❌ Bad: Unrealistic placeholder data misses real-world issues
describe('UserRegistration', () => {
  test('registers new user', async () => {
    const result = await register({
      name: 'test',
      email: 'test@test.com',
      password: '123',
      phone: '123',
      address: 'test'
    });

    expect(result.success).toBe(true);
  });
});

describe('PaymentProcessor', () => {
  test('processes payment', async () => {
    const result = await processPayment({
      cardNumber: '1234',
      expiry: '12/24',
      cvv: '123',
      amount: 1
    });

    // Might pass with mock but fails with real validation
    expect(result.status).toBe('success');
  });
});

describe('InternationalizationService', () => {
  test('formats user name', () => {
    // Only tests ASCII names
    const formatted = formatName('John Doe');

    expect(formatted).toBe('Doe, John');
  });
});
```

**Problems:**
- Placeholder values like `test` and `123` do not exercise real validation rules
- Card number `1234` is not a valid test card format
- Only ASCII names are tested, missing encoding and formatting issues
- Trivially simple data misses edge cases found in production

## Correct

```typescript
// ✅ Good: Realistic data catches real-world edge cases
describe('UserRegistration', () => {
  test('registers user with common name format', async () => {
    const result = await register({
      name: 'Sarah Johnson',
      email: 'sarah.johnson@gmail.com',
      password: 'SecureP@ss123!',
      phone: '+1-555-867-5309',
      address: '742 Evergreen Terrace, Springfield, IL 62701'
    });

    expect(result.success).toBe(true);
  });

  test('registers user with hyphenated name', async () => {
    const result = await register({
      name: 'Mary-Jane Watson-Parker',
      email: 'mj.watson@example.com',
      password: 'WebSl!ng3r2024'
    });

    expect(result.success).toBe(true);
  });

  test('registers user with international characters', async () => {
    const result = await register({
      name: 'Jose Garcia-Lopez',
      email: 'jose.garcia@empresa.es',
      password: 'Contrasena123!'
    });

    expect(result.success).toBe(true);
  });

  test('registers user with unicode name', async () => {
    const result = await register({
      name: 'Tanaka Taro',
      email: 'tanaka.taro@example.jp',
      password: 'Password123!'
    });

    expect(result.success).toBe(true);
  });
});

describe('PaymentProcessor', () => {
  // Using realistic test card numbers (Stripe test cards)
  const testCards = {
    visa: '4242424242424242',
    mastercard: '5555555555554444',
    amex: '378282246310005',
    declined: '4000000000000002'
  };

  test('processes Visa payment', async () => {
    const result = await processPayment({
      cardNumber: testCards.visa,
      expiry: '12/28',
      cvv: '123',
      amount: 49.99,
      currency: 'USD'
    });

    expect(result.status).toBe('success');
    expect(result.last4).toBe('4242');
  });

  test('handles declined card', async () => {
    const result = await processPayment({
      cardNumber: testCards.declined,
      expiry: '12/28',
      cvv: '123',
      amount: 99.99
    });

    expect(result.status).toBe('declined');
    expect(result.error).toContain('insufficient funds');
  });

  test('processes realistic order amount', async () => {
    const result = await processPayment({
      cardNumber: testCards.visa,
      expiry: '12/28',
      cvv: '123',
      amount: 147.83
    });

    expect(result.amount).toBe(147.83);
  });
});

describe('AddressFormatter', () => {
  test('formats US address', () => {
    const formatted = formatAddress({
      street: '1600 Pennsylvania Avenue NW',
      city: 'Washington',
      state: 'DC',
      zip: '20500',
      country: 'USA'
    });

    expect(formatted).toBe('1600 Pennsylvania Avenue NW\nWashington, DC 20500\nUSA');
  });

  test('formats UK address with postcode', () => {
    const formatted = formatAddress({
      street: '221B Baker Street',
      city: 'London',
      postcode: 'NW1 6XE',
      country: 'UK'
    });

    expect(formatted).toContain('NW1 6XE');
  });

  test('formats address with apartment number', () => {
    const formatted = formatAddress({
      street: '350 Fifth Avenue',
      unit: 'Floor 102',
      city: 'New York',
      state: 'NY',
      zip: '10118'
    });

    expect(formatted).toContain('Floor 102');
  });
});

describe('SearchService', () => {
  test('handles search with special characters', async () => {
    const results = await search("kid's toys");

    expect(results).toBeDefined();
  });

  test('handles search with typos', async () => {
    const results = await search('wireles headphone');

    expect(results.length).toBeGreaterThan(0);
  });
});
```

**Benefits:**
- Real-world data reveals edge cases that placeholder data misses
- Validation rules are tested with actual format patterns
- International characters catch encoding and display issues
- Tests document examples of valid real-world inputs
- Tests are more likely to reflect production behavior

Reference: [Stripe Testing — Test Card Numbers](https://docs.stripe.com/testing)


---

## Test Fixtures

**Impact: HIGH (test data reusability and consistency)**

Use fixtures to manage reusable test data and complex setup scenarios. Organize fixtures by domain or entity, include expected values alongside input data, and create fixture loaders for database setup.

## Incorrect

```typescript
// ❌ Bad: Repeated inline data setup across tests
describe('ReportService', () => {
  test('generates sales report', async () => {
    // Setup data inline — duplicated across tests
    const salesData = [
      { date: '2024-01-01', product: 'Widget', quantity: 10, price: 29.99 },
      { date: '2024-01-01', product: 'Gadget', quantity: 5, price: 49.99 },
      { date: '2024-01-02', product: 'Widget', quantity: 15, price: 29.99 },
      { date: '2024-01-02', product: 'Gadget', quantity: 8, price: 49.99 },
      // ... 50 more lines of data
    ];

    await db.insert('sales', salesData);

    const report = await reportService.generate('sales', { month: 'january' });

    expect(report.totalRevenue).toBe(1799.55);
  });

  test('filters report by product', async () => {
    // Same data duplicated again
    const salesData = [
      { date: '2024-01-01', product: 'Widget', quantity: 10, price: 29.99 },
      // ... same 50+ lines
    ];

    await db.insert('sales', salesData);

    const report = await reportService.generate('sales', {
      month: 'january',
      product: 'Widget'
    });

    expect(report.items).toHaveLength(2);
  });
});
```

**Problems:**
- Large blocks of inline data are duplicated across multiple tests
- Expected values are hardcoded without connection to the fixture data
- Database setup logic is repeated in every test
- Changes to test data require updates in many places

## Correct

```typescript
// ✅ Good: Fixtures organized by domain with expected values
// fixtures/sales.fixture.ts
export const salesFixtures = {
  january2024: [
    { date: '2024-01-01', product: 'Widget', quantity: 10, price: 29.99 },
    { date: '2024-01-01', product: 'Gadget', quantity: 5, price: 49.99 },
    { date: '2024-01-02', product: 'Widget', quantity: 15, price: 29.99 },
    { date: '2024-01-02', product: 'Gadget', quantity: 8, price: 49.99 }
  ],

  january2024Expected: {
    totalRevenue: 1099.55,
    widgetRevenue: 749.75,
    gadgetRevenue: 349.80,
    widgetCount: 25,
    gadgetCount: 13
  }
};

// fixtures/users.fixture.ts
export const userFixtures = {
  admin: {
    id: 'user-admin',
    email: 'admin@example.com',
    role: 'admin',
    permissions: ['read', 'write', 'delete', 'admin']
  },

  regularUser: {
    id: 'user-regular',
    email: 'user@example.com',
    role: 'user',
    permissions: ['read']
  },

  premiumUser: {
    id: 'user-premium',
    email: 'premium@example.com',
    role: 'user',
    subscription: 'premium',
    permissions: ['read', 'write', 'export']
  }
};

// helpers/fixture-loader.ts
export class FixtureLoader {
  constructor(private db: Database) {}

  async loadSales(fixture: typeof salesFixtures.january2024): Promise<void> {
    await this.db.insert('sales', fixture);
  }

  async loadUsers(...users: Array<typeof userFixtures.admin>): Promise<void> {
    await this.db.insert('users', users);
  }

  async clearAll(): Promise<void> {
    await this.db.truncate(['sales', 'users', 'products']);
  }
}

// Clean tests using fixtures
describe('ReportService', () => {
  let fixtureLoader: FixtureLoader;

  beforeAll(() => {
    fixtureLoader = new FixtureLoader(db);
  });

  beforeEach(async () => {
    await fixtureLoader.clearAll();
  });

  describe('sales reports', () => {
    beforeEach(async () => {
      await fixtureLoader.loadSales(salesFixtures.january2024);
    });

    test('calculates total revenue for month', async () => {
      const report = await reportService.generate('sales', { month: 'january' });

      expect(report.totalRevenue).toBe(
        salesFixtures.january2024Expected.totalRevenue
      );
    });

    test('filters by product', async () => {
      const report = await reportService.generate('sales', {
        month: 'january',
        product: 'Widget'
      });

      expect(report.totalRevenue).toBe(
        salesFixtures.january2024Expected.widgetRevenue
      );
    });
  });
});

describe('AuthorizationService', () => {
  beforeEach(async () => {
    await fixtureLoader.clearAll();
  });

  test('admin can delete resources', async () => {
    await fixtureLoader.loadUsers(userFixtures.admin);

    const canDelete = await authService.checkPermission(
      userFixtures.admin.id,
      'delete'
    );

    expect(canDelete).toBe(true);
  });

  test('regular user cannot delete resources', async () => {
    await fixtureLoader.loadUsers(userFixtures.regularUser);

    const canDelete = await authService.checkPermission(
      userFixtures.regularUser.id,
      'delete'
    );

    expect(canDelete).toBe(false);
  });
});
```

**Benefits:**
- Same data is reused across multiple tests without duplication
- All tests use identical baseline data for consistency
- Test data is updated in one place when requirements change
- Fixtures describe test scenarios and serve as documentation
- Pre-calculated expected values stored alongside input data prevent drift
- Test logic is cleanly separated from test data

Reference: [Django Docs — Fixtures](https://docs.djangoproject.com/en/stable/howto/initial-data/)


---

## Mock Only at Boundaries

**Impact: MEDIUM (reduces brittle tests by 60%, improves refactoring confidence)**

Mock external dependencies at system boundaries — APIs, databases, file system — but keep business logic unmocked so tests verify real behavior.

## Incorrect

```typescript
// ❌ Bad: mocking internal functions and private methods
import { calculateDiscount } from './pricing';
import { formatCurrency } from './utils';

vi.mock('./pricing', () => ({
  calculateDiscount: vi.fn().mockReturnValue(10),
}));

vi.mock('./utils', () => ({
  formatCurrency: vi.fn().mockReturnValue('$90.00'),
}));

describe('OrderService', () => {
  test('applies discount to order', () => {
    const order = new OrderService();
    const result = order.processOrder({ price: 100, discountCode: 'SAVE10' });

    // Testing wiring, not behavior
    expect(calculateDiscount).toHaveBeenCalledWith('SAVE10', 100);
    expect(formatCurrency).toHaveBeenCalledWith(90);
    expect(result.formattedTotal).toBe('$90.00');
  });
});
```

**Problems:**
- Mocking internal utility functions couples tests to implementation details
- Refactoring internals (e.g., inlining `formatCurrency`) breaks the test even if behavior is unchanged
- Tests verify wiring, not actual business logic
- False confidence — mocks return what you told them to, not what the real code does

## Correct

```typescript
// ✅ Good: mock only external boundaries, test real business logic
import { OrderService } from './order-service';

// Mock the external HTTP client (boundary)
const mockPaymentGateway = {
  charge: vi.fn(),
};

// Mock the external database (boundary)
const mockOrderRepository = {
  save: vi.fn(),
};

describe('OrderService', () => {
  const service = new OrderService(mockPaymentGateway, mockOrderRepository);

  beforeEach(() => {
    vi.clearAllMocks();
  });

  test('applies discount and charges correct amount', async () => {
    mockPaymentGateway.charge.mockResolvedValue({ id: 'txn_123', status: 'success' });
    mockOrderRepository.save.mockResolvedValue({ id: 'order_456' });

    const result = await service.processOrder({
      price: 100,
      discountCode: 'SAVE10',
    });

    // Real discount calculation and formatting run — only external calls are mocked
    expect(result.total).toBe(90);
    expect(result.formattedTotal).toBe('$90.00');
    expect(mockPaymentGateway.charge).toHaveBeenCalledWith(90);
  });

  test('rejects order when payment fails', async () => {
    mockPaymentGateway.charge.mockRejectedValue(new Error('Card declined'));

    await expect(
      service.processOrder({ price: 50, discountCode: '' })
    ).rejects.toThrow('Card declined');

    expect(mockOrderRepository.save).not.toHaveBeenCalled();
  });
});
```

**Benefits:**
- Business logic (discount calculation, formatting) runs for real and is actually tested
- Tests survive internal refactoring — only boundary contracts matter
- Failures reveal genuine bugs, not outdated mock wiring
- External side effects (payments, persistence) are safely isolated

Reference: [Mock Only What You Own](https://testing-library.com/docs/guiding-principles)


---

## Verify Important Mock Interactions

**Impact: MEDIUM (catches missing or incorrect side effects in 40% more cases)**

When you set up mocks, always assert they were called with the right arguments, the right number of times, and in the right order when it matters.

## Incorrect

```typescript
// ❌ Bad: mocks set up but never verified
describe('RegistrationService', () => {
  test('registers a new user', async () => {
    const mockSendEmail = vi.fn();
    const mockSaveUser = vi.fn().mockResolvedValue({ id: 'user_1' });
    const mockAuditLog = vi.fn();

    const service = new RegistrationService(mockSaveUser, mockSendEmail, mockAuditLog);
    const result = await service.register({
      name: 'Alice',
      email: 'alice@example.com',
    });

    // Only checks the return value — never verifies side effects
    expect(result.id).toBe('user_1');
    // Was the welcome email sent? To the right address? With the right content?
    // Was the audit log written? Nobody knows.
  });
});
```

**Problems:**
- Welcome email could silently stop sending and the test still passes
- Audit log could receive wrong data without detection
- Side effects are the most important thing to verify in service-layer tests
- False green — the test gives confidence it hasn't earned

## Correct

```typescript
// ✅ Good: verify every important mock interaction
describe('RegistrationService', () => {
  const mockSendEmail = vi.fn();
  const mockSaveUser = vi.fn();
  const mockAuditLog = vi.fn();

  beforeEach(() => {
    vi.clearAllMocks();
    mockSaveUser.mockResolvedValue({ id: 'user_1', name: 'Alice', email: 'alice@example.com' });
    mockSendEmail.mockResolvedValue(undefined);
  });

  test('saves user and sends welcome email with correct content', async () => {
    const service = new RegistrationService(mockSaveUser, mockSendEmail, mockAuditLog);

    await service.register({ name: 'Alice', email: 'alice@example.com' });

    // Verify user was saved with correct data
    expect(mockSaveUser).toHaveBeenCalledTimes(1);
    expect(mockSaveUser).toHaveBeenCalledWith({
      name: 'Alice',
      email: 'alice@example.com',
    });

    // Verify welcome email sent to the right address with expected content
    expect(mockSendEmail).toHaveBeenCalledTimes(1);
    expect(mockSendEmail).toHaveBeenCalledWith(
      'alice@example.com',
      expect.stringContaining('Welcome'),
    );

    // Verify audit log records the registration event
    expect(mockAuditLog).toHaveBeenCalledWith(
      expect.objectContaining({
        action: 'USER_REGISTERED',
        userId: 'user_1',
      }),
    );
  });

  test('does not send email when save fails', async () => {
    mockSaveUser.mockRejectedValue(new Error('DB error'));
    const service = new RegistrationService(mockSaveUser, mockSendEmail, mockAuditLog);

    await expect(
      service.register({ name: 'Alice', email: 'alice@example.com' })
    ).rejects.toThrow('DB error');

    // Verify no email was sent after a failed save
    expect(mockSendEmail).not.toHaveBeenCalled();
    expect(mockAuditLog).toHaveBeenLastCalledWith(
      expect.objectContaining({ action: 'REGISTRATION_FAILED' }),
    );
  });
});
```

**Benefits:**
- Every important side effect is explicitly verified
- `toHaveBeenCalledTimes` catches duplicate calls (e.g., double-sending emails)
- `toHaveBeenCalledWith` ensures correct arguments reach dependencies
- `toHaveBeenLastCalledWith` verifies the final state when order matters
- `not.toHaveBeenCalled()` confirms side effects are skipped on error paths

Reference: [Jest Mock Functions](https://jestjs.io/docs/mock-function-api)


---

## Don't Over-Mock

**Impact: MEDIUM (prevents 50% of false-green tests caused by testing implementation details)**

Only mock things that are slow, unpredictable, or external. Use real implementations for everything else so refactoring internals doesn't break tests.

## Incorrect

```typescript
// ❌ Bad: mocking every dependency, including simple utilities
import { OrderProcessor } from './order-processor';

vi.mock('./tax-calculator', () => ({
  calculateTax: vi.fn().mockReturnValue(8.5),
}));

vi.mock('./discount-engine', () => ({
  applyDiscount: vi.fn().mockReturnValue(85),
}));

vi.mock('./validators', () => ({
  validateOrder: vi.fn().mockReturnValue(true),
}));

vi.mock('./formatters', () => ({
  formatReceipt: vi.fn().mockReturnValue('Receipt: $93.50'),
}));

describe('OrderProcessor', () => {
  test('processes a valid order', () => {
    const processor = new OrderProcessor();
    const result = processor.process({ items: [{ price: 100 }], discountCode: 'SAVE15' });

    // These tests pass even if the real logic is completely broken
    expect(result.tax).toBe(8.5);
    expect(result.total).toBe(93.5);
    expect(result.receipt).toBe('Receipt: $93.50');
  });

  // After refactoring: merge tax-calculator into discount-engine
  // Result: test breaks because vi.mock('./tax-calculator') fails
  // The behavior is identical, but the test is coupled to file structure
});
```

**Problems:**
- Tests verify mock return values, not real behavior — they always pass regardless of bugs
- Refactoring internal modules (renaming, merging, splitting files) breaks tests
- Every mock is a point where the test diverges from production behavior
- Maintenance burden grows with each unnecessary mock

## Correct

```typescript
// ✅ Good: use real implementations, only mock the external payment API
import { OrderProcessor } from './order-processor';
import { createMockPaymentClient } from '../test-helpers/mock-payment';

describe('OrderProcessor', () => {
  // Only the external payment gateway is mocked
  const mockPaymentClient = createMockPaymentClient();
  const processor = new OrderProcessor(mockPaymentClient);

  test('calculates correct total with tax and discount', () => {
    const result = processor.process({
      items: [{ name: 'Widget', price: 100 }],
      discountCode: 'SAVE15',
      taxRate: 0.085,
    });

    // Real tax calculator, real discount engine, real formatter
    expect(result.subtotal).toBe(100);
    expect(result.discount).toBe(15);
    expect(result.tax).toBe(7.23); // tax on discounted price
    expect(result.total).toBe(92.23);
    expect(result.receipt).toContain('$92.23');
  });

  test('charges payment gateway with final amount', async () => {
    mockPaymentClient.charge.mockResolvedValue({ id: 'txn_1', status: 'paid' });

    const result = await processor.processAndPay({
      items: [{ name: 'Widget', price: 100 }],
      discountCode: 'SAVE15',
      taxRate: 0.085,
    });

    // Only the boundary interaction is verified via mock
    expect(mockPaymentClient.charge).toHaveBeenCalledWith(92.23);
    expect(result.transactionId).toBe('txn_1');
  });

  // After refactoring: merge tax-calculator into discount-engine
  // Result: test still passes because it tests behavior, not file structure
});
```

**Benefits:**
- Real business logic executes, so bugs are caught immediately
- Refactoring internals (renaming files, extracting functions) never breaks tests
- Only one mock to maintain instead of four
- Tests act as a safety net during refactoring, not a barrier

Reference: [Testing Implementation Details](https://kentcdodds.com/blog/testing-implementation-details)


---

## Use Realistic Mock Behavior

**Impact: MEDIUM (catches 30% more integration bugs by simulating real API behavior)**

Mocks should behave like the real dependency — return realistic data structures, simulate error responses, and respect the contract of the thing they replace.

## Incorrect

```typescript
// ❌ Bad: mocks that always succeed with minimal data
const mockApi = {
  getUser: vi.fn().mockResolvedValue({ name: 'test' }),
  createOrder: vi.fn().mockResolvedValue(true),
  fetchProducts: vi.fn().mockResolvedValue([]),
};

describe('Dashboard', () => {
  test('loads user data', async () => {
    const dashboard = new DashboardService(mockApi);
    const data = await dashboard.load('user_1');

    // Mock returns { name: 'test' } — missing id, email, role, createdAt
    // Real API returns a full user object; code accessing user.role will crash in production
    expect(data.user.name).toBe('test');
  });

  test('creates an order', async () => {
    const dashboard = new DashboardService(mockApi);
    const result = await dashboard.placeOrder({ productId: 'p1', quantity: 2 });

    // Mock returns `true` — real API returns { orderId, status, estimatedDelivery }
    // No error path tested at all
    expect(result).toBe(true);
  });
});
```

**Problems:**
- Incomplete mock responses hide runtime errors when real fields are accessed
- Never testing error paths means error handling code is unverified
- `mockResolvedValue(true)` doesn't match the real API contract
- Tests pass in CI but the feature breaks in production

## Correct

```typescript
// ✅ Good: realistic mocks with MSW for API simulation
import { http, HttpResponse } from 'msw';
import { setupServer } from 'msw/node';

// Realistic response shapes matching the actual API contract
const handlers = [
  http.get('/api/users/:id', ({ params }) => {
    if (params.id === 'not_found') {
      return HttpResponse.json(
        { error: 'User not found', code: 'USER_NOT_FOUND' },
        { status: 404 },
      );
    }

    return HttpResponse.json({
      id: params.id,
      name: 'Alice Johnson',
      email: 'alice@example.com',
      role: 'admin',
      createdAt: '2024-01-15T10:30:00Z',
    });
  }),

  http.post('/api/orders', async ({ request }) => {
    const body = (await request.json()) as Record<string, unknown>;

    if (!body.productId) {
      return HttpResponse.json(
        { error: 'Product ID is required', code: 'VALIDATION_ERROR' },
        { status: 400 },
      );
    }

    return HttpResponse.json(
      {
        orderId: 'order_789',
        status: 'confirmed',
        estimatedDelivery: '2024-02-01',
        items: [{ productId: body.productId, quantity: body.quantity }],
      },
      { status: 201 },
    );
  }),
];

const server = setupServer(...handlers);

beforeAll(() => server.listen());
afterEach(() => server.resetHandlers());
afterAll(() => server.close());

describe('DashboardService', () => {
  const service = new DashboardService();

  test('loads complete user profile', async () => {
    const data = await service.load('user_1');

    expect(data.user).toMatchObject({
      id: 'user_1',
      name: expect.any(String),
      email: expect.stringContaining('@'),
      role: expect.any(String),
    });
  });

  test('handles user not found gracefully', async () => {
    const data = await service.load('not_found');

    expect(data.user).toBeNull();
    expect(data.error).toBe('User not found');
  });

  test('creates order with full response data', async () => {
    const result = await service.placeOrder({ productId: 'p1', quantity: 2 });

    expect(result).toMatchObject({
      orderId: expect.stringMatching(/^order_/),
      status: 'confirmed',
      estimatedDelivery: expect.any(String),
    });
  });

  test('rejects order with missing product ID', async () => {
    await expect(
      service.placeOrder({ productId: '', quantity: 1 })
    ).rejects.toThrow('Product ID is required');
  });
});
```

**Benefits:**
- MSW intercepts real fetch/axios calls — no manual mock wiring needed
- Response shapes match the actual API contract, catching field-access bugs early
- Both success and error paths are tested with realistic status codes
- Tests run against the same HTTP layer used in production

Reference: [Mock Service Worker](https://mswjs.io/docs/getting-started)


---

## Focus on Meaningful Coverage

**Impact: MEDIUM (eliminates wasted test effort on trivial code, focuses on high-risk logic)**

Write tests that verify business logic, decision branches, and error handling. Use coverage as a guide to find untested areas, not as a score to maximize.

## Incorrect

```typescript
// ❌ Bad: testing getters/setters and framework boilerplate to inflate coverage
interface UserDTO {
  id: string;
  name: string;
  email: string;
}

class User {
  constructor(
    public id: string,
    public name: string,
    public email: string,
  ) {}

  getId(): string { return this.id; }
  getName(): string { return this.name; }
  getEmail(): string { return this.email; }
  setName(name: string): void { this.name = name; }
  setEmail(email: string): void { this.email = email; }
}

describe('User', () => {
  // These tests add coverage but verify nothing meaningful
  test('getId returns id', () => {
    const user = new User('1', 'Alice', 'alice@example.com');
    expect(user.getId()).toBe('1');
  });

  test('getName returns name', () => {
    const user = new User('1', 'Alice', 'alice@example.com');
    expect(user.getName()).toBe('Alice');
  });

  test('setName sets name', () => {
    const user = new User('1', 'Alice', 'alice@example.com');
    user.setName('Bob');
    expect(user.getName()).toBe('Bob');
  });

  test('setEmail sets email', () => {
    const user = new User('1', 'Alice', 'alice@example.com');
    user.setEmail('bob@example.com');
    expect(user.getEmail()).toBe('bob@example.com');
  });
});
```

**Problems:**
- Testing trivial getters/setters wastes time and clutters the test suite
- Coverage percentage increases but confidence in the system does not
- Critical business logic (pricing, permissions, validation) remains untested
- Creates a false sense of security — 90% coverage with zero meaningful tests

## Correct

```typescript
// ✅ Good: test the logic that actually matters
describe('PricingEngine', () => {
  test('applies tiered discount for bulk orders', () => {
    const engine = new PricingEngine();

    // Business rule: 10+ items get 10% off, 50+ get 20% off
    expect(engine.calculateTotal(9, 100)).toBe(900);
    expect(engine.calculateTotal(10, 100)).toBe(900);  // 10% discount kicks in
    expect(engine.calculateTotal(50, 100)).toBe(4000); // 20% discount kicks in
  });

  test('never discounts below cost price', () => {
    const engine = new PricingEngine();

    // Critical invariant: price * quantity should never go below cost
    const result = engine.calculateTotal(100, 10, { costPerUnit: 8 });
    expect(result).toBeGreaterThanOrEqual(800);
  });

  test('rejects negative quantities', () => {
    const engine = new PricingEngine();

    expect(() => engine.calculateTotal(-1, 100)).toThrow('Quantity must be positive');
  });
});

describe('PermissionService', () => {
  test('admin can delete any resource', () => {
    const service = new PermissionService();

    expect(service.canDelete({ role: 'admin' }, { ownerId: 'other_user' })).toBe(true);
  });

  test('regular user can only delete own resources', () => {
    const service = new PermissionService();

    expect(service.canDelete({ role: 'user', id: 'u1' }, { ownerId: 'u1' })).toBe(true);
    expect(service.canDelete({ role: 'user', id: 'u1' }, { ownerId: 'u2' })).toBe(false);
  });

  test('suspended user cannot delete anything', () => {
    const service = new PermissionService();

    expect(service.canDelete({ role: 'user', id: 'u1', suspended: true }, { ownerId: 'u1' })).toBe(false);
  });
});
```

**Benefits:**
- Every test verifies a business rule or decision branch that could break in production
- Coverage reflects real risk areas, not trivial code
- Tests serve as documentation for important domain rules
- Refactoring getters/setters or data structures won't break meaningful tests

Reference: [Write Tests, Not Too Many, Mostly Integration](https://kentcdodds.com/blog/write-tests)


---

## Cover Edge Cases

**Impact: MEDIUM (catches 35% of production bugs caused by unexpected input values)**

Test boundary values, empty inputs, null/undefined, and special characters. Most production bugs live at the edges, not in the happy path.

## Incorrect

```typescript
// ❌ Bad: only testing the happy path
describe('StringUtils', () => {
  test('truncates a long string', () => {
    expect(truncate('Hello World', 5)).toBe('He...');
  });
});

describe('ArrayUtils', () => {
  test('finds the maximum value', () => {
    expect(findMax([3, 1, 4, 1, 5])).toBe(5);
  });
});

describe('UserValidator', () => {
  test('validates a correct email', () => {
    expect(validateUser({ name: 'Alice', email: 'alice@example.com', age: 25 })).toBe(true);
  });
});
```

**Problems:**
- What happens when `truncate` receives an empty string? A string shorter than the limit?
- What does `findMax` return for an empty array? An array with one element? Negative numbers?
- What if `age` is 0, -1, or `Number.MAX_SAFE_INTEGER`?
- Production crashes from unhandled edge cases that happy-path tests never exercise

## Correct

```typescript
// ✅ Good: systematically test edge cases and boundary values
describe('StringUtils - truncate', () => {
  test('truncates string longer than limit', () => {
    expect(truncate('Hello World', 5)).toBe('He...');
  });

  test('returns original string when shorter than limit', () => {
    expect(truncate('Hi', 10)).toBe('Hi');
  });

  test('returns original string when exactly at limit', () => {
    expect(truncate('Hello', 5)).toBe('Hello');
  });

  test('handles empty string', () => {
    expect(truncate('', 5)).toBe('');
  });

  test('handles limit of zero', () => {
    expect(truncate('Hello', 0)).toBe('...');
  });

  test('handles special characters and unicode', () => {
    expect(truncate('Hello! @#$%', 7)).toBe('Hell...');
  });

  test('handles null input gracefully', () => {
    expect(truncate(null as unknown as string, 5)).toBe('');
  });
});

describe('ArrayUtils - findMax', () => {
  test('finds max in a normal array', () => {
    expect(findMax([3, 1, 4, 1, 5])).toBe(5);
  });

  test('handles single-element array', () => {
    expect(findMax([42])).toBe(42);
  });

  test('throws for empty array', () => {
    expect(() => findMax([])).toThrow('Array must not be empty');
  });

  test('handles negative numbers', () => {
    expect(findMax([-5, -1, -3])).toBe(-1);
  });

  test('handles zero', () => {
    expect(findMax([0, -1, -2])).toBe(0);
  });

  test('handles duplicate max values', () => {
    expect(findMax([5, 5, 3, 5])).toBe(5);
  });

  test('handles Number.MAX_SAFE_INTEGER', () => {
    expect(findMax([1, Number.MAX_SAFE_INTEGER, 3])).toBe(Number.MAX_SAFE_INTEGER);
  });
});

describe('UserValidator - edge cases', () => {
  test('rejects empty name', () => {
    expect(validateUser({ name: '', email: 'a@b.com', age: 25 })).toBe(false);
  });

  test('rejects undefined email', () => {
    expect(validateUser({ name: 'Alice', email: undefined as unknown as string, age: 25 })).toBe(false);
  });

  test('rejects age of zero', () => {
    expect(validateUser({ name: 'Alice', email: 'a@b.com', age: 0 })).toBe(false);
  });

  test('rejects negative age', () => {
    expect(validateUser({ name: 'Alice', email: 'a@b.com', age: -1 })).toBe(false);
  });

  test('handles name with special characters', () => {
    expect(validateUser({ name: "O'Brien-Smith", email: 'a@b.com', age: 30 })).toBe(true);
  });

  test('rejects age exceeding reasonable maximum', () => {
    expect(validateUser({ name: 'Alice', email: 'a@b.com', age: 200 })).toBe(false);
  });
});
```

**Benefits:**
- Boundary values (0, -1, empty, max) catch off-by-one errors and unguarded code paths
- Null/undefined tests verify defensive coding works in practice
- Special character tests prevent encoding and parsing bugs
- Each edge case documents an assumption the code makes about its inputs

Reference: [Boundary Value Analysis](https://en.wikipedia.org/wiki/Boundary-value_analysis)


---

## Test Error Scenarios

**Impact: MEDIUM (prevents silent failures and unhandled exceptions in production)**

Test thrown exceptions, rejected promises, validation failures, network errors, and timeouts. Error handling code is only reliable if it is exercised by tests.

## Incorrect

```typescript
// ❌ Bad: only testing success scenarios
describe('PaymentService', () => {
  test('processes payment successfully', async () => {
    const service = new PaymentService(mockGateway);
    mockGateway.charge.mockResolvedValue({ id: 'txn_1', status: 'paid' });

    const result = await service.processPayment({
      amount: 99.99,
      cardToken: 'tok_valid',
    });

    expect(result.status).toBe('paid');
  });
});

describe('FileParser', () => {
  test('parses valid JSON file', () => {
    const data = parseConfigFile('{"port": 3000}');
    expect(data.port).toBe(3000);
  });
});

// What happens when the card is declined?
// What happens when the network times out?
// What happens when the JSON is malformed?
// Nobody knows — these paths are untested.
```

**Problems:**
- Error handling code never executes during tests, so bugs in it go unnoticed
- Production users hit unhandled exceptions that crash the process
- Silent failures (swallowed errors, missing error messages) slip through
- No confidence that the system degrades gracefully under failure

## Correct

```typescript
// ✅ Good: test every error scenario the code handles
describe('PaymentService', () => {
  const mockGateway = { charge: vi.fn() };
  const service = new PaymentService(mockGateway);

  test('processes payment successfully', async () => {
    mockGateway.charge.mockResolvedValue({ id: 'txn_1', status: 'paid' });

    const result = await service.processPayment({
      amount: 99.99,
      cardToken: 'tok_valid',
    });

    expect(result.status).toBe('paid');
  });

  test('throws PaymentDeclinedError when card is declined', async () => {
    mockGateway.charge.mockRejectedValue(new Error('Card declined'));

    await expect(
      service.processPayment({ amount: 99.99, cardToken: 'tok_declined' })
    ).rejects.toThrow('Card declined');
  });

  test('throws ValidationError for negative amount', () => {
    expect(() =>
      service.validatePayment({ amount: -10, cardToken: 'tok_valid' })
    ).toThrow('Amount must be positive');
  });

  test('throws ValidationError for zero amount', () => {
    expect(() =>
      service.validatePayment({ amount: 0, cardToken: 'tok_valid' })
    ).toThrow('Amount must be positive');
  });

  test('wraps gateway timeout in a user-friendly error', async () => {
    mockGateway.charge.mockRejectedValue(new Error('ETIMEDOUT'));

    await expect(
      service.processPayment({ amount: 50, cardToken: 'tok_valid' })
    ).rejects.toThrow('Payment service is temporarily unavailable');
  });

  test('rejects with specific error type for insufficient funds', async () => {
    mockGateway.charge.mockRejectedValue(
      Object.assign(new Error('Insufficient funds'), { code: 'insufficient_funds' })
    );

    await expect(
      service.processPayment({ amount: 9999, cardToken: 'tok_valid' })
    ).rejects.toBeInstanceOf(InsufficientFundsError);
  });
});

describe('FileParser', () => {
  test('parses valid JSON', () => {
    const data = parseConfigFile('{"port": 3000}');
    expect(data.port).toBe(3000);
  });

  test('throws descriptive error for malformed JSON', () => {
    expect(() => parseConfigFile('{invalid}')).toThrow(
      expect.objectContaining({
        message: expect.stringContaining('Invalid configuration format'),
      }),
    );
  });

  test('throws for empty input', () => {
    expect(() => parseConfigFile('')).toThrow('Configuration cannot be empty');
  });

  test('rejects config missing required fields', () => {
    expect(() => parseConfigFile('{"debug": true}')).toThrow(
      'Missing required field: port',
    );
  });
});
```

**Benefits:**
- `expect().rejects.toThrow()` verifies async error handling without try/catch boilerplate
- `expect(() => fn()).toThrow()` confirms synchronous errors are thrown correctly
- `toBeInstanceOf` ensures the right error type is used for programmatic error handling
- Timeout and network error tests verify the system degrades gracefully
- Each test documents what the user sees when something goes wrong

Reference: [Jest Expect API - Errors](https://jestjs.io/docs/expect#tothrowerror)


---

## 100% Coverage Isn't the Goal

**Impact: MEDIUM (saves 20% of test development time by avoiding diminishing-return tests)**

Aim for around 80% coverage as a baseline. Use coverage reports to find untested risk areas, not as a number to maximize at all costs.

## Incorrect

```typescript
// ❌ Bad: writing meaningless tests to hit 100% coverage
// --- src/logger.ts ---
export class Logger {
  private static instance: Logger;

  private constructor() {}

  static getInstance(): Logger {
    if (!Logger.instance) {
      Logger.instance = new Logger();
    }
    return Logger.instance;
  }

  debug(msg: string): void { console.debug(msg); }
  info(msg: string): void { console.info(msg); }
  warn(msg: string): void { console.warn(msg); }
  error(msg: string): void { console.error(msg); }
}

// --- tests written just to reach 100% ---
describe('Logger', () => {
  test('getInstance returns a Logger', () => {
    expect(Logger.getInstance()).toBeInstanceOf(Logger);
  });

  test('getInstance returns same instance', () => {
    expect(Logger.getInstance()).toBe(Logger.getInstance());
  });

  test('debug calls console.debug', () => {
    const spy = vi.spyOn(console, 'debug').mockImplementation(() => {});
    Logger.getInstance().debug('test');
    expect(spy).toHaveBeenCalledWith('test');
  });

  test('info calls console.info', () => {
    const spy = vi.spyOn(console, 'info').mockImplementation(() => {});
    Logger.getInstance().info('test');
    expect(spy).toHaveBeenCalledWith('test');
  });

  // ...repeated for warn and error — four tests that verify console wrappers
});
```

**Problems:**
- Tests verify that `console.info` is called when you call `info()` — trivially obvious
- Time spent writing and maintaining these tests could go toward testing business logic
- 100% coverage creates pressure to test every line, even generated code and framework glue
- Diminishing returns — the last 20% of coverage takes 80% of the effort

## Correct

```typescript
// ✅ Good: set reasonable thresholds and focus coverage on critical paths
// --- vitest.config.ts ---
import { defineConfig } from 'vitest/config';

export default defineConfig({
  test: {
    coverage: {
      provider: 'v8',
      reporter: ['text', 'html', 'lcov'],
      thresholds: {
        statements: 80,
        branches: 80,
        functions: 80,
        lines: 80,
      },
      exclude: [
        'node_modules/',
        'test/',
        '**/*.d.ts',
        '**/*.config.ts',
        '**/types/**',
        '**/generated/**',
      ],
    },
  },
});

// --- Focus tests on business-critical code ---
describe('InvoiceCalculator', () => {
  // This is worth testing — complex business rules with many branches
  test('applies early payment discount within 10 days', () => {
    const invoice = createInvoice({ amount: 1000, issuedAt: daysAgo(5) });
    expect(calculateTotal(invoice)).toBe(980); // 2% early discount
  });

  test('applies no discount after 10 days', () => {
    const invoice = createInvoice({ amount: 1000, issuedAt: daysAgo(15) });
    expect(calculateTotal(invoice)).toBe(1000);
  });

  test('adds late fee after 30 days', () => {
    const invoice = createInvoice({ amount: 1000, issuedAt: daysAgo(45) });
    expect(calculateTotal(invoice)).toBe(1050); // 5% late fee
  });

  test('caps late fee at maximum penalty', () => {
    const invoice = createInvoice({ amount: 1000, issuedAt: daysAgo(365) });
    expect(calculateTotal(invoice)).toBe(1200); // max 20% penalty
  });
});

// Skip testing: Logger wrapper, type definitions, config constants, generated code
```

**Benefits:**
- 80% threshold catches missing tests for important code without busywork
- Excluding generated code, types, and config prevents false coverage pressure
- Coverage reports become useful — uncovered lines point to genuinely untested risk areas
- Team velocity improves because effort targets high-value tests

Reference: [Test Coverage - Martin Fowler](https://martinfowler.com/bliki/TestCoverage.html)


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


---

## Organize Tests for Fast Feedback

**Impact: LOW (reduces developer wait time by running only relevant tests during development)**

Organize tests by speed tier (unit/integration/e2e). Run fast tests in watch mode during development and the full suite in CI.

## Incorrect

```typescript
// ❌ Bad: all tests in one flat directory, no way to run subsets
// tests/
//   user.test.ts          (unit — 10ms)
//   order.test.ts         (unit — 15ms)
//   checkout.test.ts      (integration — 2s, hits DB)
//   payment.test.ts       (integration — 3s, hits API)
//   full-purchase.test.ts (e2e — 15s, launches browser)
//   signup-flow.test.ts   (e2e — 12s, launches browser)

// vitest.config.ts
import { defineConfig } from 'vitest/config';

export default defineConfig({
  test: {
    include: ['tests/**/*.test.ts'],
    // Every file change runs ALL tests — unit, integration, and e2e
    // Developer waits 30+ seconds for feedback on a one-line change
  },
});
```

**Problems:**
- Changing a utility function triggers 30+ seconds of e2e tests
- No way to run just unit tests during development
- Watch mode becomes useless when every save triggers slow integration tests
- Developers stop running tests locally because the feedback loop is too slow

## Correct

```typescript
// ✅ Good: organized by speed tier with separate configs
// src/
//   services/
//     user-service.ts
//     user-service.unit.test.ts       (fast — pure logic)
//     user-service.integration.test.ts (medium — hits DB)
// e2e/
//   signup.e2e.test.ts                (slow — browser)

// vitest.config.ts — default runs only unit tests (fast feedback)
import { defineConfig } from 'vitest/config';

export default defineConfig({
  test: {
    include: ['src/**/*.unit.test.ts'],
    // Watch mode only runs affected unit tests — feedback in < 2 seconds
  },
});

// vitest.config.integration.ts — for integration tests
export default defineConfig({
  test: {
    include: ['src/**/*.integration.test.ts'],
    pool: 'forks', // Isolation for database tests
    testTimeout: 10000,
    hookTimeout: 15000,
  },
});

// vitest.config.e2e.ts — for end-to-end tests
export default defineConfig({
  test: {
    include: ['e2e/**/*.e2e.test.ts'],
    testTimeout: 30000,
    hookTimeout: 30000,
  },
});

// package.json scripts for each tier
// {
//   "scripts": {
//     "test": "vitest",
//     "test:unit": "vitest --config vitest.config.ts",
//     "test:integration": "vitest --config vitest.config.integration.ts",
//     "test:e2e": "vitest --config vitest.config.e2e.ts",
//     "test:all": "vitest --config vitest.config.ts && vitest run --config vitest.config.integration.ts && vitest run --config vitest.config.e2e.ts",
//     "test:ci": "vitest run --config vitest.config.ts --coverage && vitest run --config vitest.config.integration.ts && vitest run --config vitest.config.e2e.ts"
//   }
// }

// --- Use Vitest's built-in filtering for quick targeted runs ---

// Run tests related to changed files only (watch mode default)
// $ vitest

// Run tests matching a pattern
// $ vitest user-service

// Run a specific test file
// $ vitest src/services/user-service.unit.test.ts

// --- CI pipeline runs tiers in order: fast failures first ---
// .github/workflows/test.yml
// jobs:
//   unit:        runs test:unit      (~10s)
//   integration: runs test:integration (~60s, needs unit to pass)
//   e2e:         runs test:e2e       (~5min, needs integration to pass)
```

**Benefits:**
- Watch mode runs only fast unit tests, giving sub-second feedback during development
- `vitest user-service` instantly filters to relevant tests without configuration
- CI runs tiers in order — if unit tests fail, slow integration/e2e tests are skipped
- Developers can run the right level of testing for their current task

Reference: [Vitest - Filtering Tests](https://vitest.dev/guide/filtering)


---

