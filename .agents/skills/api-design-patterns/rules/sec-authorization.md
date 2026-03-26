---
title: Implement Proper Authorization
impact: CRITICAL
impactDescription: "Enforces access control and resource permissions"
tags: security, authorization, rbac, permissions, access-control
---

## Implement Proper Authorization

**Impact: CRITICAL (Enforces access control and resource permissions)**

Authorization verifies what authenticated users can do. Implement role-based (RBAC) or attribute-based (ABAC) access control consistently.

## Incorrect

```javascript
// ❌ No authorization checks
app.delete('/users/:id', authenticate, async (req, res) => {
  // Anyone authenticated can delete any user!
  await db.deleteUser(req.params.id);
  res.status(204).send();
});

// ❌ Client-side only authorization
app.get('/admin/users', authenticate, async (req, res) => {
  // Relies on frontend hiding the button
  const users = await db.getAllUsers();
  res.json(users);
});

// ❌ Inconsistent checks
app.get('/documents/:id', async (req, res) => {
  const doc = await db.findDocument(req.params.id);
  // Sometimes checks, sometimes doesn't
  if (doc.isPublic) {
    res.json(doc);
  }
  // Private docs accessible without check!
  res.json(doc);
});
```

**Problems:**
- Any authenticated user can perform any operation on any resource
- Client-side authorization can be bypassed by calling the API directly
- Inconsistent checks leave gaps attackers can exploit
- No separation between authentication (who) and authorization (what)
- No audit trail of permission-based access decisions

## Correct

```javascript
// ✅ Role-Based Access Control (RBAC)
const ROLES = {
  ADMIN: 'admin',
  MANAGER: 'manager',
  USER: 'user'
};

const PERMISSIONS = {
  // Resource: action -> roles that can perform it
  users: {
    read: [ROLES.ADMIN, ROLES.MANAGER, ROLES.USER],
    create: [ROLES.ADMIN, ROLES.MANAGER],
    update: [ROLES.ADMIN, ROLES.MANAGER],
    delete: [ROLES.ADMIN]
  },
  reports: {
    read: [ROLES.ADMIN, ROLES.MANAGER],
    create: [ROLES.ADMIN, ROLES.MANAGER],
    delete: [ROLES.ADMIN]
  },
  settings: {
    read: [ROLES.ADMIN],
    update: [ROLES.ADMIN]
  }
};

// Authorization middleware
function authorize(resource, action) {
  return (req, res, next) => {
    const userRoles = req.user.roles || [];
    const allowedRoles = PERMISSIONS[resource]?.[action] || [];

    const hasPermission = userRoles.some(role =>
      allowedRoles.includes(role)
    );

    if (!hasPermission) {
      return res.status(403).json({
        error: {
          code: 'forbidden',
          message: `You don't have permission to ${action} ${resource}`,
          requiredRoles: allowedRoles,
          yourRoles: userRoles
        }
      });
    }

    next();
  };
}

// Resource ownership check
async function authorizeOwnership(req, res, next) {
  const resourceId = req.params.id;
  const userId = req.user.sub;

  const resource = await db.findResource(resourceId);

  if (!resource) {
    return res.status(404).json({
      error: { code: 'not_found', message: 'Resource not found' }
    });
  }

  // Admin can access anything
  if (req.user.roles.includes(ROLES.ADMIN)) {
    req.resource = resource;
    return next();
  }

  // Owner can access their own resources
  if (resource.ownerId !== userId) {
    return res.status(403).json({
      error: {
        code: 'forbidden',
        message: 'You can only access your own resources'
      }
    });
  }

  req.resource = resource;
  next();
}

// Usage
app.get('/users',
  authenticate,
  authorize('users', 'read'),
  async (req, res) => {
    const users = await db.getUsers();
    res.json(users);
  }
);

app.delete('/users/:id',
  authenticate,
  authorize('users', 'delete'),
  async (req, res) => {
    await db.deleteUser(req.params.id);
    res.status(204).send();
  }
);

app.get('/documents/:id',
  authenticate,
  authorizeOwnership,
  async (req, res) => {
    res.json(req.resource);
  }
);

