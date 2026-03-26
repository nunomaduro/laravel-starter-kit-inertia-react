---
section: page-components
priority: critical
description: Type-safe page props with TypeScript interfaces extending base PageProps
keywords: [typescript, props, typing, interfaces, type-safety]
---

# Page Props Typing

All Inertia page props should be strongly typed using TypeScript interfaces that extend a base PageProps type containing shared data.

## Bad Example

```tsx
// Anti-pattern: Using any or missing types
export default function Users({ users, filters }: any) {
  return (
    <div>
      {users.map((user) => (
        <div key={user.id}>{user.name}</div>
      ))}
    </div>
  );
}

// Anti-pattern: Inline typing without extending base props
export default function Users({
  users
}: {
  users: { id: number; name: string }[]
}) {
  // Missing auth, flash, and other shared props
}
```

## Good Example

```tsx
// resources/js/types/index.d.ts
export interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at: string | null;
  created_at: string;
  updated_at: string;
}

export interface PaginatedData<T> {
  data: T[];
  links: {
    first: string;
    last: string;
    prev: string | null;
    next: string | null;
  };
  meta: {
    current_page: number;
    from: number;
    last_page: number;
    path: string;
    per_page: number;
    to: number;
    total: number;
  };
}

export interface PageProps {
  auth: {
    user: User;
  };
  flash: {
    success?: string;
    error?: string;
  };
  ziggy: {
    url: string;
    port: number | null;
    defaults: Record<string, unknown>;
    routes: Record<string, unknown>;
  };
}

// resources/js/Pages/Users/Index.tsx
import { PageProps, PaginatedData, User } from '@/types';

interface Filters {
  search: string;
  role: string;
  status: 'active' | 'inactive' | 'all';
}

interface UsersIndexProps extends PageProps {
  users: PaginatedData<User>;
  filters: Filters;
  roles: { value: string; label: string }[];
}

export default function Index({ auth, users, filters, roles }: UsersIndexProps) {
  return (
    <div>
      <h1>Users ({users.meta.total})</h1>
      {users.data.map((user) => (
        <UserCard key={user.id} user={user} />
      ))}
    </div>
  );
}
```

## Why

Proper props typing is essential for Inertia applications:

1. **Contract Enforcement**: Types create a contract between Laravel controllers and React components
2. **IDE Support**: Autocomplete and inline documentation improve developer productivity
3. **Error Prevention**: Catch typos and missing properties before runtime
4. **Refactoring Safety**: TypeScript will flag all affected components when data shapes change
5. **Documentation**: Types serve as living documentation for the data flow
6. **Shared Data Access**: Extending PageProps ensures access to auth, flash messages, and Ziggy routes
