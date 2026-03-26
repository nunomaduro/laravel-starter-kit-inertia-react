---
id: solid-dip-abstractions
title: SOLID - Dependency Inversion (Abstractions)
category: solid-principles
priority: critical
tags: [SOLID, DIP, dependency-inversion, abstractions]
related: [solid-dip-injection, solid-ocp-abstraction, pattern-repository]
---

# Dependency Inversion Principle - Depend on Abstractions

High-level modules should not depend on low-level modules. Both should depend on abstractions. Abstractions should not depend on details; details should depend on abstractions.

## Bad Example

```typescript
// Anti-pattern: High-level module depends on low-level implementation details

// Low-level modules (concrete implementations)
class MySQLDatabase {
  connect(): void {
    console.log('Connecting to MySQL...');
  }

  query(sql: string): any[] {
    console.log(`Executing MySQL query: ${sql}`);
    return [];
  }

  close(): void {
    console.log('Closing MySQL connection');
  }
}

class SmtpEmailSender {
  send(to: string, subject: string, body: string): void {
    console.log(`Sending SMTP email to ${to}`);
  }
}

class StripePaymentGateway {
  charge(amount: number, cardToken: string): string {
    console.log(`Charging $${amount} via Stripe`);
    return 'stripe_txn_123';
  }
}

// High-level module directly depends on low-level implementations
class OrderService {
  private database: MySQLDatabase;
  private emailSender: SmtpEmailSender;
  private paymentGateway: StripePaymentGateway;

  constructor() {
    // Direct instantiation creates tight coupling
    this.database = new MySQLDatabase();
    this.emailSender = new SmtpEmailSender();
    this.paymentGateway = new StripePaymentGateway();
  }

  async createOrder(orderData: OrderData): Promise<Order> {
    this.database.connect();

    // MySQL-specific query
    const result = this.database.query(
      `INSERT INTO orders (customer_id, total) VALUES (${orderData.customerId}, ${orderData.total})`
    );

    // Stripe-specific charging
    const txnId = this.paymentGateway.charge(orderData.total, orderData.cardToken);

    // SMTP-specific sending
    this.emailSender.send(
      orderData.customerEmail,
      'Order Confirmation',
      `Your order has been placed. Transaction: ${txnId}`
    );

    this.database.close();

    return { id: 'order_1', ...orderData };
  }
}

// Problems:
// 1. Cannot switch to PostgreSQL without modifying OrderService
// 2. Cannot use SendGrid for emails without modifying OrderService
// 3. Cannot test without real MySQL, SMTP, and Stripe
// 4. OrderService knows implementation details it shouldn't
```

## Good Example

