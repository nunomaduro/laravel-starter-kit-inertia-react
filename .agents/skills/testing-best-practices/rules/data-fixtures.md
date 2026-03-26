---
title: Test Fixtures
impact: HIGH
impactDescription: "test data reusability and consistency"
tags: test-data, fixtures, setup, reusable-data
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