app.put('/documents/:id',
  authenticate,
  authorizeOwnership,
  async (req, res) => {
    const updated = await db.updateDocument(req.params.id, req.body);
    res.json(updated);
  }
);
```

```python
# ✅ FastAPI with RBAC
from fastapi import FastAPI, Depends, HTTPException, status
from enum import Enum
from typing import List
from functools import wraps

app = FastAPI()

class Role(str, Enum):
    ADMIN = "admin"
    MANAGER = "manager"
    USER = "user"

class Permission(str, Enum):
    READ_USERS = "read:users"
    WRITE_USERS = "write:users"
    DELETE_USERS = "delete:users"
    READ_REPORTS = "read:reports"
    ADMIN_SETTINGS = "admin:settings"

ROLE_PERMISSIONS = {
    Role.ADMIN: [
        Permission.READ_USERS,
        Permission.WRITE_USERS,
        Permission.DELETE_USERS,
        Permission.READ_REPORTS,
        Permission.ADMIN_SETTINGS
    ],
    Role.MANAGER: [
        Permission.READ_USERS,
        Permission.WRITE_USERS,
        Permission.READ_REPORTS
    ],
    Role.USER: [
        Permission.READ_USERS
    ]
}

def get_user_permissions(user) -> List[Permission]:
    permissions = set()
    for role in user.roles:
        permissions.update(ROLE_PERMISSIONS.get(role, []))
    return list(permissions)

def require_permission(permission: Permission):
    def decorator(func):
        @wraps(func)
        async def wrapper(*args, current_user = Depends(get_current_user), **kwargs):
            user_permissions = get_user_permissions(current_user)

            if permission not in user_permissions:
                raise HTTPException(
                    status_code=status.HTTP_403_FORBIDDEN,
                    detail={
                        "code": "forbidden",
                        "message": f"Permission '{permission.value}' required",
                        "your_permissions": [p.value for p in user_permissions]
                    }
                )
            return await func(*args, current_user=current_user, **kwargs)
        return wrapper
    return decorator

def require_ownership_or_admin(resource_type: str):
    async def check_ownership(
        resource_id: int,
        current_user = Depends(get_current_user)
    ):
        resource = await db.get_resource(resource_type, resource_id)

        if not resource:
            raise HTTPException(status_code=404, detail="Not found")

        if Role.ADMIN in current_user.roles:
            return resource

        if resource.owner_id != current_user.id:
            raise HTTPException(
                status_code=403,
                detail={
                    "code": "forbidden",
                    "message": "You can only access your own resources"
                }
            )
        return resource
    return check_ownership

@app.get("/users")
@require_permission(Permission.READ_USERS)
async def list_users(current_user = Depends(get_current_user)):
    return await db.get_users()

@app.delete("/users/{user_id}")
@require_permission(Permission.DELETE_USERS)
async def delete_user(user_id: int, current_user = Depends(get_current_user)):
    await db.delete_user(user_id)
    return {"deleted": True}

@app.get("/documents/{document_id}")
async def get_document(
    document = Depends(require_ownership_or_admin("documents"))
):
    return document
```

```json
// ✅ Authorization error response
{
  "error": {
    "code": "forbidden",
    "message": "You don't have permission to delete users",
    "details": {
      "requiredPermission": "delete:users",
      "yourRoles": ["user", "manager"],
      "requiredRoles": ["admin"]
    }
  }
}
```

## Authorization Patterns

| Pattern | Use Case | Example |
|---------|----------|---------|
| RBAC | Role-based access | Admin, Manager, User roles |
| ABAC | Attribute-based | Department, location, time-based |
| Ownership | Resource owners | User owns their documents |
| Hierarchical | Org structure | Managers see team's data |
| Feature flags | Feature access | Premium features |

**Benefits:**
- Prevents unauthorized access to sensitive data and operations
- Principle of least privilege: users only get access they need
- Centralized permission definitions are easy to audit and update
- Server-side checks cannot be bypassed like client-side authorization
- RBAC/ABAC scales better than per-user permissions
- Clear authorization rules support compliance requirements

Reference: [OWASP Authorization Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Authorization_Cheat_Sheet.html)
