---
id: solid-srp-function
title: SOLID - Single Responsibility Principle (Function Level)
category: solid-principles
priority: critical
tags: [SOLID, SRP, single-responsibility, function-design]
related: [solid-srp-class, core-dry-extraction, core-kiss-simplicity]
---

# Single Responsibility Principle - Function Level

A function should do one thing, do it well, and do it only. It should have a single, clear purpose that can be described without using conjunctions like "and" or "or".

## Bad Example

```typescript
// Anti-pattern: Function does multiple things
async function processOrder(orderId: string): Promise<void> {
  // Fetch order
  const order = await db.query('SELECT * FROM orders WHERE id = ?', [orderId]);

  // Validate order
  if (!order) throw new Error('Order not found');
  if (order.status !== 'pending') throw new Error('Order already processed');
  if (order.items.length === 0) throw new Error('Order has no items');

  // Calculate totals
  let subtotal = 0;
  for (const item of order.items) {
    const product = await db.query('SELECT price FROM products WHERE id = ?', [item.productId]);
    subtotal += product.price * item.quantity;
  }
  const tax = subtotal * 0.1;
  const shipping = subtotal > 100 ? 0 : 10;
  const total = subtotal + tax + shipping;

  // Update inventory
  for (const item of order.items) {
    await db.query('UPDATE products SET stock = stock - ? WHERE id = ?',
      [item.quantity, item.productId]);
  }

  // Process payment
  const paymentResult = await stripe.charges.create({
    amount: Math.round(total * 100),
    currency: 'usd',
    customer: order.customerId
  });

  // Update order status
  await db.query('UPDATE orders SET status = ?, total = ?, payment_id = ? WHERE id = ?',
    ['completed', total, paymentResult.id, orderId]);

  // Send confirmation email
  const emailHtml = `<h1>Order Confirmed</h1><p>Total: $${total}</p>`;
  await sendgrid.send({
    to: order.customerEmail,
    subject: 'Order Confirmation',
    html: emailHtml
  });

  // Log analytics
  await analytics.track('order_completed', { orderId, total, itemCount: order.items.length });
}
```

## Good Example

```typescript
// Correct approach: Each function has a single purpose

async function processOrder(orderId: string): Promise<ProcessedOrder> {
  const order = await fetchAndValidateOrder(orderId);
  const pricing = await calculateOrderPricing(order);

  await reserveInventory(order.items);

  try {
    const payment = await processPayment(order.customerId, pricing.total);
    const completedOrder = await finalizeOrder(order, pricing, payment);

    await sendOrderConfirmation(completedOrder);
    await trackOrderCompletion(completedOrder);

    return completedOrder;
  } catch (error) {
    await releaseInventory(order.items);
    throw error;
  }
}

async function fetchAndValidateOrder(orderId: string): Promise<Order> {
  const order = await orderRepository.findById(orderId);

  if (!order) {
    throw new OrderNotFoundError(orderId);
  }

  validateOrderStatus(order);
  validateOrderItems(order);

  return order;
}

function validateOrderStatus(order: Order): void {
  if (order.status !== 'pending') {
    throw new InvalidOrderStatusError(order.id, order.status);
  }
}

function validateOrderItems(order: Order): void {
  if (order.items.length === 0) {
    throw new EmptyOrderError(order.id);
  }
}

async function calculateOrderPricing(order: Order): Promise<OrderPricing> {
  const subtotal = await calculateSubtotal(order.items);
  const tax = calculateTax(subtotal);
  const shipping = calculateShipping(subtotal);

  return {
    subtotal,
    tax,
    shipping,
    total: subtotal + tax + shipping
  };
}

async function calculateSubtotal(items: OrderItem[]): Promise<number> {
  const itemTotals = await Promise.all(
    items.map(item => calculateItemTotal(item))
  );
  return itemTotals.reduce((sum, total) => sum + total, 0);
}

async function calculateItemTotal(item: OrderItem): Promise<number> {
  const product = await productRepository.findById(item.productId);
  return product.price * item.quantity;
}

function calculateTax(subtotal: number): number {
  const TAX_RATE = 0.1;
  return subtotal * TAX_RATE;
}

function calculateShipping(subtotal: number): number {
  const FREE_SHIPPING_THRESHOLD = 100;
  const STANDARD_SHIPPING = 10;
  return subtotal > FREE_SHIPPING_THRESHOLD ? 0 : STANDARD_SHIPPING;
}

async function reserveInventory(items: OrderItem[]): Promise<void> {
  await Promise.all(
    items.map(item => inventoryService.reserve(item.productId, item.quantity))
  );
}

async function releaseInventory(items: OrderItem[]): Promise<void> {
  await Promise.all(
    items.map(item => inventoryService.release(item.productId, item.quantity))
  );
}

async function processPayment(customerId: string, amount: number): Promise<Payment> {
  return paymentService.charge(customerId, amount);
}

async function finalizeOrder(
  order: Order,
  pricing: OrderPricing,
  payment: Payment
): Promise<ProcessedOrder> {
  return orderRepository.updateStatus(order.id, {
    status: 'completed',
    total: pricing.total,
    paymentId: payment.id
  });
}

async function sendOrderConfirmation(order: ProcessedOrder): Promise<void> {
  await emailService.sendOrderConfirmation(order);
}

async function trackOrderCompletion(order: ProcessedOrder): Promise<void> {
  await analyticsService.track('order_completed', {
    orderId: order.id,
    total: order.total,
    itemCount: order.items.length
  });
}
```

## Why

1. **Readability**: The main function reads like a story - fetch, validate, calculate, reserve, charge, finalize, notify.

2. **Testability**: Each function can be unit tested independently. Testing `calculateTax` doesn't require mocking a database.

3. **Reusability**: `calculateShipping` can be reused in a shipping estimate feature. `validateOrderStatus` can be used in other order operations.

4. **Debugging**: Stack traces point to specific functions. "Error in calculateSubtotal" is more helpful than "Error in processOrder at line 47".

5. **Modification**: Changing tax calculation only touches `calculateTax`. Adding a discount feature can be inserted cleanly between subtotal and tax.

6. **Error Handling**: Each function can have appropriate error handling. The main function can orchestrate rollback on failure.

7. **Documentation**: Function names serve as documentation. The code is self-explanatory without comments.
