---
title: Mock Only at Boundaries
impact: MEDIUM
impactDescription: "reduces brittle tests by 60%, improves refactoring confidence"
tags: mocking, boundaries, dependencies, isolation
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
