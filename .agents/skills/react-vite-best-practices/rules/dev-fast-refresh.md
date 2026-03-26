---
title: Structure Components for Fast Refresh
impact: HIGH
impactDescription: "Instant updates without losing state"
tags: dev, fast-refresh, hmr, react, development
---

## Structure Components for Fast Refresh

**Impact: HIGH (Instant updates without losing state)**

Structure components to take full advantage of React Fast Refresh for instant updates during development.

## Incorrect

```tsx
// ❌ Bad: Named exports can break Fast Refresh in some cases
export const App = () => {
  return <div>App</div>;
};

// Multiple component exports in one file
export const Header = () => <header>Header</header>;
export const Footer = () => <footer>Footer</footer>;
export const Sidebar = () => <aside>Sidebar</aside>;
```

```tsx
// ❌ Bad: Module-level side effects break Fast Refresh
import { fetchUser } from './api';

const initialUser = await fetchUser('current');

export default function UserProfile() {
  const [user] = useState(initialUser);
  return <div>{user.name}</div>;
}
```

```tsx
// ❌ Bad: Mixing components with non-component exports
export default function Counter() {
  const [count, setCount] = useState(0);
  return (
    <button onClick={() => setCount(c => c + 1)}>
      Count: {count}
    </button>
  );
}

export const MAX_COUNT = 100;
export const formatCount = (n: number) => n.toLocaleString();
```

```tsx
// ❌ Bad: Anonymous component - Fast Refresh can't identify it
export default () => {
  return <div>Anonymous</div>;
};
```

**Problems:**
- Multiple components per file may cause full page reloads instead of hot updates
- Module-level side effects re-execute on every edit, breaking state
- Non-component exports in component files trigger full module replacement
- Anonymous components cannot be tracked by Fast Refresh

## Correct

```tsx
// ✅ Good: Default export for main component, one per file
export default function App() {
  return (
    <div>
      <Header />
      <main>
        <Outlet />
      </main>
      <Footer />
    </div>
  );
}
```

```tsx
// ✅ Good: Separate file for constants
// constants/counter.ts
export const MAX_COUNT = 100;
export const MIN_COUNT = 0;

// utils/format.ts
export function formatCount(n: number): string {
  return n.toLocaleString();
}

// components/Counter.tsx - Pure component file
import { useState } from 'react';
import { MAX_COUNT, MIN_COUNT } from '../constants/counter';
import { formatCount } from '../utils/format';

export default function Counter() {
  const [count, setCount] = useState(0);

  return (
    <div className="counter">
      <button onClick={() => setCount(c => Math.max(c - 1, MIN_COUNT))}>-</button>
      <span>{formatCount(count)}</span>
      <button onClick={() => setCount(c => Math.min(c + 1, MAX_COUNT))}>+</button>
    </div>
  );
}
```

```tsx
// ✅ Good: Proper data fetching with hooks instead of module-level side effects
import { useQuery } from '@tanstack/react-query';
import { fetchUser } from '../api/users';

export default function UserProfile() {
  const { data: user, isLoading, error } = useQuery({
    queryKey: ['user', 'current'],
    queryFn: () => fetchUser('current'),
  });

  if (isLoading) return <Skeleton />;
  if (error) return <ErrorMessage error={error} />;

  return (
    <div className="user-profile">
      <Avatar src={user.avatar} alt={user.name} />
      <h1>{user.name}</h1>
      <p>{user.email}</p>
    </div>
  );
}
```

```tsx
// ✅ Good: Set displayName on HOCs for Fast Refresh and DevTools
export function withAuth<P extends object>(
  WrappedComponent: ComponentType<P>
) {
  function WithAuth(props: P) {
    const { user, isLoading } = useAuth();
    if (isLoading) return <LoadingSpinner />;
    if (!user) return <Navigate to="/login" />;
    return <WrappedComponent {...props} />;
  }

  WithAuth.displayName = `WithAuth(${
    WrappedComponent.displayName || WrappedComponent.name || 'Component'
  })`;

  return WithAuth;
}
```

**Benefits:**
- State preserved across edits — no losing form inputs or scroll position
- Changes reflect in ~50ms, enabling rapid UI iteration
- Error recovery restores previous state without full reload
- Only changed components re-render, keeping the rest of the app intact

| Pattern | Fast Refresh | Notes |
|---------|--------------|-------|
| Default export function | Works | Recommended |
| Named export function | Usually works | Name must be PascalCase |
| Anonymous function | Fails | Always name components |
| Multiple components/file | May break | One component per file |
| Non-component exports | May break | Separate into utility files |

Reference: [React Fast Refresh](https://react.dev/learn/editor-setup#your-editor) | [@vitejs/plugin-react](https://github.com/vitejs/vite-plugin-react/tree/main/packages/plugin-react)