```typescript
// Correct approach: Depend on abstractions, not concretions

// Abstractions (interfaces) - owned by the high-level module
interface Database {
  connect(): Promise<void>;
  query<T>(query: QueryBuilder): Promise<T[]>;
  execute(command: Command): Promise<void>;
  disconnect(): Promise<void>;
}

interface QueryBuilder {
  table: string;
  select?: string[];
  where?: Record<string, any>;
  orderBy?: string;
  limit?: number;
}

interface Command {
  table: string;
  operation: 'insert' | 'update' | 'delete';
  data?: Record<string, any>;
  where?: Record<string, any>;
}

interface EmailSender {
  send(options: EmailOptions): Promise<void>;
}

interface EmailOptions {
  to: string;
  subject: string;
  body: string;
  html?: string;
}

interface PaymentGateway {
  charge(request: ChargeRequest): Promise<ChargeResult>;
  refund(transactionId: string, amount?: number): Promise<RefundResult>;
}

interface ChargeRequest {
  amount: number;
  currency: string;
  paymentMethodId: string;
  metadata?: Record<string, string>;
}

interface ChargeResult {
  transactionId: string;
  status: 'success' | 'pending' | 'failed';
  amount: number;
}

// High-level module depends only on abstractions
class OrderService {
  constructor(
    private database: Database,
    private emailSender: EmailSender,
    private paymentGateway: PaymentGateway
  ) {}

  async createOrder(orderData: OrderData): Promise<Order> {
    await this.database.connect();

    try {
      // Uses abstraction - no SQL dialect specifics
      await this.database.execute({
        table: 'orders',
        operation: 'insert',
        data: {
          customerId: orderData.customerId,
          total: orderData.total,
          status: 'pending'
        }
      });

      // Uses abstraction - no payment provider specifics
      const chargeResult = await this.paymentGateway.charge({
        amount: orderData.total,
        currency: 'USD',
        paymentMethodId: orderData.paymentMethodId,
        metadata: { orderId: orderData.id }
      });

      // Uses abstraction - no email provider specifics
      await this.emailSender.send({
        to: orderData.customerEmail,
        subject: 'Order Confirmation',
        body: `Your order has been placed. Transaction: ${chargeResult.transactionId}`
      });

      return { id: orderData.id, ...orderData, status: 'completed' };
    } finally {
      await this.database.disconnect();
    }
  }
}

// Low-level implementations depend on abstractions
class MySQLDatabase implements Database {
  private connection: any;

  async connect(): Promise<void> {
    this.connection = await mysql.createConnection(config);
  }

  async query<T>(query: QueryBuilder): Promise<T[]> {
    const sql = this.buildSelectQuery(query);
    const [rows] = await this.connection.execute(sql);
    return rows as T[];
  }

  async execute(command: Command): Promise<void> {
    const sql = this.buildCommandQuery(command);
    await this.connection.execute(sql);
  }

  async disconnect(): Promise<void> {
    await this.connection.end();
  }

  private buildSelectQuery(query: QueryBuilder): string {
    // MySQL-specific query building
    return `SELECT ${query.select?.join(', ') ?? '*'} FROM ${query.table}`;
  }

  private buildCommandQuery(command: Command): string {
    // MySQL-specific command building
    return `INSERT INTO ${command.table} ...`;
  }
}

class PostgreSQLDatabase implements Database {
  // PostgreSQL-specific implementation of the same interface
  async connect(): Promise<void> { /* PostgreSQL connection */ }
  async query<T>(query: QueryBuilder): Promise<T[]> { /* PostgreSQL query */ }
  async execute(command: Command): Promise<void> { /* PostgreSQL execute */ }
  async disconnect(): Promise<void> { /* PostgreSQL disconnect */ }
}

class SendGridEmailSender implements EmailSender {
  constructor(private apiKey: string) {}

  async send(options: EmailOptions): Promise<void> {
    await sendgrid.send({
      to: options.to,
      from: 'noreply@example.com',
      subject: options.subject,
      text: options.body,
      html: options.html
    });
  }
}

class StripePaymentGateway implements PaymentGateway {
  constructor(private stripe: Stripe) {}

  async charge(request: ChargeRequest): Promise<ChargeResult> {
    const intent = await this.stripe.paymentIntents.create({
      amount: request.amount * 100,
      currency: request.currency,
      payment_method: request.paymentMethodId,
      confirm: true
    });

    return {
      transactionId: intent.id,
      status: intent.status === 'succeeded' ? 'success' : 'pending',
      amount: request.amount
    };
  }

  async refund(transactionId: string, amount?: number): Promise<RefundResult> {
    const refund = await this.stripe.refunds.create({
      payment_intent: transactionId,
      amount: amount ? amount * 100 : undefined
    });
    return { refundId: refund.id, status: 'success' };
  }
}

// Composition root - where we wire everything together
function createOrderService(): OrderService {
  const database = new PostgreSQLDatabase();
  const emailSender = new SendGridEmailSender(process.env.SENDGRID_API_KEY!);
  const paymentGateway = new StripePaymentGateway(new Stripe(process.env.STRIPE_KEY));

  return new OrderService(database, emailSender, paymentGateway);
}

// Testing with mock implementations
class MockDatabase implements Database {
  public queries: QueryBuilder[] = [];
  public commands: Command[] = [];

  async connect(): Promise<void> {}
  async query<T>(query: QueryBuilder): Promise<T[]> {
    this.queries.push(query);
    return [];
  }
  async execute(command: Command): Promise<void> {
    this.commands.push(command);
  }
  async disconnect(): Promise<void> {}
}

// Test without real databases, email servers, or payment gateways
describe('OrderService', () => {
  it('should create order', async () => {
    const mockDb = new MockDatabase();
    const mockEmail: EmailSender = { send: jest.fn() };
    const mockPayment: PaymentGateway = {
      charge: jest.fn().mockResolvedValue({ transactionId: 'test_txn', status: 'success', amount: 100 }),
      refund: jest.fn()
    };

    const service = new OrderService(mockDb, mockEmail, mockPayment);
    await service.createOrder(testOrderData);

    expect(mockDb.commands).toHaveLength(1);
    expect(mockPayment.charge).toHaveBeenCalled();
    expect(mockEmail.send).toHaveBeenCalled();
  });
});
```

## Why

1. **Loose Coupling**: `OrderService` doesn't know or care about MySQL, Stripe, or SendGrid. It works with any implementation.

2. **Easy Swapping**: Switch from MySQL to PostgreSQL by providing a different implementation. No changes to `OrderService`.

3. **Testability**: Test with mock implementations - no real databases or external services needed.

4. **Parallel Development**: Teams can work on different implementations simultaneously against the same interface.

5. **Policy/Detail Separation**: Business rules (OrderService) are separate from infrastructure details (MySQLDatabase).

6. **Stable Architecture**: Abstractions change less frequently than implementations. The core is protected from change.

7. **Plugin Architecture**: New implementations can be added without modifying existing code.
