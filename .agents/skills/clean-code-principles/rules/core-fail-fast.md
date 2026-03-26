---
id: core-fail-fast
title: Fail Fast Principle
category: core-principles
priority: critical
tags: [fail-fast, error-handling, validation]
related: [solid-lsp-preconditions, core-encapsulation]
---

# Fail Fast Principle

Detect and report errors as early as possible. Validate inputs at system boundaries, check preconditions at the start of functions, and throw exceptions immediately when something is wrong.

## Bad Example

```typescript
// Anti-pattern: Delayed error detection

class OrderProcessor {
  async processOrder(order: any): Promise<ProcessResult> {
    // No validation - problems will surface later

    // Proceeds even with potentially invalid data
    const customer = await this.customerRepo.findById(order.customerId);

    // Customer might be null, but we keep going
    const items = order.items;

    // Calculates total even if items might be undefined or empty
    let total = 0;
    if (items) {
      for (const item of items) {
        // item.productId might not exist
        const product = await this.productRepo.findById(item.productId);
        // product might be null
        if (product) {
          total += product.price * (item.quantity || 1);
        }
      }
    }

    // Attempts payment even if customer is null
    let paymentResult;
    if (customer && customer.paymentMethod) {
      paymentResult = await this.paymentService.charge(
        customer.paymentMethod,
        total
      );
    }

    // Creates order record even if payment failed
    const orderRecord = await this.orderRepo.create({
      customerId: order.customerId,
      total,
      status: paymentResult?.success ? 'paid' : 'pending'
    });

    // Sends email even if we don't have a valid email
    if (customer?.email) {
      await this.emailService.send(customer.email, 'Order confirmation');
    }

    // Returns "success" even though many things might have gone wrong
    return { success: true, orderId: orderRecord.id };
  }
}

// Problems:
// 1. Null customer leads to silent failures
// 2. Empty order goes through system doing nothing useful
// 3. Payment failure creates orphaned order records
// 4. No indication of what went wrong
// 5. Database in inconsistent state
```

## Good Example

