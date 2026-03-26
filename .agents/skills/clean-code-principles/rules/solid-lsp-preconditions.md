---
id: solid-lsp-preconditions
title: SOLID - Liskov Substitution (Preconditions)
category: solid-principles
priority: critical
tags: [SOLID, LSP, liskov-substitution, preconditions, postconditions]
related: [solid-lsp-contracts, core-fail-fast, solid-ocp-abstraction]
---

# Liskov Substitution Principle - Preconditions and Postconditions

Subtypes cannot strengthen preconditions (require more) or weaken postconditions (guarantee less) compared to their base types.

## Bad Example

```typescript
// Anti-pattern: Subclass strengthens preconditions and weakens postconditions

interface PaymentProcessor {
  // Contract: accepts any amount > 0, returns transaction ID
  processPayment(amount: number): Promise<string>;
}

class StandardPaymentProcessor implements PaymentProcessor {
  async processPayment(amount: number): Promise<string> {
    if (amount <= 0) {
      throw new Error('Amount must be positive');
    }
    // Process payment and return transaction ID
    return `TXN-${Date.now()}`;
  }
}

// Violates LSP: Strengthens preconditions
class PremiumPaymentProcessor implements PaymentProcessor {
  private readonly minimumAmount = 100; // Stronger precondition!
  private readonly maximumAmount = 10000; // Stronger precondition!

  async processPayment(amount: number): Promise<string> {
    if (amount <= 0) {
      throw new Error('Amount must be positive');
    }
    // Additional restrictions not in base contract
    if (amount < this.minimumAmount) {
      throw new Error(`Minimum amount is ${this.minimumAmount}`); // Violates LSP!
    }
    if (amount > this.maximumAmount) {
      throw new Error(`Maximum amount is ${this.maximumAmount}`); // Violates LSP!
    }
    return `PREM-TXN-${Date.now()}`;
  }
}

// Violates LSP: Weakens postconditions
class UnreliablePaymentProcessor implements PaymentProcessor {
  async processPayment(amount: number): Promise<string> {
    if (amount <= 0) {
      throw new Error('Amount must be positive');
    }

    // Sometimes returns null instead of transaction ID - weaker postcondition!
    if (Math.random() > 0.5) {
      return null as any; // Violates the guarantee of returning a string
    }

    return `TXN-${Date.now()}`;
  }
}

// Client code breaks when substituting implementations
async function checkout(processor: PaymentProcessor, amount: number): Promise<void> {
  const txnId = await processor.processPayment(amount);
  // Assumes txnId is always a valid string (as per contract)
  console.log(`Payment complete: ${txnId.toUpperCase()}`); // Crashes with null!
}

// Works with StandardPaymentProcessor
await checkout(new StandardPaymentProcessor(), 50);

// Fails with PremiumPaymentProcessor - amount too low
await checkout(new PremiumPaymentProcessor(), 50); // Error: Minimum amount is 100

// Fails with UnreliablePaymentProcessor - null txnId
await checkout(new UnreliablePaymentProcessor(), 50); // Error: Cannot read toUpperCase of null
```

## Good Example

