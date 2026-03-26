---
title: Realistic Test Data
impact: HIGH
impactDescription: "edge case discovery and validation accuracy"
tags: test-data, realistic, internationalization, edge-cases
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
