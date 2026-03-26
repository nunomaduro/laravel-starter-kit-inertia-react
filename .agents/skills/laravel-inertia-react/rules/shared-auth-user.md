---
section: shared-data
priority: critical
description: Access authenticated user data through Inertia's shared props
keywords: [auth, user, authentication, shared, middleware, HandleInertiaRequests]
---

# Shared Auth User Data

Access authenticated user data through Inertia's shared props, set up via Laravel middleware, for consistent auth state across all pages.

## Bad Example

```tsx
// Anti-pattern: Fetching user data separately
import { useEffect, useState } from 'react';

export default function Dashboard() {
  const [user, setUser] = useState(null);

  useEffect(() => {
    fetch('/api/user')
      .then(res => res.json())
      .then(setUser);
  }, []);

  if (!user) return <div>Loading...</div>;

  return <div>Welcome {user.name}</div>;
}

// Anti-pattern: Passing user through every component
export default function Dashboard({ user }) {
  return (
    <Layout user={user}>
      <Sidebar user={user}>
        <Content user={user} />
      </Sidebar>
    </Layout>
  );
}
```

## Good Example

```tsx
// app/Http/Middleware/HandleInertiaRequests.php (Laravel)
/*
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'avatar_url' => $request->user()->avatar_url,
                    'email_verified_at' => $request->user()->email_verified_at,
                    'roles' => $request->user()->roles->pluck('name'),
                    'permissions' => $request->user()->getAllPermissions()->pluck('name'),
                ] : null,
            ],
        ]);
    }
}
*/

// resources/js/types/index.d.ts
export interface User {
  id: number;
  name: string;
  email: string;
  avatar_url: string | null;
  email_verified_at: string | null;
  roles: string[];
  permissions: string[];
}

export interface PageProps {
  auth: {
    user: User | null;
  };
  flash: {
    success?: string;
    error?: string;
  };
}

// resources/js/hooks/useAuth.ts
import { usePage } from '@inertiajs/react';
import { PageProps } from '@/types';

export function useAuth() {
  const { auth } = usePage<PageProps>().props;

  return {
    user: auth.user,
    isAuthenticated: !!auth.user,
    isGuest: !auth.user,
  };
}

export function usePermissions() {
  const { user } = useAuth();

  return {
    hasRole: (role: string) => user?.roles.includes(role) ?? false,
    hasPermission: (permission: string) => user?.permissions.includes(permission) ?? false,
    hasAnyRole: (roles: string[]) => roles.some(role => user?.roles.includes(role)) ?? false,
    hasAllRoles: (roles: string[]) => roles.every(role => user?.roles.includes(role)) ?? false,
  };
}

// resources/js/Components/UserMenu.tsx
import { Link } from '@inertiajs/react';
import { useAuth } from '@/hooks/useAuth';

export default function UserMenu() {
  const { user, isAuthenticated } = useAuth();

  if (!isAuthenticated) {
    return (
      <div className="flex gap-4">
        <Link href={route('login')}>Log In</Link>
        <Link href={route('register')}>Sign Up</Link>
      </div>
    );
  }

  return (
    <div className="flex items-center gap-4">
      {user?.avatar_url ? (
        <img
          src={user.avatar_url}
          alt={user.name}
          className="h-8 w-8 rounded-full"
        />
      ) : (
        <div className="flex h-8 w-8 items-center justify-center rounded-full bg-gray-200">
          {user?.name.charAt(0).toUpperCase()}
        </div>
      )}
      <span>{user?.name}</span>
      <Link href={route('profile.edit')}>Settings</Link>
      <Link href={route('logout')} method="post" as="button">
        Log Out
      </Link>
    </div>
  );
}

// resources/js/Components/Can.tsx - Permission-based rendering
import { usePermissions } from '@/hooks/useAuth';
import { ReactNode } from 'react';

interface CanProps {
  permission?: string;
  role?: string;
  children: ReactNode;
  fallback?: ReactNode;
}

export default function Can({ permission, role, children, fallback = null }: CanProps) {
  const { hasPermission, hasRole } = usePermissions();

  const authorized = permission
    ? hasPermission(permission)
    : role
    ? hasRole(role)
    : false;

  return authorized ? <>{children}</> : <>{fallback}</>;
}

// Usage in pages
import { useAuth, usePermissions } from '@/hooks/useAuth';
import Can from '@/Components/Can';

export default function Dashboard() {
  const { user } = useAuth();
  const { hasRole } = usePermissions();

  return (
    <div>
      <h1>Welcome, {user?.name}</h1>

      {/* Role-based content */}
      {hasRole('admin') && (
        <Link href={route('admin.dashboard')}>Admin Panel</Link>
      )}

      {/* Permission-based component */}
      <Can permission="manage-users">
        <Link href={route('users.index')}>Manage Users</Link>
      </Can>

      {/* With fallback */}
      <Can permission="create-posts" fallback={<p>You cannot create posts</p>}>
        <Link href={route('posts.create')}>Create Post</Link>
      </Can>
    </div>
  );
}
```

## Why

Sharing auth data through Inertia provides:

1. **Single Source of Truth**: User data is consistent across all components
2. **No Extra Requests**: Auth data comes with every Inertia response
3. **Type Safety**: TypeScript interfaces ensure correct user data shape
4. **Easy Access**: Custom hooks make auth data accessible anywhere
5. **Permission Handling**: Role and permission checks can be centralized
6. **SSR Compatible**: Works correctly with server-side rendering
7. **Automatic Updates**: User data refreshes on every page visit
