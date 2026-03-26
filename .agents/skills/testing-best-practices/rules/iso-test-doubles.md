---
title: Test Doubles for Isolation
impact: CRITICAL
impactDescription: "unit test speed and reliability"
tags: test-isolation, mocks, stubs, spies, fakes
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
