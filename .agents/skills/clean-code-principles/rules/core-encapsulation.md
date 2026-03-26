---
id: core-encapsulation
title: Encapsulation
category: core-principles
priority: critical
tags: [encapsulation, information-hiding, data-protection]
related: [solid-srp-class, core-law-demeter, solid-isp-interfaces]
---

# Encapsulation

Hide internal implementation details and expose only what's necessary through a well-defined interface. Protect data integrity by controlling access to internal state.

## Bad Example

```typescript
// Anti-pattern: Exposed internals, no encapsulation

class BankAccount {
  // Public fields - anyone can modify directly
  public accountNumber: string;
  public balance: number;
  public transactions: Transaction[];
  public overdraftLimit: number;
  public isLocked: boolean;

  constructor(accountNumber: string, initialBalance: number) {
    this.accountNumber = accountNumber;
    this.balance = initialBalance;
    this.transactions = [];
    this.overdraftLimit = 0;
    this.isLocked = false;
  }
}

// External code can violate business rules
const account = new BankAccount('12345', 1000);

// Direct modification bypasses validation
account.balance = -999999; // Negative balance without check!
account.balance = account.balance + 1000; // No transaction record!

// Can manipulate transaction history
account.transactions.push({
  id: 'fake',
  amount: 1000000,
  type: 'deposit'
}); // Fraudulent transaction!

// Can unlock locked accounts
account.isLocked = false; // Bypasses security!

// Can change overdraft without authorization
account.overdraftLimit = 100000; // Unauthorized overdraft!

// Other classes depend on internal structure
class AccountReport {
  generate(account: BankAccount): Report {
    // Directly accesses internal array
    const deposits = account.transactions.filter(t => t.type === 'deposit');
    const withdrawals = account.transactions.filter(t => t.type === 'withdrawal');

    // Depends on internal structure of Transaction
    const totalDeposits = deposits.reduce((sum, t) => sum + t.amount, 0);

    return {
      balance: account.balance,
      totalDeposits,
      transactionCount: account.transactions.length
    };
  }
}

// Problems:
// 1. Anyone can modify balance without recording transaction
// 2. Business rules can be bypassed
// 3. No audit trail for changes
// 4. Internal structure changes break external code
// 5. No way to add validation later without breaking changes
```

## Good Example