```typescript
// Correct approach: Subtypes honor preconditions and postconditions

// Clear contract with documented preconditions and postconditions
interface PaymentProcessor {
  /**
   * Process a payment transaction.
   *
   * @precondition amount > 0
   * @postcondition returns a non-empty transaction ID string
   * @throws PaymentError if payment fails (not for invalid preconditions)
   */
  processPayment(amount: number): Promise<PaymentResult>;

  /**
   * Check if this processor can handle the given amount.
   * Allows clients to check before attempting payment.
   */
  canProcess(amount: number): boolean;

  /**
   * Get the constraints of this processor.
   */
  getConstraints(): ProcessorConstraints;
}

interface PaymentResult {
  readonly transactionId: string;
  readonly processedAt: Date;
  readonly amount: number;
  readonly status: 'success' | 'pending';
}

interface ProcessorConstraints {
  readonly minAmount: number;
  readonly maxAmount: number;
  readonly supportedCurrencies: string[];
}

// Base implementation with standard constraints
class StandardPaymentProcessor implements PaymentProcessor {
  private readonly constraints: ProcessorConstraints = {
    minAmount: 0.01,
    maxAmount: Infinity,
    supportedCurrencies: ['USD', 'EUR', 'GBP']
  };

  getConstraints(): ProcessorConstraints {
    return this.constraints;
  }

  canProcess(amount: number): boolean {
    return amount >= this.constraints.minAmount &&
           amount <= this.constraints.maxAmount;
  }

  async processPayment(amount: number): Promise<PaymentResult> {
    // Precondition check (same as interface contract)
    if (amount <= 0) {
      throw new InvalidAmountError('Amount must be positive');
    }

    // Implementation
    const transactionId = `TXN-${Date.now()}-${Math.random().toString(36).slice(2, 11)}`;

    // Postcondition: always returns valid PaymentResult
    return {
      transactionId,
      processedAt: new Date(),
      amount,
      status: 'success'
    };
  }
}

// Premium processor with different constraints but same contract behavior
class PremiumPaymentProcessor implements PaymentProcessor {
  private readonly constraints: ProcessorConstraints = {
    minAmount: 100,
    maxAmount: 100000,
    supportedCurrencies: ['USD', 'EUR', 'GBP', 'JPY', 'CHF']
  };

  getConstraints(): ProcessorConstraints {
    return this.constraints;
  }

  // Clients can check constraints before calling processPayment
  canProcess(amount: number): boolean {
    return amount >= this.constraints.minAmount &&
           amount <= this.constraints.maxAmount;
  }

  async processPayment(amount: number): Promise<PaymentResult> {
    // Same precondition as interface - not strengthened
    if (amount <= 0) {
      throw new InvalidAmountError('Amount must be positive');
    }

    // Business logic can still reject, but with proper error handling
    if (!this.canProcess(amount)) {
      throw new PaymentError(
        `Amount ${amount} outside processor range [${this.constraints.minAmount}, ${this.constraints.maxAmount}]`
      );
    }

    // Premium processing logic
    const transactionId = `PREM-${Date.now()}-${Math.random().toString(36).slice(2, 11)}`;

    // Postcondition: always returns valid PaymentResult (same guarantee)
    return {
      transactionId,
      processedAt: new Date(),
      amount,
      status: 'success'
    };
  }
}

// Factory that selects appropriate processor
class PaymentProcessorFactory {
  private processors: PaymentProcessor[] = [
    new StandardPaymentProcessor(),
    new PremiumPaymentProcessor()
  ];

  getProcessorFor(amount: number): PaymentProcessor {
    const suitable = this.processors.find(p => p.canProcess(amount));
    if (!suitable) {
      throw new NoSuitableProcessorError(amount);
    }
    return suitable;
  }
}

// Client code works correctly with any processor
async function checkout(amount: number, factory: PaymentProcessorFactory): Promise<void> {
  const processor = factory.getProcessorFor(amount);
  const result = await processor.processPayment(amount);

  // Postcondition guarantees result is valid
  console.log(`Payment complete: ${result.transactionId}`);
  console.log(`Processed at: ${result.processedAt.toISOString()}`);
}

// Usage
const factory = new PaymentProcessorFactory();

await checkout(50, factory);    // Uses StandardPaymentProcessor
await checkout(500, factory);   // Uses PremiumPaymentProcessor
await checkout(5000, factory);  // Uses PremiumPaymentProcessor
```

## Why

1. **Substitutability**: Any `PaymentProcessor` can be used interchangeably where `PaymentProcessor` is expected.

2. **Client Safety**: Clients can rely on the contract. If preconditions are met, postconditions are guaranteed.

3. **Explicit Constraints**: Instead of silently strengthening preconditions, processors expose their constraints through `getConstraints()` and `canProcess()`.

4. **Proper Error Handling**: Constraint violations throw appropriate errors that clients can handle, rather than unexpected failures.

5. **Factory Pattern**: The factory selects the appropriate processor, keeping constraint logic out of client code.

6. **Design by Contract**: Clear documentation of preconditions and postconditions makes the contract explicit.

7. **Defensive Programming**: Clients can check `canProcess()` before attempting payment, avoiding errors entirely.
