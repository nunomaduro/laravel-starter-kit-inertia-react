---
title: Test Data Factories
impact: HIGH
impactDescription: "test data consistency and reduced duplication"
tags: test-data, factories, defaults
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
