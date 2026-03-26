---
id: pattern-repository
title: Design Pattern - Repository
category: design-patterns
priority: high
tags: [design-patterns, repository, data-access, separation-of-concerns]
related: [solid-dip-abstractions, solid-srp-class, core-separation-concerns]
---

# Repository Pattern

The Repository pattern abstracts data persistence, providing a collection-like interface for accessing domain objects. It separates business logic from data access, makes code testable, and allows swapping storage implementations without changing application code.

## Bad Example

```typescript
// ❌ Data access mixed with business logic
class OrderService {
  async createOrder(userId: string, items: CartItem[]) {
    // Business logic
    const total = items.reduce((sum, i) => sum + i.price * i.quantity, 0);

    // Direct database access - tightly coupled
    const result = await pool.query(
      `INSERT INTO orders (user_id, total, status, created_at)
       VALUES ($1, $2, $3, NOW()) RETURNING *`,
      [userId, total, 'pending']
    );

    const orderId = result.rows[0].id;

    // More SQL scattered in business logic
    for (const item of items) {
      await pool.query(
        `INSERT INTO order_items (order_id, product_id, quantity, price)
         VALUES ($1, $2, $3, $4)`,
        [orderId, item.productId, item.quantity, item.price]
      );
    }

    return result.rows[0];
  }

  async getOrdersByUser(userId: string) {
    // SQL everywhere
    const result = await pool.query(
      `SELECT o.*, json_agg(oi.*) as items
       FROM orders o
       LEFT JOIN order_items oi ON o.id = oi.order_id
       WHERE o.user_id = $1
       GROUP BY o.id`,
      [userId]
    );
    return result.rows;
  }
}
```

**Problems:**
- Business logic mixed with SQL
- Hard to test without real database
- Changing database requires rewriting service
- SQL scattered across codebase

## Good Example

### Define Repository Interface

```typescript
// ✅ Repository interface - contract for data access
interface OrderRepository {
  find(id: string): Promise<Order | null>;
  findByUser(userId: string): Promise<Order[]>;
  findByStatus(status: OrderStatus): Promise<Order[]>;
  save(order: Order): Promise<Order>;
  delete(id: string): Promise<void>;
}

interface OrderItemRepository {
  findByOrder(orderId: string): Promise<OrderItem[]>;
  saveMany(items: OrderItem[]): Promise<OrderItem[]>;
  deleteByOrder(orderId: string): Promise<void>;
}
```

### Implement Repository

```typescript
// ✅ PostgreSQL implementation
class PostgresOrderRepository implements OrderRepository {
  constructor(private db: Pool) {}

  async find(id: string): Promise<Order | null> {
    const result = await this.db.query(
      'SELECT * FROM orders WHERE id = $1',
      [id]
    );
    return result.rows[0] ? this.mapToOrder(result.rows[0]) : null;
  }

  async findByUser(userId: string): Promise<Order[]> {
    const result = await this.db.query(
      'SELECT * FROM orders WHERE user_id = $1 ORDER BY created_at DESC',
      [userId]
    );
    return result.rows.map(this.mapToOrder);
  }

  async findByStatus(status: OrderStatus): Promise<Order[]> {
    const result = await this.db.query(
      'SELECT * FROM orders WHERE status = $1',
      [status]
    );
    return result.rows.map(this.mapToOrder);
  }

  async save(order: Order): Promise<Order> {
    if (order.id) {
      return this.update(order);
    }
    return this.insert(order);
  }

  private async insert(order: Order): Promise<Order> {
    const result = await this.db.query(
      `INSERT INTO orders (user_id, total, status, created_at)
       VALUES ($1, $2, $3, NOW()) RETURNING *`,
      [order.userId, order.total, order.status]
    );
    return this.mapToOrder(result.rows[0]);
  }

  private async update(order: Order): Promise<Order> {
    const result = await this.db.query(
      `UPDATE orders SET total = $1, status = $2, updated_at = NOW()
       WHERE id = $3 RETURNING *`,
      [order.total, order.status, order.id]
    );
    return this.mapToOrder(result.rows[0]);
  }

  async delete(id: string): Promise<void> {
    await this.db.query('DELETE FROM orders WHERE id = $1', [id]);
  }

  private mapToOrder(row: any): Order {
    return new Order({
      id: row.id,
      userId: row.user_id,
      total: parseFloat(row.total),
      status: row.status as OrderStatus,
      createdAt: row.created_at,
      updatedAt: row.updated_at,
    });
  }
}
```

### Clean Service Layer

