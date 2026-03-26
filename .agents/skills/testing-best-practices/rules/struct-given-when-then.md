---
title: Given-When-Then Pattern
impact: CRITICAL
impactDescription: "behavior-driven test clarity"
tags: test-structure, bdd, given-when-then
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
