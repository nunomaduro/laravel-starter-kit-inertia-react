---
title: Arrange-Act-Assert Pattern
impact: CRITICAL
impactDescription: "test readability and maintainability"
tags: test-structure, aaa, arrange-act-assert
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