```typescript
// ✅ Service focuses on business logic only
class OrderService {
  constructor(
    private orderRepository: OrderRepository,
    private orderItemRepository: OrderItemRepository,
    private productRepository: ProductRepository,
  ) {}

  async createOrder(userId: string, items: CartItem[]): Promise<Order> {
    // Pure business logic
    const total = this.calculateTotal(items);

    const order = new Order({
      userId,
      total,
      status: OrderStatus.Pending,
    });

    // Repository handles persistence
    const savedOrder = await this.orderRepository.save(order);

    const orderItems = items.map(item => new OrderItem({
      orderId: savedOrder.id,
      productId: item.productId,
      quantity: item.quantity,
      price: item.price,
    }));

    await this.orderItemRepository.saveMany(orderItems);

    return savedOrder;
  }

  async cancelOrder(orderId: string): Promise<Order> {
    const order = await this.orderRepository.find(orderId);

    if (!order) {
      throw new OrderNotFoundException(orderId);
    }

    if (!order.canBeCancelled()) {
      throw new InvalidOrderStateException('Order cannot be cancelled');
    }

    order.cancel();
    return this.orderRepository.save(order);
  }

  private calculateTotal(items: CartItem[]): number {
    return items.reduce((sum, item) => sum + item.price * item.quantity, 0);
  }
}
```

### In-Memory Repository for Testing

```typescript
// ✅ In-memory implementation for tests
class InMemoryOrderRepository implements OrderRepository {
  private orders: Map<string, Order> = new Map();
  private idCounter = 1;

  async find(id: string): Promise<Order | null> {
    return this.orders.get(id) ?? null;
  }

  async findByUser(userId: string): Promise<Order[]> {
    return Array.from(this.orders.values())
      .filter(order => order.userId === userId);
  }

  async findByStatus(status: OrderStatus): Promise<Order[]> {
    return Array.from(this.orders.values())
      .filter(order => order.status === status);
  }

  async save(order: Order): Promise<Order> {
    if (!order.id) {
      order.id = String(this.idCounter++);
    }
    this.orders.set(order.id, order);
    return order;
  }

  async delete(id: string): Promise<void> {
    this.orders.delete(id);
  }

  // Test helper methods
  clear(): void {
    this.orders.clear();
  }

  seed(orders: Order[]): void {
    orders.forEach(order => this.orders.set(order.id, order));
  }
}
```

### Easy Testing

```typescript
// ✅ Unit tests without database
describe('OrderService', () => {
  let orderService: OrderService;
  let orderRepository: InMemoryOrderRepository;
  let orderItemRepository: InMemoryOrderItemRepository;

  beforeEach(() => {
    orderRepository = new InMemoryOrderRepository();
    orderItemRepository = new InMemoryOrderItemRepository();
    orderService = new OrderService(
      orderRepository,
      orderItemRepository,
      new InMemoryProductRepository(),
    );
  });

  it('creates order with calculated total', async () => {
    const items = [
      { productId: '1', quantity: 2, price: 10 },
      { productId: '2', quantity: 1, price: 25 },
    ];

    const order = await orderService.createOrder('user-1', items);

    expect(order.total).toBe(45);
    expect(order.status).toBe(OrderStatus.Pending);
  });

  it('cancels pending order', async () => {
    orderRepository.seed([
      new Order({ id: '1', userId: 'user-1', status: OrderStatus.Pending }),
    ]);

    const order = await orderService.cancelOrder('1');

    expect(order.status).toBe(OrderStatus.Cancelled);
  });

  it('throws when cancelling shipped order', async () => {
    orderRepository.seed([
      new Order({ id: '1', userId: 'user-1', status: OrderStatus.Shipped }),
    ]);

    await expect(orderService.cancelOrder('1'))
      .rejects.toThrow(InvalidOrderStateException);
  });
});
```

### Generic Repository Base

```typescript
// ✅ Generic base for common operations
interface Repository<T, ID> {
  find(id: ID): Promise<T | null>;
  findAll(): Promise<T[]>;
  save(entity: T): Promise<T>;
  delete(id: ID): Promise<void>;
  exists(id: ID): Promise<boolean>;
}

abstract class BasePostgresRepository<T, ID> implements Repository<T, ID> {
  constructor(
    protected db: Pool,
    protected tableName: string,
  ) {}

  async find(id: ID): Promise<T | null> {
    const result = await this.db.query(
      `SELECT * FROM ${this.tableName} WHERE id = $1`,
      [id]
    );
    return result.rows[0] ? this.mapToEntity(result.rows[0]) : null;
  }

  async exists(id: ID): Promise<boolean> {
    const result = await this.db.query(
      `SELECT 1 FROM ${this.tableName} WHERE id = $1`,
      [id]
    );
    return result.rows.length > 0;
  }

  protected abstract mapToEntity(row: any): T;
}
```

## Why

- Separation of concerns
- Business logic free of persistence details
- Easy to test with in-memory implementations
- Swap databases without changing services
- Centralized query logic
- Consistent data access patterns
- Single place to add caching, logging
