---
id: core-kiss-readability
title: KISS - Readability
category: core-principles
priority: critical
tags: [KISS, readability, clear-code, maintainability]
related: [core-kiss-simplicity, solid-srp-function]
---

# KISS - Readability

Code is read far more often than it is written. Optimize for readability by using clear names, straightforward logic, and avoiding clever tricks that obscure intent.

## Bad Example

```typescript
// Anti-pattern: Clever but cryptic code

// One-liner that's hard to understand
const r = d.filter(x => x.s === 'a' && x.t > Date.now() - 864e5).reduce((a, x) => ({ ...a, [x.c]: (a[x.c] || 0) + x.v }), {});

// Nested ternaries
const status = x > 100 ? 'high' : x > 50 ? 'medium' : x > 20 ? 'low' : x > 0 ? 'minimal' : 'none';

// Bitwise operations for boolean logic
const isValid = !!(flags & 0x1) && !!(flags & 0x2) || !!(flags & 0x4);

// Regex that nobody can read
const isValidEmail = /^(?:[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])$/i.test(email);

// Clever use of || and && for control flow
user && user.isActive && user.hasPermission('admin') && doAdminStuff() || showError();

// Abusing array methods
const result = [...Array(10)].map((_, i) => i * 2).filter(Boolean).reduce((a, b) => a + b, 0);

// Short variable names that save typing but cost understanding
function p(d, o) {
  return d.map(i => ({ ...i, t: i.t * o.r, s: o.s ? i.s + o.s : i.s })).filter(i => i.t > o.m);
}
```

## Good Example

```typescript
// Correct approach: Clear, self-documenting code

// Break complex operations into named steps
function getActiveOrdersSummaryByCategory(orders: Order[]): Record<string, number> {
  const oneDayAgo = Date.now() - 24 * 60 * 60 * 1000;

  const activeRecentOrders = orders.filter(order =>
    order.status === 'active' && order.timestamp > oneDayAgo
  );

  const summaryByCategory: Record<string, number> = {};

  for (const order of activeRecentOrders) {
    const currentTotal = summaryByCategory[order.category] || 0;
    summaryByCategory[order.category] = currentTotal + order.value;
  }

  return summaryByCategory;
}

// Use clear conditional logic
function getAlertLevel(value: number): AlertLevel {
  if (value > 100) {
    return 'high';
  }
  if (value > 50) {
    return 'medium';
  }
  if (value > 20) {
    return 'low';
  }
  if (value > 0) {
    return 'minimal';
  }
  return 'none';
}

// Use named constants for flags
const UserFlags = {
  IS_VERIFIED: 0x1,
  IS_PREMIUM: 0x2,
  IS_ADMIN: 0x4
} as const;

function hasUserFlag(flags: number, flag: number): boolean {
  return (flags & flag) !== 0;
}

function isValidPremiumAdmin(flags: number): boolean {
  const isVerified = hasUserFlag(flags, UserFlags.IS_VERIFIED);
  const isPremium = hasUserFlag(flags, UserFlags.IS_PREMIUM);
  const isAdmin = hasUserFlag(flags, UserFlags.IS_ADMIN);

  return (isVerified && isPremium) || isAdmin;
}

// Use a simple email validation
function isValidEmail(email: string): boolean {
  if (!email || email.length > 254) {
    return false;
  }

  const atIndex = email.indexOf('@');
  if (atIndex < 1) {
    return false;
  }

  const domain = email.slice(atIndex + 1);
  if (!domain || !domain.includes('.')) {
    return false;
  }

  return true;
}

// Or use a well-tested library with clear intent
import { isEmail } from 'validator';
const isValidEmail = isEmail(email);

// Use explicit control flow
function handleUserAction(user: User | null): void {
  if (!user) {
    showError('User not found');
    return;
  }

  if (!user.isActive) {
    showError('User account is inactive');
    return;
  }

  if (!user.hasPermission('admin')) {
    showError('Admin permission required');
    return;
  }

  doAdminStuff();
}

// Use descriptive variable and function names
function generateEvenNumbersSum(count: number): number {
  const evenNumbers: number[] = [];

  for (let i = 0; i < count; i++) {
    evenNumbers.push(i * 2);
  }

  const sum = evenNumbers.reduce((total, num) => total + num, 0);

  return sum;
}

// Use full, descriptive parameter names
interface PriceAdjustmentOptions {
  rateMultiplier: number;
  shippingSurcharge?: number;
  minimumThreshold: number;
}

function adjustProductPrices(
  products: Product[],
  options: PriceAdjustmentOptions
): Product[] {
  return products
    .map(product => ({
      ...product,
      totalPrice: product.basePrice * options.rateMultiplier,
      shippingCost: options.shippingSurcharge
        ? product.shippingCost + options.shippingSurcharge
        : product.shippingCost
    }))
    .filter(product => product.totalPrice > options.minimumThreshold);
}

// Usage is self-documenting
const adjustedProducts = adjustProductPrices(products, {
  rateMultiplier: 1.1,
  shippingSurcharge: 5,
  minimumThreshold: 20
});
```

## Why

1. **Comprehension Speed**: Readable code is understood quickly. Clever code requires deciphering.

2. **Fewer Bugs**: When code clearly expresses intent, mistakes are obvious.

3. **Onboarding**: New team members can contribute faster with readable code.

4. **Code Reviews**: Reviewers can focus on logic, not translation.

5. **Future You**: Code you wrote 6 months ago might as well have been written by someone else.

6. **Maintenance Cost**: Most of a codebase's lifetime is spent in maintenance, not initial development.

7. **Collaboration**: Teams work better when everyone can understand everyone's code.
