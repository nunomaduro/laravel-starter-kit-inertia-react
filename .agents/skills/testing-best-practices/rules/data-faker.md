---
title: Faker Libraries for Test Data
impact: HIGH
impactDescription: "test data variety and edge case discovery"
tags: test-data, faker, random-data, reproducibility
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
