---
title: Minimal Test Data
impact: HIGH
impactDescription: "test clarity and focus"
tags: test-data, minimal, noise-reduction
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
