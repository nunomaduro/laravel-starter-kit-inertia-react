---
id: core-separation-concerns
title: Separation of Concerns
category: core-principles
priority: critical
tags: [separation-of-concerns, modularity, cohesion]
related: [solid-srp-class, solid-srp-function, core-law-demeter]
---

# Separation of Concerns

Different concerns should be handled by different parts of the system. Each module, class, or function should address a single concern, making the code easier to understand, test, and modify.

## Bad Example

```typescript
// Anti-pattern: Multiple concerns mixed together

class OrderProcessor {
  async processOrder(orderData: any, request: Request): Promise<Response> {
    // Concern 1: HTTP request parsing
    const authHeader = request.headers.get('Authorization');
    if (!authHeader || !authHeader.startsWith('Bearer ')) {
      return new Response(JSON.stringify({ error: 'Unauthorized' }), { status: 401 });
    }
    const token = authHeader.slice(7);

    // Concern 2: Authentication
    let userId: string;
    try {
      const decoded = jwt.verify(token, process.env.JWT_SECRET!) as { userId: string };
      userId = decoded.userId;
    } catch {
      return new Response(JSON.stringify({ error: 'Invalid token' }), { status: 401 });
    }

    // Concern 3: Validation
    if (!orderData.items || orderData.items.length === 0) {
      return new Response(JSON.stringify({ error: 'Order must have items' }), { status: 400 });
    }
    for (const item of orderData.items) {
      if (!item.productId || item.quantity < 1) {
        return new Response(JSON.stringify({ error: 'Invalid item' }), { status: 400 });
      }
    }

    // Concern 4: Database access
    const connection = await mysql.createConnection(process.env.DATABASE_URL!);

    try {
      // Concern 5: Business logic - inventory check
      for (const item of orderData.items) {
        const [rows] = await connection.execute(
          'SELECT stock FROM products WHERE id = ?',
          [item.productId]
        );
        if (rows[0].stock < item.quantity) {
          return new Response(
            JSON.stringify({ error: `Insufficient stock for ${item.productId}` }),
            { status: 400 }
          );
        }
      }

      // Concern 6: Business logic - pricing
      let total = 0;
      for (const item of orderData.items) {
        const [rows] = await connection.execute(
          'SELECT price FROM products WHERE id = ?',
          [item.productId]
        );
        total += rows[0].price * item.quantity;
      }

      // Concern 7: Business logic - create order
      const orderId = crypto.randomUUID();
      await connection.execute(
        'INSERT INTO orders (id, user_id, total, status) VALUES (?, ?, ?, ?)',
        [orderId, userId, total, 'pending']
      );

      // Concern 8: Payment processing
      const stripe = new Stripe(process.env.STRIPE_KEY!);
      const paymentIntent = await stripe.paymentIntents.create({
        amount: Math.round(total * 100),
        currency: 'usd',
        customer: userId
      });

      // Concern 9: Inventory update
      for (const item of orderData.items) {
        await connection.execute(
          'UPDATE products SET stock = stock - ? WHERE id = ?',
          [item.quantity, item.productId]
        );
      }

      // Concern 10: Notification
      const transporter = nodemailer.createTransport({ /* SMTP config */ });
      await transporter.sendMail({
        to: orderData.email,
        subject: 'Order Confirmation',
        html: `<h1>Order ${orderId} confirmed</h1><p>Total: $${total}</p>`
      });

      // Concern 11: Logging
      console.log(`Order ${orderId} created for user ${userId}`);

      // Concern 12: HTTP response formatting
      return new Response(
        JSON.stringify({ orderId, total, paymentIntentId: paymentIntent.id }),
        { status: 201, headers: { 'Content-Type': 'application/json' } }
      );

    } finally {
      await connection.end();
    }
  }
}
```

## Good Example

