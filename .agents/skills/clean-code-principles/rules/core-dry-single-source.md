---
id: core-dry-single-source
title: DRY - Single Source of Truth
category: core-principles
priority: critical
tags: [DRY, single-source-of-truth, constants, configuration]
related: [core-dry, core-dry-extraction, core-encapsulation]
---

# DRY - Single Source of Truth

Every piece of knowledge or configuration should exist in exactly one place. When data or logic needs to be referenced from multiple locations, use a single authoritative source.

## Bad Example

```typescript
// Anti-pattern: Same values defined in multiple places

// In constants file
const API_BASE_URL = 'https://api.example.com/v1';

// In another file - duplicate!
const baseUrl = 'https://api.example.com/v1';

// In config.ts - another duplicate!
export const config = {
  apiUrl: 'https://api.example.com/v1'
};

// In API client - yet another!
class ApiClient {
  private baseUrl = 'https://api.example.com/v1'; // Duplicated!
}

// Status codes defined in multiple places
class OrderService {
  async getOrder(id: string): Promise<Order> {
    const order = await this.repository.findById(id);
    if (order.status === 'pending') { // Magic string
      // ...
    }
    if (order.status === 'completed') { // Magic string
      // ...
    }
  }
}

class OrderController {
  async listPendingOrders(): Promise<Order[]> {
    return this.repository.findByStatus('pending'); // Same magic string
  }
}

// In frontend code
const isPending = order.status === 'pending'; // And again

// Database seeds with duplicated data
const seedRoles = [
  { id: 1, name: 'admin', permissions: ['read', 'write', 'delete', 'admin'] },
  { id: 2, name: 'editor', permissions: ['read', 'write'] },
  { id: 3, name: 'viewer', permissions: ['read'] }
];

// In authorization middleware - duplicated permission logic
function checkPermission(user: User, action: string): boolean {
  if (user.role === 'admin') {
    return true; // Admin can do anything - duplicated knowledge
  }
  if (user.role === 'editor' && ['read', 'write'].includes(action)) {
    return true; // Editor permissions - duplicated
  }
  if (user.role === 'viewer' && action === 'read') {
    return true; // Viewer permissions - duplicated
  }
  return false;
}
```

## Good Example