```typescript
// Correct approach: Proper encapsulation

class BankAccount {
  private readonly _accountNumber: string;
  private _balance: number;
  private readonly _transactions: Transaction[] = [];
  private _overdraftLimit: number = 0;
  private _isLocked: boolean = false;
  private _lockReason: string | null = null;

  constructor(accountNumber: string, initialBalance: number) {
    if (!accountNumber || accountNumber.length < 5) {
      throw new Error('Invalid account number');
    }
    if (initialBalance < 0) {
      throw new Error('Initial balance cannot be negative');
    }

    this._accountNumber = accountNumber;
    this._balance = initialBalance;
    this._transactions.push(
      Transaction.createInitial(initialBalance)
    );
  }

  // Read-only access to account number
  get accountNumber(): string {
    return this._accountNumber;
  }

  // Read-only access to balance
  get balance(): number {
    return this._balance;
  }

  // Read-only access to lock status
  get isLocked(): boolean {
    return this._isLocked;
  }

  // Controlled deposit with validation and audit trail
  deposit(amount: number, description: string = 'Deposit'): Transaction {
    this.ensureNotLocked();

    if (amount <= 0) {
      throw new InvalidAmountError('Deposit amount must be positive');
    }

    this._balance += amount;

    const transaction = Transaction.createDeposit(amount, description, this._balance);
    this._transactions.push(transaction);

    return transaction;
  }

  // Controlled withdrawal with business rules
  withdraw(amount: number, description: string = 'Withdrawal'): Transaction {
    this.ensureNotLocked();

    if (amount <= 0) {
      throw new InvalidAmountError('Withdrawal amount must be positive');
    }

    const availableBalance = this._balance + this._overdraftLimit;
    if (amount > availableBalance) {
      throw new InsufficientFundsError(amount, availableBalance);
    }

    this._balance -= amount;

    const transaction = Transaction.createWithdrawal(amount, description, this._balance);
    this._transactions.push(transaction);

    return transaction;
  }

  // Transfer with proper validation
  transferTo(recipient: BankAccount, amount: number): TransferResult {
    this.ensureNotLocked();
    recipient.ensureNotLocked();

    if (amount <= 0) {
      throw new InvalidAmountError('Transfer amount must be positive');
    }

    const withdrawalTx = this.withdraw(amount, `Transfer to ${recipient.accountNumber}`);
    const depositTx = recipient.deposit(amount, `Transfer from ${this._accountNumber}`);

    return { withdrawalTx, depositTx };
  }

  // Controlled overdraft limit modification
  setOverdraftLimit(limit: number, authorizedBy: string): void {
    if (limit < 0) {
      throw new Error('Overdraft limit cannot be negative');
    }
    if (limit > 10000) {
      throw new Error('Overdraft limit exceeds maximum allowed');
    }

    this._overdraftLimit = limit;

    // Audit trail for limit changes
    this._transactions.push(
      Transaction.createAdministrative(
        `Overdraft limit set to ${limit} by ${authorizedBy}`
      )
    );
  }

  // Security control with audit
  lock(reason: string): void {
    this._isLocked = true;
    this._lockReason = reason;
    this._transactions.push(
      Transaction.createAdministrative(`Account locked: ${reason}`)
    );
  }

  unlock(authorizedBy: string): void {
    this._isLocked = false;
    this._lockReason = null;
    this._transactions.push(
      Transaction.createAdministrative(`Account unlocked by ${authorizedBy}`)
    );
  }

  // Return copy of transactions, not the internal array
  getTransactionHistory(): ReadonlyArray<Transaction> {
    return [...this._transactions];
  }

  // Provide summary without exposing internals
  getSummary(): AccountSummary {
    const deposits = this._transactions.filter(t => t.type === 'deposit');
    const withdrawals = this._transactions.filter(t => t.type === 'withdrawal');

    return {
      accountNumber: this._accountNumber,
      balance: this._balance,
      isLocked: this._isLocked,
      totalDeposits: deposits.reduce((sum, t) => sum + t.amount, 0),
      totalWithdrawals: withdrawals.reduce((sum, t) => sum + t.amount, 0),
      transactionCount: this._transactions.length
    };
  }

  // Private helper method
  private ensureNotLocked(): void {
    if (this._isLocked) {
      throw new AccountLockedError(this._lockReason || 'Account is locked');
    }
  }
}

// Transaction is also encapsulated
class Transaction {
  private constructor(
    public readonly id: string,
    public readonly type: TransactionType,
    public readonly amount: number,
    public readonly description: string,
    public readonly balanceAfter: number,
    public readonly timestamp: Date
  ) {}

  static createDeposit(amount: number, description: string, balanceAfter: number): Transaction {
    return new Transaction(
      generateId(),
      'deposit',
      amount,
      description,
      balanceAfter,
      new Date()
    );
  }

  static createWithdrawal(amount: number, description: string, balanceAfter: number): Transaction {
    return new Transaction(
      generateId(),
      'withdrawal',
      amount,
      description,
      balanceAfter,
      new Date()
    );
  }

  static createInitial(balance: number): Transaction {
    return new Transaction(
      generateId(),
      'initial',
      balance,
      'Account opened',
      balance,
      new Date()
    );
  }

  static createAdministrative(description: string): Transaction {
    return new Transaction(
      generateId(),
      'administrative',
      0,
      description,
      0,
      new Date()
    );
  }
}

// Usage - business rules are enforced
const account = new BankAccount('12345', 1000);

// Proper deposit with audit trail
account.deposit(500, 'Paycheck');

// Withdrawal validates funds
try {
  account.withdraw(2000); // Will throw InsufficientFundsError
} catch (error) {
  console.log('Cannot withdraw more than available');
}

// Cannot manipulate balance directly
// account.balance = 999999; // Error: Property 'balance' is read-only

// Cannot manipulate transactions
// account.getTransactionHistory().push(fakeTx); // Original array unaffected

// Account summary provides what reports need
const summary = account.getSummary();
console.log(`Balance: ${summary.balance}, Transactions: ${summary.transactionCount}`);
```

## Why

1. **Data Integrity**: Balance can only change through proper deposit/withdraw methods that maintain consistency.

2. **Business Rules**: All rules (positive amounts, sufficient funds, locked accounts) are enforced in one place.

3. **Audit Trail**: Every change is recorded. Cannot modify history without proper methods.

4. **Flexibility**: Internal representation can change without affecting clients. Switch from array to database later.

5. **Security**: Cannot bypass validation or manipulate internal state directly.

6. **Testing**: Can verify all edge cases through the public interface. Internal state is controlled.

7. **Documentation**: The public interface documents what operations are allowed and how to use them.