```typescript
// Correct approach: Fail fast with immediate validation

// Custom error types for clear failure reasons
class ValidationError extends Error {
  constructor(
    message: string,
    public readonly field: string,
    public readonly value: unknown
  ) {
    super(message);
    this.name = 'ValidationError';
  }
}

class NotFoundError extends Error {
  constructor(
    public readonly entity: string,
    public readonly id: string
  ) {
    super(`${entity} not found: ${id}`);
    this.name = 'NotFoundError';
  }
}

class PaymentError extends Error {
  constructor(
    message: string,
    public readonly code: string
  ) {
    super(message);
    this.name = 'PaymentError';
  }
}

// Input validation schema
interface CreateOrderInput {
  customerId: string;
  items: OrderItemInput[];
}

interface OrderItemInput {
  productId: string;
  quantity: number;
}

class OrderProcessor {
  async processOrder(input: unknown): Promise<ProcessResult> {
    // STEP 1: Validate input immediately
    const validatedInput = this.validateInput(input);

    // STEP 2: Verify customer exists before proceeding
    const customer = await this.loadCustomer(validatedInput.customerId);

    // STEP 3: Verify all products exist before any processing
    const products = await this.loadProducts(validatedInput.items);

    // STEP 4: Verify payment method before creating order
    this.verifyPaymentMethod(customer);

    // STEP 5: Calculate total (now safe - all data validated)
    const total = this.calculateTotal(validatedInput.items, products);

    // STEP 6: Verify sufficient inventory before payment
    await this.verifyInventory(validatedInput.items, products);

    // STEP 7: Process payment before creating order
    const payment = await this.processPayment(customer, total);

    // STEP 8: Create order only after successful payment
    const order = await this.createOrder(validatedInput, customer, total, payment);

    // STEP 9: Send confirmation (non-critical, can fail gracefully)
    await this.sendConfirmation(customer, order);

    return { success: true, orderId: order.id };
  }

  private validateInput(input: unknown): CreateOrderInput {
    if (!input || typeof input !== 'object') {
      throw new ValidationError('Input must be an object', 'input', input);
    }

    const obj = input as Record<string, unknown>;

    if (!obj.customerId || typeof obj.customerId !== 'string') {
      throw new ValidationError('customerId is required and must be a string', 'customerId', obj.customerId);
    }

    if (!Array.isArray(obj.items) || obj.items.length === 0) {
      throw new ValidationError('items must be a non-empty array', 'items', obj.items);
    }

    const validatedItems: OrderItemInput[] = [];
    for (let i = 0; i < obj.items.length; i++) {
      const item = obj.items[i];
      if (!item || typeof item !== 'object') {
        throw new ValidationError(`Item at index ${i} must be an object`, `items[${i}]`, item);
      }

      const itemObj = item as Record<string, unknown>;

      if (!itemObj.productId || typeof itemObj.productId !== 'string') {
        throw new ValidationError(
          `productId is required at index ${i}`,
          `items[${i}].productId`,
          itemObj.productId
        );
      }

      if (typeof itemObj.quantity !== 'number' || itemObj.quantity < 1) {
        throw new ValidationError(
          `quantity must be a positive number at index ${i}`,
          `items[${i}].quantity`,
          itemObj.quantity
        );
      }

      validatedItems.push({
        productId: itemObj.productId,
        quantity: itemObj.quantity
      });
    }

    return {
      customerId: obj.customerId,
      items: validatedItems
    };
  }

  private async loadCustomer(customerId: string): Promise<Customer> {
    const customer = await this.customerRepo.findById(customerId);

    if (!customer) {
      throw new NotFoundError('Customer', customerId);
    }

    if (!customer.isActive) {
      throw new ValidationError('Customer account is inactive', 'customerId', customerId);
    }

    return customer;
  }

  private async loadProducts(items: OrderItemInput[]): Promise<Map<string, Product>> {
    const productIds = items.map(item => item.productId);
    const products = await this.productRepo.findByIds(productIds);

    const productMap = new Map<string, Product>();
    for (const product of products) {
      productMap.set(product.id, product);
    }

    // Verify all products were found
    for (const item of items) {
      if (!productMap.has(item.productId)) {
        throw new NotFoundError('Product', item.productId);
      }
    }

    return productMap;
  }

  private verifyPaymentMethod(customer: Customer): void {
    if (!customer.paymentMethodId) {
      throw new ValidationError(
        'Customer has no payment method configured',
        'paymentMethod',
        null
      );
    }
  }

  private calculateTotal(items: OrderItemInput[], products: Map<string, Product>): number {
    return items.reduce((total, item) => {
      const product = products.get(item.productId)!; // Safe - already validated
      return total + product.price * item.quantity;
    }, 0);
  }

  private async verifyInventory(
    items: OrderItemInput[],
    products: Map<string, Product>
  ): Promise<void> {
    for (const item of items) {
      const product = products.get(item.productId)!;
      if (product.stock < item.quantity) {
        throw new ValidationError(
          `Insufficient stock for product ${product.name}. Available: ${product.stock}, Requested: ${item.quantity}`,
          'quantity',
          item.quantity
        );
      }
    }
  }

  private async processPayment(customer: Customer, amount: number): Promise<Payment> {
    try {
      return await this.paymentService.charge(customer.paymentMethodId, amount);
    } catch (error) {
      throw new PaymentError(
        `Payment failed: ${error.message}`,
        error.code || 'UNKNOWN'
      );
    }
  }

  private async createOrder(
    input: CreateOrderInput,
    customer: Customer,
    total: number,
    payment: Payment
  ): Promise<Order> {
    return this.orderRepo.create({
      customerId: customer.id,
      items: input.items,
      total,
      paymentId: payment.id,
      status: 'paid'
    });
  }

  private async sendConfirmation(customer: Customer, order: Order): Promise<void> {
    try {
      await this.emailService.sendOrderConfirmation(customer.email, order);
    } catch (error) {
      // Log but don't fail - email is non-critical
      this.logger.error('Failed to send order confirmation', { orderId: order.id, error });
    }
  }
}
```

## Why

1. **Clear Error Messages**: When validation fails, you know exactly what's wrong and where.

2. **No Wasted Work**: Invalid requests fail immediately, not after expensive operations.

3. **Data Integrity**: The database never enters an inconsistent state because we validate before mutating.

4. **Debugging**: Stack traces point to the actual problem, not to a downstream symptom.

5. **Predictability**: Either the operation fully succeeds or it cleanly fails with a clear reason.

6. **Security**: Invalid inputs are rejected at the boundary, not passed through the system.

7. **Recovery**: Callers can handle specific error types appropriately (retry, report, fallback).