```typescript
// Correct approach: Single source of truth for all shared knowledge

// Configuration - one authoritative source
// config/index.ts
export const Config = {
  api: {
    baseUrl: process.env.API_BASE_URL || 'https://api.example.com/v1',
    timeout: Number(process.env.API_TIMEOUT) || 30000,
    retries: Number(process.env.API_RETRIES) || 3
  },
  database: {
    url: process.env.DATABASE_URL!,
    poolSize: Number(process.env.DB_POOL_SIZE) || 10
  }
} as const;

// All code references the single config
class ApiClient {
  constructor(private baseUrl: string = Config.api.baseUrl) {}
}

// Enums for finite sets of values - single source of truth
// domain/order/status.ts
export const OrderStatus = {
  PENDING: 'pending',
  PROCESSING: 'processing',
  SHIPPED: 'shipped',
  DELIVERED: 'delivered',
  CANCELLED: 'cancelled'
} as const;

export type OrderStatus = typeof OrderStatus[keyof typeof OrderStatus];

// All code uses the enum
class OrderService {
  async getOrder(id: string): Promise<Order> {
    const order = await this.repository.findById(id);
    if (order.status === OrderStatus.PENDING) {
      // Single source of truth
    }
  }
}

class OrderController {
  async listPendingOrders(): Promise<Order[]> {
    return this.repository.findByStatus(OrderStatus.PENDING); // Same source
  }
}

// Roles and permissions - single authoritative definition
// domain/auth/roles.ts
export const Permission = {
  READ: 'read',
  WRITE: 'write',
  DELETE: 'delete',
  ADMIN: 'admin'
} as const;

export type Permission = typeof Permission[keyof typeof Permission];

export const RoleDefinitions = {
  admin: {
    name: 'Administrator',
    permissions: [Permission.READ, Permission.WRITE, Permission.DELETE, Permission.ADMIN]
  },
  editor: {
    name: 'Editor',
    permissions: [Permission.READ, Permission.WRITE]
  },
  viewer: {
    name: 'Viewer',
    permissions: [Permission.READ]
  }
} as const;

export type RoleName = keyof typeof RoleDefinitions;

// Permission checking uses the definitions
export function hasPermission(role: RoleName, permission: Permission): boolean {
  const roleDef = RoleDefinitions[role];
  return roleDef.permissions.includes(permission);
}

// Database seeds generated from the single source
export function generateRoleSeeds(): RoleSeed[] {
  return Object.entries(RoleDefinitions).map(([key, def], index) => ({
    id: index + 1,
    name: key,
    displayName: def.name,
    permissions: [...def.permissions]
  }));
}

// Validation rules - single source
// domain/user/validation.ts
export const UserValidationRules = {
  email: {
    maxLength: 255,
    pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  },
  password: {
    minLength: 8,
    maxLength: 128,
    requireUppercase: true,
    requireLowercase: true,
    requireNumber: true,
    requireSpecial: false
  },
  username: {
    minLength: 3,
    maxLength: 30,
    pattern: /^[a-zA-Z0-9_]+$/
  }
} as const;

// Validators use the rules
export function validateEmail(email: string): ValidationResult {
  const rules = UserValidationRules.email;

  if (email.length > rules.maxLength) {
    return { valid: false, error: `Email must be ${rules.maxLength} characters or less` };
  }
  if (!rules.pattern.test(email)) {
    return { valid: false, error: 'Invalid email format' };
  }
  return { valid: true };
}

// Database schema uses the same rules
// migrations/001_create_users.ts
export const createUsersTable = `
  CREATE TABLE users (
    id UUID PRIMARY KEY,
    email VARCHAR(${UserValidationRules.email.maxLength}) NOT NULL UNIQUE,
    username VARCHAR(${UserValidationRules.username.maxLength}) NOT NULL UNIQUE,
    -- ...
  )
`;

// Frontend validation uses the same rules (shared module)
// shared/validation.ts (used by both frontend and backend)
export function getPasswordRequirements(): string[] {
  const rules = UserValidationRules.password;
  const requirements: string[] = [];

  requirements.push(`At least ${rules.minLength} characters`);
  if (rules.requireUppercase) requirements.push('At least one uppercase letter');
  if (rules.requireLowercase) requirements.push('At least one lowercase letter');
  if (rules.requireNumber) requirements.push('At least one number');
  if (rules.requireSpecial) requirements.push('At least one special character');

  return requirements;
}

// Error messages - single source
// errors/messages.ts
export const ErrorMessages = {
  user: {
    notFound: 'User not found',
    alreadyExists: 'A user with this email already exists',
    invalidCredentials: 'Invalid email or password',
    accountLocked: 'Account is locked. Please contact support.'
  },
  order: {
    notFound: 'Order not found',
    alreadyCancelled: 'Order has already been cancelled',
    cannotCancel: 'Order cannot be cancelled in its current state'
  },
  auth: {
    tokenExpired: 'Your session has expired. Please log in again.',
    unauthorized: 'You do not have permission to perform this action'
  }
} as const;

// All code uses the same error messages
class UserService {
  async findById(id: string): Promise<User> {
    const user = await this.repository.findById(id);
    if (!user) {
      throw new NotFoundError(ErrorMessages.user.notFound);
    }
    return user;
  }
}
```

## Why

1. **Consistency**: The same value is always the same everywhere. No "this worked yesterday" bugs.

2. **Easy Updates**: Change a validation rule, config value, or status code in one place.

3. **Prevents Drift**: Without a single source, values diverge over time as different developers make changes.

4. **Documentation**: The source file serves as documentation for what values are valid.

5. **Type Safety**: TypeScript can enforce that only valid values are used.

6. **Searchability**: Easy to find all usages by searching for the constant name.

7. **Refactoring**: Rename a status? Change it in one place and let the compiler find all usages.
