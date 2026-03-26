---
id: core-dry
title: Don't Repeat Yourself (DRY)
category: core-principles
priority: critical
tags: [DRY, duplication, single-source-of-truth, maintainability]
related: [core-dry-extraction, core-dry-single-source, solid-srp-class]
---

# Don't Repeat Yourself (DRY)

## Why It Matters

"Don't Repeat Yourself" means every piece of knowledge should have a single, authoritative representation. Duplication leads to inconsistencies, increases maintenance burden, and makes bugs harder to fix. When you change one copy but forget others, bugs creep in.

## Incorrect

```typescript
// ❌ Duplicated validation logic
class UserController {
  createUser(data) {
    if (!data.email || !data.email.includes('@')) {
      throw new Error('Invalid email');
    }
    if (!data.password || data.password.length < 8) {
      throw new Error('Password too short');
    }
    // create user...
  }

  updateUser(id, data) {
    if (!data.email || !data.email.includes('@')) { // Duplicated
      throw new Error('Invalid email');
    }
    if (!data.password || data.password.length < 8) { // Duplicated
      throw new Error('Password too short');
    }
    // update user...
  }
}

// ❌ Duplicated business rules
function calculateOrderTotal(items) {
  let total = 0;
  for (const item of items) {
    total += item.price * item.quantity;
  }
  if (total > 100) {
    total = total * 0.9; // 10% discount over $100
  }
  return total;
}

function calculateCartTotal(cartItems) {
  let total = 0;
  for (const item of cartItems) {
    total += item.price * item.quantity; // Duplicated
  }
  if (total > 100) {
    total = total * 0.9; // Duplicated discount logic
  }
  return total;
}

// ❌ Duplicated constants
// file: checkout.ts
const TAX_RATE = 0.08;
const FREE_SHIPPING_THRESHOLD = 50;

// file: cart.ts
const TAX_RATE = 0.08; // Duplicated
const FREE_SHIPPING_THRESHOLD = 50; // Duplicated
```

## Correct

### Extract Shared Validation

```typescript
// ✅ Single validation module
// validators/user.ts
export class UserValidator {
  static validateEmail(email: string): void {
    if (!email || !email.includes('@')) {
      throw new ValidationError('Invalid email address');
    }
  }

  static validatePassword(password: string): void {
    if (!password || password.length < 8) {
      throw new ValidationError('Password must be at least 8 characters');
    }
  }

  static validate(data: UserData): void {
    this.validateEmail(data.email);
    this.validatePassword(data.password);
  }
}

// controller.ts
class UserController {
  createUser(data) {
    UserValidator.validate(data);
    // create user...
  }

  updateUser(id, data) {
    UserValidator.validate(data);
    // update user...
  }
}
```

### Extract Shared Business Logic

```typescript
// ✅ Single source of truth for pricing
// services/pricing.ts
export class PricingService {
  private static readonly BULK_DISCOUNT_THRESHOLD = 100;
  private static readonly BULK_DISCOUNT_RATE = 0.1;

  static calculateSubtotal(items: LineItem[]): number {
    return items.reduce(
      (sum, item) => sum + item.price * item.quantity,
      0
    );
  }

  static applyDiscount(subtotal: number): number {
    if (subtotal > this.BULK_DISCOUNT_THRESHOLD) {
      return subtotal * (1 - this.BULK_DISCOUNT_RATE);
    }
    return subtotal;
  }

  static calculateTotal(items: LineItem[]): number {
    const subtotal = this.calculateSubtotal(items);
    return this.applyDiscount(subtotal);
  }
}

// Both order and cart use the same logic
const orderTotal = PricingService.calculateTotal(order.items);
const cartTotal = PricingService.calculateTotal(cart.items);
```

### Centralize Constants

