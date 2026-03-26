---
section: shared-data
priority: critical
description: Use Ziggy for type-safe Laravel route generation in React
keywords: [ziggy, routes, routing, laravel, named-routes, type-safety]
---

# Shared Ziggy Routes

Use Ziggy to share Laravel named routes with your React frontend, enabling type-safe route generation with parameters.

## Bad Example

```tsx
// Anti-pattern: Hardcoding URLs
import { Link } from '@inertiajs/react';

export default function UserList({ users }) {
  return (
    <ul>
      {users.map(user => (
        <li key={user.id}>
          <Link href={`/users/${user.id}`}>{user.name}</Link>
          <Link href={`/users/${user.id}/edit`}>Edit</Link>
          <button onClick={() => deleteUser(`/users/${user.id}`)}>
            Delete
          </button>
        </li>
      ))}
    </ul>
  );
}

// Anti-pattern: String concatenation for complex routes
const searchUrl = `/users?search=${search}&role=${role}&page=${page}`;
```

## Good Example

```tsx
// Install Ziggy: composer require tightenco/ziggy
// Add to your blade template:
// @routes

// resources/js/types/ziggy.d.ts
import { Config, RouteParamsWithQueryOverload, Router } from 'ziggy-js';

declare module 'ziggy-js' {
  export function route<T extends keyof ZiggyRoutes>(
    name: T,
    params?: RouteParamsWithQueryOverload,
    absolute?: boolean,
    config?: Config
  ): string;

  export function route(): Router;
}

declare global {
  function route<T extends keyof ZiggyRoutes>(
    name: T,
    params?: RouteParamsWithQueryOverload,
    absolute?: boolean
  ): string;

  function route(): Router;
}

// Generate types for your routes
// resources/js/types/ziggy-routes.d.ts (auto-generated with: php artisan ziggy:generate)
interface ZiggyRoutes {
  'home': [];
  'dashboard': [];
  'users.index': [];
  'users.create': [];
  'users.store': [];
  'users.show': [{ user: number | string }];
  'users.edit': [{ user: number | string }];
  'users.update': [{ user: number | string }];
  'users.destroy': [{ user: number | string }];
  'posts.index': [];
  'posts.show': [{ post: number | string }];
  'profile.edit': [];
  'profile.update': [];
}

// resources/js/Pages/Users/Index.tsx
import { Link, router } from '@inertiajs/react';

interface User {
  id: number;
  name: string;
  email: string;
}

interface UsersIndexProps {
  users: PaginatedData<User>;
  filters: {
    search: string;
    role: string;
  };
}

export default function Index({ users, filters }: UsersIndexProps) {
  const handleDelete = (user: User) => {
    if (confirm(`Delete ${user.name}?`)) {
      router.delete(route('users.destroy', { user: user.id }));
    }
  };

  const handleSearch = (search: string) => {
    router.get(
      route('users.index'),
      { ...filters, search },
      { preserveState: true }
    );
  };

  return (
    <div>
      <div className="mb-4 flex justify-between">
        <input
          type="search"
          defaultValue={filters.search}
          onChange={(e) => handleSearch(e.target.value)}
          placeholder="Search users..."
        />

        {/* Link with route helper */}
        <Link
          href={route('users.create')}
          className="rounded bg-blue-600 px-4 py-2 text-white"
        >
          Add User
        </Link>
      </div>

      <table className="w-full">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          {users.data.map((user) => (
            <tr key={user.id}>
              <td>
                {/* Route with parameter */}
                <Link href={route('users.show', { user: user.id })}>
                  {user.name}
                </Link>
              </td>
              <td>{user.email}</td>
              <td className="flex gap-2">
                <Link
                  href={route('users.edit', { user: user.id })}
                  className="text-blue-600"
                >
                  Edit
                </Link>
                <button
                  onClick={() => handleDelete(user)}
                  className="text-red-600"
                >
                  Delete
                </button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

// Using route() helper for various scenarios
function RouteExamples() {
  // Simple route
  const homeUrl = route('home');

  // Route with parameters
  const userUrl = route('users.show', { user: 1 });

  // Route with query parameters
  const searchUrl = route('users.index', {
    _query: {
      search: 'john',
      role: 'admin',
      page: 2,
    },
  });

  // Check current route
  const isOnDashboard = route().current('dashboard');
  const isOnUserPages = route().current('users.*');
  const isOnUserEdit = route().current('users.edit', { user: 1 });

  // Get current route name
  const currentRoute = route().current();

  // Check if route exists
  const hasRoute = route().has('users.index');

  return (
    <nav>
      <Link
        href={route('dashboard')}
        className={route().current('dashboard') ? 'font-bold' : ''}
      >
        Dashboard
      </Link>
      <Link
        href={route('users.index')}
        className={route().current('users.*') ? 'font-bold' : ''}
      >
        Users
      </Link>
    </nav>
  );
}

// Reusable navigation component with active state
interface NavLinkProps {
  routeName: string;
  params?: Record<string, unknown>;
  children: React.ReactNode;
  activePattern?: string;
}

function NavLink({ routeName, params, children, activePattern }: NavLinkProps) {
  const isActive = route().current(activePattern || routeName);

  return (
    <Link
      href={route(routeName, params)}
      className={`px-4 py-2 ${isActive ? 'bg-blue-100 text-blue-800' : 'text-gray-600'}`}
    >
      {children}
    </Link>
  );
}

// Usage
<NavLink routeName="users.index" activePattern="users.*">
  Users
</NavLink>
```

## Why

Using Ziggy for route generation provides:

1. **Single Source of Truth**: Routes defined once in Laravel, used everywhere
2. **Refactoring Safety**: Route name changes are caught at compile time
3. **Parameter Validation**: TypeScript ensures correct route parameters
4. **No Hardcoding**: URLs don't break when route patterns change
5. **Active Route Detection**: Easy highlighting of current navigation items
6. **Query Parameters**: Clean syntax for adding query strings
7. **IDE Support**: Autocomplete for route names and parameters