```typescript
// Correct approach: Each concern in its own module

// Concern: HTTP handling
class OrderController {
  constructor(private orderService: OrderService) {}

  async createOrder(req: Request, res: Response): Promise<void> {
    try {
      const userId = req.user.id; // Auth middleware already handled this
      const orderData = req.body; // Validation middleware already validated

      const order = await this.orderService.createOrder(userId, orderData);

      res.status(201).json({
        orderId: order.id,
        total: order.total,
        status: order.status
      });
    } catch (error) {
      if (error instanceof InsufficientStockError) {
        res.status(400).json({ error: error.message });
      } else if (error instanceof PaymentFailedError) {
        res.status(402).json({ error: 'Payment failed' });
      } else {
        throw error; // Let error middleware handle
      }
    }
  }
}

// Concern: Authentication (middleware)
class AuthMiddleware {
  constructor(private authService: AuthService) {}

  async handle(req: Request, res: Response, next: NextFunction): Promise<void> {
    const token = this.extractToken(req);
    if (!token) {
      res.status(401).json({ error: 'Unauthorized' });
      return;
    }

    const user = await this.authService.validateToken(token);
    if (!user) {
      res.status(401).json({ error: 'Invalid token' });
      return;
    }

    req.user = user;
    next();
  }

  private extractToken(req: Request): string | null {
    const header = req.headers.authorization;
    return header?.startsWith('Bearer ') ? header.slice(7) : null;
  }
}

// Concern: Validation (separate validator)
class OrderValidator {
  validate(data: unknown): CreateOrderData {
    const schema = z.object({
      items: z.array(z.object({
        productId: z.string().uuid(),
        quantity: z.number().int().positive()
      })).min(1)
    });

    return schema.parse(data);
  }
}

// Concern: Business logic orchestration
class OrderService {
  constructor(
    private orderRepository: OrderRepository,
    private inventoryService: InventoryService,
    private pricingService: PricingService,
    private paymentService: PaymentService,
    private notificationService: NotificationService,
    private logger: Logger
  ) {}

  async createOrder(userId: string, data: CreateOrderData): Promise<Order> {
    // Check inventory
    await this.inventoryService.checkAvailability(data.items);

    // Calculate pricing
    const pricing = await this.pricingService.calculateTotal(data.items);

    // Create order
    const order = await this.orderRepository.create({
      userId,
      items: data.items,
      total: pricing.total,
      status: 'pending'
    });

    // Process payment
    await this.paymentService.charge(userId, pricing.total, order.id);

    // Reserve inventory
    await this.inventoryService.reserve(data.items);

    // Send confirmation
    await this.notificationService.sendOrderConfirmation(order);

    // Log
    this.logger.info('Order created', { orderId: order.id, userId });

    return order;
  }
}

// Concern: Inventory management
class InventoryService {
  constructor(private productRepository: ProductRepository) {}

  async checkAvailability(items: OrderItem[]): Promise<void> {
    for (const item of items) {
      const product = await this.productRepository.findById(item.productId);
      if (product.stock < item.quantity) {
        throw new InsufficientStockError(item.productId, product.stock, item.quantity);
      }
    }
  }

  async reserve(items: OrderItem[]): Promise<void> {
    for (const item of items) {
      await this.productRepository.decrementStock(item.productId, item.quantity);
    }
  }
}

// Concern: Pricing calculation
class PricingService {
  constructor(private productRepository: ProductRepository) {}

  async calculateTotal(items: OrderItem[]): Promise<PricingResult> {
    let subtotal = 0;

    for (const item of items) {
      const product = await this.productRepository.findById(item.productId);
      subtotal += product.price * item.quantity;
    }

    const tax = this.calculateTax(subtotal);
    const total = subtotal + tax;

    return { subtotal, tax, total };
  }

  private calculateTax(amount: number): number {
    return amount * 0.1; // 10% tax
  }
}

// Concern: Payment processing
class PaymentService {
  constructor(private stripeClient: Stripe) {}

  async charge(userId: string, amount: number, orderId: string): Promise<Payment> {
    const intent = await this.stripeClient.paymentIntents.create({
      amount: Math.round(amount * 100),
      currency: 'usd',
      metadata: { orderId }
    });

    return { id: intent.id, status: 'pending' };
  }
}

// Concern: Notifications
class NotificationService {
  constructor(private emailService: EmailService) {}

  async sendOrderConfirmation(order: Order): Promise<void> {
    await this.emailService.send({
      to: order.userEmail,
      template: 'order-confirmation',
      data: { orderId: order.id, total: order.total }
    });
  }
}

// Concern: Data access
class OrderRepository {
  constructor(private db: Database) {}

  async create(data: CreateOrderInput): Promise<Order> {
    return this.db.orders.create({ data });
  }

  async findById(id: string): Promise<Order | null> {
    return this.db.orders.findUnique({ where: { id } });
  }
}
```

## Why

1. **Understandability**: Each module has one job. Easy to understand what it does.

2. **Testability**: Test inventory logic without payment, test pricing without database.

3. **Reusability**: `PricingService` can be used for quotes, carts, or invoices.

4. **Maintainability**: Change email provider? Only touch `EmailService`.

5. **Team Scaling**: Different developers can work on different concerns without conflicts.

6. **Flexibility**: Swap Stripe for another payment provider by changing only `PaymentService`.

7. **Debugging**: Error in pricing? Look at `PricingService`. Clear boundaries help locate issues.