```typescript
// ✅ Single constants file
// constants/pricing.ts
export const PRICING = {
  TAX_RATE: 0.08,
  FREE_SHIPPING_THRESHOLD: 50,
  BULK_DISCOUNT_THRESHOLD: 100,
  BULK_DISCOUNT_RATE: 0.1,
} as const;

// Used everywhere
import { PRICING } from '@/constants/pricing';

const tax = subtotal * PRICING.TAX_RATE;
const freeShipping = total >= PRICING.FREE_SHIPPING_THRESHOLD;
```

### Extract Shared Components

```tsx
// ❌ Duplicated UI patterns
function UserCard({ user }) {
  return (
    <div className="p-4 rounded-lg shadow bg-white">
      <img src={user.avatar} className="w-12 h-12 rounded-full" />
      <h3 className="font-bold">{user.name}</h3>
      <p className="text-gray-600">{user.email}</p>
    </div>
  );
}

function TeamMemberCard({ member }) {
  return (
    <div className="p-4 rounded-lg shadow bg-white"> {/* Same styles */}
      <img src={member.avatar} className="w-12 h-12 rounded-full" />
      <h3 className="font-bold">{member.name}</h3>
      <p className="text-gray-600">{member.role}</p>
    </div>
  );
}

// ✅ Reusable component
function Card({ children, className }) {
  return (
    <div className={cn("p-4 rounded-lg shadow bg-white", className)}>
      {children}
    </div>
  );
}

function Avatar({ src, alt }) {
  return <img src={src} alt={alt} className="w-12 h-12 rounded-full" />;
}

function UserCard({ user }) {
  return (
    <Card>
      <Avatar src={user.avatar} alt={user.name} />
      <h3 className="font-bold">{user.name}</h3>
      <p className="text-gray-600">{user.email}</p>
    </Card>
  );
}

function TeamMemberCard({ member }) {
  return (
    <Card>
      <Avatar src={member.avatar} alt={member.name} />
      <h3 className="font-bold">{member.name}</h3>
      <p className="text-gray-600">{member.role}</p>
    </Card>
  );
}
```

### Extract Shared Hooks

```typescript
// ❌ Duplicated fetch logic
function UserProfile() {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    fetch('/api/user')
      .then(res => res.json())
      .then(setUser)
      .catch(setError)
      .finally(() => setLoading(false));
  }, []);
  // ...
}

function ProductList() {
  const [products, setProducts] = useState(null);
  const [loading, setLoading] = useState(true); // Duplicated
  const [error, setError] = useState(null); // Duplicated

  useEffect(() => {
    fetch('/api/products') // Same pattern
      .then(res => res.json())
      .then(setProducts)
      .catch(setError)
      .finally(() => setLoading(false));
  }, []);
  // ...
}

// ✅ Custom hook (or use React Query)
function useFetch<T>(url: string) {
  const [data, setData] = useState<T | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<Error | null>(null);

  useEffect(() => {
    fetch(url)
      .then(res => res.json())
      .then(setData)
      .catch(setError)
      .finally(() => setLoading(false));
  }, [url]);

  return { data, loading, error };
}

function UserProfile() {
  const { data: user, loading, error } = useFetch<User>('/api/user');
  // ...
}

function ProductList() {
  const { data: products, loading, error } = useFetch<Product[]>('/api/products');
  // ...
}
```

## When NOT to DRY

```typescript
// ⚠️ Don't extract coincidentally similar code
// These might look similar but serve different purposes

function validateUserAge(age: number) {
  return age >= 18; // Legal adult age
}

function validateMinimumOrderQuantity(quantity: number) {
  return quantity >= 18; // Business rule: minimum order
}

// These should remain separate even though both check >= 18
// Their reasons for change are different
```

## Rule of Three

```
Wait until you see duplication three times before extracting.
Two occurrences might be coincidental. Three indicates a pattern.
```

## Benefits

- Single source of truth
- Fix bugs in one place
- Consistent behavior across codebase
- Easier refactoring
- Reduced code size
- Lower maintenance cost
