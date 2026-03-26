---
section: navigation
priority: critical
description: Use Link component for SPA-like navigation without full page reloads
keywords: [Link, navigation, spa, router, link-component]
---

# Navigation Link Component

Use Inertia's Link component for internal navigation to enable SPA-like page transitions without full page reloads.

## Bad Example

```tsx
// Anti-pattern: Using regular anchor tags
export default function Navigation() {
  return (
    <nav>
      <a href="/dashboard">Dashboard</a>
      <a href="/users">Users</a>
      <a href={`/users/${user.id}`}>Profile</a>
    </nav>
  );
}

// Anti-pattern: Using React Router's Link
import { Link } from 'react-router-dom';

export default function Navigation() {
  return (
    <nav>
      <Link to="/dashboard">Dashboard</Link>
    </nav>
  );
}

// Anti-pattern: Manual navigation on click
<button onClick={() => window.location.href = '/dashboard'}>
  Dashboard
</button>
```

## Good Example

```tsx
// resources/js/Components/NavLink.tsx
import { Link, InertiaLinkProps } from '@inertiajs/react';

interface NavLinkProps extends InertiaLinkProps {
  active?: boolean;
  children: React.ReactNode;
}

export default function NavLink({
  active = false,
  className = '',
  children,
  ...props
}: NavLinkProps) {
  return (
    <Link
      {...props}
      className={`inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none ${
        active
          ? 'border-indigo-400 text-gray-900 focus:border-indigo-700'
          : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 focus:border-gray-300 focus:text-gray-700'
      } ${className}`}
    >
      {children}
    </Link>
  );
}

// resources/js/Layouts/AuthenticatedLayout.tsx
import { Link, usePage } from '@inertiajs/react';
import NavLink from '@/Components/NavLink';

export default function AuthenticatedLayout({ children }) {
  const { url } = usePage();

  return (
    <div>
      <nav className="border-b bg-white">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div className="flex h-16 justify-between">
            <div className="flex">
              {/* Logo/Home link */}
              <Link href={route('home')} className="flex items-center">
                <ApplicationLogo className="h-9 w-auto" />
              </Link>

              {/* Navigation Links */}
              <div className="hidden space-x-8 sm:ml-10 sm:flex">
                <NavLink
                  href={route('dashboard')}
                  active={route().current('dashboard')}
                >
                  Dashboard
                </NavLink>

                <NavLink
                  href={route('projects.index')}
                  active={route().current('projects.*')}
                >
                  Projects
                </NavLink>

                <NavLink
                  href={route('users.index')}
                  active={route().current('users.*')}
                >
                  Users
                </NavLink>
              </div>
            </div>

            {/* User dropdown */}
            <div className="flex items-center">
              <Link
                href={route('profile.edit')}
                className="text-sm text-gray-700 hover:text-gray-900"
              >
                Profile
              </Link>

              <Link
                href={route('logout')}
                method="post"
                as="button"
                className="ml-4 text-sm text-gray-700 hover:text-gray-900"
              >
                Log Out
              </Link>
            </div>
          </div>
        </div>
      </nav>

      <main>{children}</main>
    </div>
  );
}

// Using Link with various options
function AdvancedLinkExamples() {
  return (
    <div className="space-y-4">
      {/* Basic link */}
      <Link href="/users">Users</Link>

      {/* Using Ziggy routes */}
      <Link href={route('users.show', { user: 1 })}>View User</Link>

      {/* Preserve scroll position */}
      <Link href="/users?page=2" preserveScroll>
        Next Page
      </Link>

      {/* Replace history instead of push */}
      <Link href="/users" replace>
        Users (replace)
      </Link>

      {/* Preserve component state */}
      <Link href="/users?filter=active" preserveState>
        Active Users
      </Link>

      {/* Only reload specific props */}
      <Link href="/dashboard" only={['notifications']}>
        Refresh Notifications
      </Link>

      {/* POST request as link */}
      <Link
        href={route('posts.favorite', { post: 1 })}
        method="post"
        as="button"
        className="text-blue-600 hover:underline"
      >
        Add to Favorites
      </Link>

      {/* DELETE with confirmation */}
      <Link
        href={route('posts.destroy', { post: 1 })}
        method="delete"
        as="button"
        onBefore={() => confirm('Are you sure?')}
        className="text-red-600 hover:underline"
      >
        Delete Post
      </Link>

      {/* With custom headers */}
      <Link
        href="/api/resource"
        headers={{ 'X-Custom-Header': 'value' }}
      >
        Custom Request
      </Link>
    </div>
  );
}
```

## Why

Using Inertia's Link component is essential for proper SPA behavior:

1. **No Full Reload**: Pages transition smoothly without browser refresh
2. **State Preservation**: React component state persists across navigation
3. **Prefetching**: Inertia can prefetch pages on hover for faster navigation
4. **Progress Indicator**: Integrates with Inertia's loading progress bar
5. **HTTP Methods**: Support for POST, PUT, PATCH, DELETE via method prop
6. **Ziggy Integration**: Works seamlessly with Laravel's named routes
7. **History Management**: Proper browser back/forward button behavior
8. **Accessibility**: Renders semantic anchor tags with proper href attributes
