---
title: No Magic Numbers in Assertions
impact: HIGH
impactDescription: "test self-documentation and maintainability"
tags: assertions, magic-numbers, constants
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
