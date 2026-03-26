---
id: solid-ocp-extension
title: SOLID - Open/Closed Principle (Extension)
category: solid-principles
priority: critical
tags: [SOLID, OCP, open-closed, extensibility, design-patterns]
related: [solid-ocp-abstraction, pattern-repository, solid-dip-abstractions]
---

# Open/Closed Principle - Extension

Software entities should be open for extension but closed for modification. Add new functionality by adding new code, not by changing existing code.

## Bad Example

```typescript
// Anti-pattern: Modifying existing code to add new functionality
class PaymentProcessor {
  processPayment(payment: Payment): PaymentResult {
    switch (payment.method) {
      case 'credit_card':
        return this.processCreditCard(payment);
      case 'paypal':
        return this.processPayPal(payment);
      case 'stripe':
        return this.processStripe(payment);
      // Every new payment method requires modifying this class
      case 'apple_pay':
        return this.processApplePay(payment);
      case 'google_pay':
        return this.processGooglePay(payment);
      case 'crypto':
        return this.processCrypto(payment);
      default:
        throw new Error(`Unknown payment method: ${payment.method}`);
    }
  }

  private processCreditCard(payment: Payment): PaymentResult {
    // Credit card logic
  }

  private processPayPal(payment: Payment): PaymentResult {
    // PayPal logic
  }

  private processStripe(payment: Payment): PaymentResult {
    // Stripe logic
  }

  private processApplePay(payment: Payment): PaymentResult {
    // Apple Pay logic
  }

  private processGooglePay(payment: Payment): PaymentResult {
    // Google Pay logic
  }

  private processCrypto(payment: Payment): PaymentResult {
    // Crypto logic
  }
}

// Adding a new payment method requires:
// 1. Adding a new case to the switch statement
// 2. Adding a new private method
// 3. Testing the entire class again
```

## Good Example

```typescript
// Correct approach: Open for extension, closed for modification

// Define the contract for payment handlers
interface PaymentHandler {
  readonly methodType: string;
  canHandle(payment: Payment): boolean;
  process(payment: Payment): Promise<PaymentResult>;
  validate(payment: Payment): ValidationResult;
}

// Payment processor that never needs modification
class PaymentProcessor {
  private handlers: Map<string, PaymentHandler> = new Map();

  registerHandler(handler: PaymentHandler): void {
    this.handlers.set(handler.methodType, handler);
  }

  async processPayment(payment: Payment): Promise<PaymentResult> {
    const handler = this.handlers.get(payment.method);

    if (!handler) {
      throw new UnsupportedPaymentMethodError(payment.method);
    }

    const validation = handler.validate(payment);
    if (!validation.isValid) {
      throw new PaymentValidationError(validation.errors);
    }

    return handler.process(payment);
  }

  getSupportedMethods(): string[] {
    return Array.from(this.handlers.keys());
  }
}

// Each payment method is a separate class that can be added without modifying PaymentProcessor

class CreditCardPaymentHandler implements PaymentHandler {
  readonly methodType = 'credit_card';

  canHandle(payment: Payment): boolean {
    return payment.method === this.methodType;
  }

  validate(payment: Payment): ValidationResult {
    const errors: string[] = [];
    if (!payment.cardNumber || payment.cardNumber.length !== 16) {
      errors.push('Invalid card number');
    }
    if (!payment.cvv || payment.cvv.length !== 3) {
      errors.push('Invalid CVV');
    }
    return { isValid: errors.length === 0, errors };
  }

  async process(payment: Payment): Promise<PaymentResult> {
    // Credit card processing logic
    return { success: true, transactionId: generateId() };
  }
}

class PayPalPaymentHandler implements PaymentHandler {
  readonly methodType = 'paypal';

  canHandle(payment: Payment): boolean {
    return payment.method === this.methodType;
  }

  validate(payment: Payment): ValidationResult {
    const errors: string[] = [];
    if (!payment.paypalEmail) {
      errors.push('PayPal email required');
    }
    return { isValid: errors.length === 0, errors };
  }

  async process(payment: Payment): Promise<PaymentResult> {
    // PayPal processing logic
    return { success: true, transactionId: generateId() };
  }
}

// Adding a new payment method - no modification to existing code!
class CryptoPaymentHandler implements PaymentHandler {
  readonly methodType = 'crypto';

  canHandle(payment: Payment): boolean {
    return payment.method === this.methodType;
  }

  validate(payment: Payment): ValidationResult {
    const errors: string[] = [];
    if (!payment.walletAddress) {
      errors.push('Wallet address required');
    }
    if (!payment.cryptoCurrency) {
      errors.push('Cryptocurrency type required');
    }
    return { isValid: errors.length === 0, errors };
  }

  async process(payment: Payment): Promise<PaymentResult> {
    // Crypto processing logic
    return { success: true, transactionId: generateId() };
  }
}

// Application setup - register handlers
const processor = new PaymentProcessor();
processor.registerHandler(new CreditCardPaymentHandler());
processor.registerHandler(new PayPalPaymentHandler());
processor.registerHandler(new CryptoPaymentHandler());
// Add new payment methods by simply registering new handlers
```

## Why

1. **Stability**: The `PaymentProcessor` class is stable and tested. Adding new payment methods doesn't risk breaking existing functionality.

2. **Team Scalability**: Different developers can work on different payment handlers simultaneously without merge conflicts.

3. **Plugin Architecture**: Payment handlers can be loaded dynamically, even from external packages or plugins.

4. **Testing Isolation**: Each handler is tested independently. New handlers don't require retesting old ones.

5. **Feature Flags**: New payment methods can be enabled/disabled by simply registering or not registering the handler.

6. **Compliance**: When regulations change for one payment method, only that handler needs updating.

7. **Rollback Safety**: If a new payment handler has issues, remove it without touching proven code.
