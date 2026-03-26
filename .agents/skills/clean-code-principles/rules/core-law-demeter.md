---
id: core-law-demeter
title: Law of Demeter
category: core-principles
priority: critical
tags: [law-of-demeter, coupling, encapsulation]
related: [core-encapsulation, solid-srp-class, core-separation-concerns]
---

# Law of Demeter

A method should only talk to its immediate friends, not to strangers. Don't reach through objects to access their internal structure. This reduces coupling and makes code more maintainable.

## Bad Example

```typescript
// Anti-pattern: Reaching through object chains

class Address {
  street: string;
  city: string;
  country: Country;
}

class Country {
  name: string;
  code: string;
  taxRules: TaxRules;
}

class TaxRules {
  vatRate: number;
  calculateTax(amount: number): number {
    return amount * this.vatRate;
  }
}

class Customer {
  name: string;
  address: Address;
  wallet: Wallet;
}

class Wallet {
  balance: number;
  currency: Currency;

  deduct(amount: number): void {
    this.balance -= amount;
  }
}

class Currency {
  code: string;
  exchangeRate: number;
}

class Order {
  customer: Customer;
  items: OrderItem[];

  // Violation: Reaching deep into customer's structure
  getCustomerCountry(): string {
    return this.customer.address.country.name; // 4 levels deep!
  }

  // Violation: Reaching into customer's wallet
  calculateTax(): number {
    const amount = this.getTotal();
    // Reaching through customer -> address -> country -> taxRules
    return this.customer.address.country.taxRules.calculateTax(amount);
  }

  // Violation: Manipulating customer's wallet directly
  processPayment(): void {
    const total = this.calculateTax() + this.getTotal();

    // Reaching into wallet to check and modify
    if (this.customer.wallet.balance < total) {
      throw new Error('Insufficient funds');
    }

    // Reaching into wallet's currency for conversion
    const exchangeRate = this.customer.wallet.currency.exchangeRate;
    const convertedAmount = total * exchangeRate;

    this.customer.wallet.deduct(convertedAmount);
  }

  getTotal(): number {
    return this.items.reduce((sum, item) => sum + item.price, 0);
  }
}

// Problems with this approach:
// 1. Order knows too much about Customer's internal structure
// 2. Changes to Address, Country, or Wallet break Order
// 3. Hard to test - must mock entire object graph
// 4. Tight coupling between unrelated classes
```

## Good Example

```typescript
// Correct approach: Talk only to immediate friends

class Address {
  private street: string;
  private city: string;
  private country: Country;

  constructor(street: string, city: string, country: Country) {
    this.street = street;
    this.city = city;
    this.country = country;
  }

  getCountryName(): string {
    return this.country.getName();
  }

  calculateTax(amount: number): number {
    return this.country.calculateTax(amount);
  }
}

class Country {
  private name: string;
  private code: string;
  private taxRules: TaxRules;

  constructor(name: string, code: string, taxRules: TaxRules) {
    this.name = name;
    this.code = code;
    this.taxRules = taxRules;
  }

  getName(): string {
    return this.name;
  }

  getCode(): string {
    return this.code;
  }

  calculateTax(amount: number): number {
    return this.taxRules.calculate(amount);
  }
}

class TaxRules {
  private vatRate: number;

  constructor(vatRate: number) {
    this.vatRate = vatRate;
  }

  calculate(amount: number): number {
    return amount * this.vatRate;
  }
}

class Wallet {
  private balance: number;
  private currency: Currency;

  constructor(balance: number, currency: Currency) {
    this.balance = balance;
    this.currency = currency;
  }

  canAfford(amount: number): boolean {
    const convertedAmount = this.currency.convert(amount);
    return this.balance >= convertedAmount;
  }

  pay(amount: number): PaymentResult {
    const convertedAmount = this.currency.convert(amount);

    if (!this.canAfford(amount)) {
      return { success: false, error: 'Insufficient funds' };
    }

    this.balance -= convertedAmount;
    return { success: true, amountPaid: convertedAmount };
  }

  getBalance(): number {
    return this.balance;
  }
}

class Currency {
  private code: string;
  private exchangeRate: number;

  constructor(code: string, exchangeRate: number) {
    this.code = code;
    this.exchangeRate = exchangeRate;
  }

  convert(amount: number): number {
    return amount * this.exchangeRate;
  }
}

class Customer {
  private name: string;
  private address: Address;
  private wallet: Wallet;

  constructor(name: string, address: Address, wallet: Wallet) {
    this.name = name;
    this.address = address;
    this.wallet = wallet;
  }

  getName(): string {
    return this.name;
  }

  getCountryName(): string {
    return this.address.getCountryName();
  }

  calculateTaxFor(amount: number): number {
    return this.address.calculateTax(amount);
  }

  canAfford(amount: number): boolean {
    return this.wallet.canAfford(amount);
  }

  pay(amount: number): PaymentResult {
    return this.wallet.pay(amount);
  }
}

class Order {
  private customer: Customer;
  private items: OrderItem[];

  constructor(customer: Customer, items: OrderItem[]) {
    this.customer = customer;
    this.items = items;
  }

  // Only talks to immediate friend (customer)
  getCustomerCountry(): string {
    return this.customer.getCountryName();
  }

  getTotal(): number {
    return this.items.reduce((sum, item) => sum + item.getPrice(), 0);
  }

  // Asks customer to calculate tax (customer knows how)
  calculateTax(): number {
    return this.customer.calculateTaxFor(this.getTotal());
  }

  getTotalWithTax(): number {
    return this.getTotal() + this.calculateTax();
  }

  // Asks customer to pay (customer handles wallet)
  processPayment(): PaymentResult {
    const total = this.getTotalWithTax();

    if (!this.customer.canAfford(total)) {
      return { success: false, error: 'Insufficient funds' };
    }

    return this.customer.pay(total);
  }
}

// Usage
const order = new Order(customer, items);
const result = order.processPayment();

if (result.success) {
  console.log(`Payment successful: ${result.amountPaid}`);
} else {
  console.log(`Payment failed: ${result.error}`);
}

// Testing is now easy - only mock the immediate friend
describe('Order', () => {
  it('should process payment through customer', () => {
    const mockCustomer: Customer = {
      getCountryName: () => 'USA',
      calculateTaxFor: (amount: number) => amount * 0.1,
      canAfford: () => true,
      pay: jest.fn().mockReturnValue({ success: true, amountPaid: 110 })
    } as any;

    const order = new Order(mockCustomer, [{ getPrice: () => 100 }]);
    const result = order.processPayment();

    expect(result.success).toBe(true);
    expect(mockCustomer.pay).toHaveBeenCalledWith(110);
  });
});
```

## Why

1. **Reduced Coupling**: Order only knows about Customer. Changes to Address, Country, or Wallet don't affect Order.

2. **Encapsulation**: Internal structure is hidden. Customer can change how it stores address without affecting clients.

3. **Easier Testing**: Mock only the immediate friend. No need to construct deep object graphs.

4. **Better Abstraction**: Customer is responsible for customer things. Order doesn't need to know about wallets.

5. **Maintainability**: When requirements change, changes are localized to the responsible class.

6. **Readability**: `customer.calculateTaxFor(amount)` is clearer than `customer.address.country.taxRules.calculateTax(amount)`.

7. **Flexibility**: Can change internal implementations without affecting clients. Customer could switch from wallet to payment service.
