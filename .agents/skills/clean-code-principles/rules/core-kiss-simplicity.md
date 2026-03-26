---
id: core-kiss-simplicity
title: KISS - Simplicity
category: core-principles
priority: critical
tags: [KISS, simplicity, over-engineering, maintainability]
related: [core-kiss-readability, core-yagni-abstractions, solid-srp-function]
---

# KISS Principle - Simplicity

Keep It Simple, Stupid. Choose the simplest solution that solves the problem. Avoid unnecessary complexity, over-engineering, and clever code that's hard to understand.

## Bad Example

```typescript
// Anti-pattern: Over-engineered solution for a simple problem

// Simple task: Check if a user can access a resource
// Over-engineered approach with unnecessary abstractions

interface AccessControlStrategy {
  evaluate(context: AccessContext): AccessDecision;
}

interface AccessContext {
  subject: Subject;
  resource: Resource;
  action: Action;
  environment: Environment;
}

interface Subject {
  id: string;
  attributes: Map<string, AttributeValue>;
}

interface Resource {
  id: string;
  type: string;
  attributes: Map<string, AttributeValue>;
}

interface Action {
  id: string;
  attributes: Map<string, AttributeValue>;
}

interface Environment {
  currentTime: Date;
  ipAddress: string;
  attributes: Map<string, AttributeValue>;
}

type AttributeValue = string | number | boolean | string[];

interface AccessDecision {
  decision: 'permit' | 'deny' | 'indeterminate' | 'not_applicable';
  obligations?: Obligation[];
  advice?: Advice[];
}

interface Obligation {
  id: string;
  fulfillOn: 'permit' | 'deny';
  attributes: Map<string, AttributeValue>;
}

interface Advice {
  id: string;
  appliesTo: 'permit' | 'deny';
  attributes: Map<string, AttributeValue>;
}

class AttributeBasedAccessControl {
  private strategies: AccessControlStrategy[] = [];
  private combiningAlgorithm: CombiningAlgorithm;

  constructor(combiningAlgorithm: CombiningAlgorithm) {
    this.combiningAlgorithm = combiningAlgorithm;
  }

  addStrategy(strategy: AccessControlStrategy): void {
    this.strategies.push(strategy);
  }

  evaluate(context: AccessContext): AccessDecision {
    const decisions = this.strategies.map(s => s.evaluate(context));
    return this.combiningAlgorithm.combine(decisions);
  }
}

// 500+ more lines of abstraction layers...

// Actually usage for a simple check:
const context: AccessContext = {
  subject: {
    id: user.id,
    attributes: new Map([['role', user.role]])
  },
  resource: {
    id: document.id,
    type: 'document',
    attributes: new Map([['ownerId', document.ownerId]])
  },
  action: {
    id: 'read',
    attributes: new Map()
  },
  environment: {
    currentTime: new Date(),
    ipAddress: request.ip,
    attributes: new Map()
  }
};

const decision = accessControl.evaluate(context);
if (decision.decision === 'permit') {
  // Allow access
}
```

## Good Example

```typescript
// Correct approach: Simple, direct solution

// Simple function that does what's needed
function canUserAccessDocument(user: User, document: Document, action: 'read' | 'write' | 'delete'): boolean {
  // Admin can do anything
  if (user.role === 'admin') {
    return true;
  }

  // Owner can do anything with their document
  if (document.ownerId === user.id) {
    return true;
  }

  // Check explicit permissions
  const permission = document.permissions.find(p => p.userId === user.id);
  if (!permission) {
    return false;
  }

  // Check if permission level is sufficient
  switch (action) {
    case 'read':
      return ['read', 'write', 'admin'].includes(permission.level);
    case 'write':
      return ['write', 'admin'].includes(permission.level);
    case 'delete':
      return permission.level === 'admin';
    default:
      return false;
  }
}

// Usage is straightforward
if (canUserAccessDocument(user, document, 'read')) {
  // Allow access
}

// If requirements grow, evolve the solution incrementally
// Add time-based access when actually needed
function canUserAccessDocument(
  user: User,
  document: Document,
  action: 'read' | 'write' | 'delete'
): boolean {
  // Admin can do anything
  if (user.role === 'admin') {
    return true;
  }

  // Owner can do anything with their document
  if (document.ownerId === user.id) {
    return true;
  }

  // Check explicit permissions
  const permission = document.permissions.find(p => p.userId === user.id);
  if (!permission) {
    return false;
  }

  // Check expiration (added when actually needed)
  if (permission.expiresAt && permission.expiresAt < new Date()) {
    return false;
  }

  // Check if permission level is sufficient
  const requiredLevel = getRequiredLevel(action);
  return hasRequiredLevel(permission.level, requiredLevel);
}

function getRequiredLevel(action: 'read' | 'write' | 'delete'): PermissionLevel {
  const levels: Record<string, PermissionLevel> = {
    read: 'read',
    write: 'write',
    delete: 'admin'
  };
  return levels[action];
}

function hasRequiredLevel(userLevel: PermissionLevel, required: PermissionLevel): boolean {
  const hierarchy: PermissionLevel[] = ['read', 'write', 'admin'];
  return hierarchy.indexOf(userLevel) >= hierarchy.indexOf(required);
}

// Still simple, still readable, handles new requirement

// For multiple resources, create focused helper
class DocumentAccessChecker {
  canRead(user: User, document: Document): boolean {
    return canUserAccessDocument(user, document, 'read');
  }

  canWrite(user: User, document: Document): boolean {
    return canUserAccessDocument(user, document, 'write');
  }

  canDelete(user: User, document: Document): boolean {
    return canUserAccessDocument(user, document, 'delete');
  }

  // Filter a list of documents to only accessible ones
  filterReadable(user: User, documents: Document[]): Document[] {
    return documents.filter(doc => this.canRead(user, doc));
  }
}

// Usage remains simple
const checker = new DocumentAccessChecker();
if (checker.canRead(user, document)) {
  // Show document
}
const accessibleDocs = checker.filterReadable(user, allDocuments);
```

## Why

1. **Readability**: Simple code can be understood in seconds. Complex abstractions require studying.

2. **Maintainability**: New developers can work with simple code immediately. Complex frameworks need training.

3. **Debugging**: When something breaks, simple code has obvious failure points.

4. **Performance**: Simple code is often faster - fewer layers, fewer allocations, less indirection.

5. **Time to Market**: Simple solutions are built and shipped faster.

6. **YAGNI Alignment**: The complex solution solves problems you don't have (and may never have).

7. **Incremental Complexity**: Start simple, add complexity only when real requirements demand it. The simple solution can evolve.
