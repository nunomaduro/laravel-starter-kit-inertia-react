---
title: Prefetch Code Chunks on User Intent
impact: CRITICAL
impactDescription: "Instant navigation perceived speed"
tags: split, prefetch, preload, performance, code-splitting
---

## Prefetch Code Chunks on User Intent

**Impact: CRITICAL (Instant navigation perceived speed)**

Use prefetch and preload hints to load code chunks before they are needed, improving perceived navigation speed.

## Incorrect

```tsx
// ❌ Bad: No prefetching - chunks load only when navigation occurs
import { lazy, Suspense } from 'react';
import { Routes, Route, Link } from 'react-router-dom';

const Dashboard = lazy(() => import('./pages/Dashboard'));
const Analytics = lazy(() => import('./pages/Analytics'));
const Settings = lazy(() => import('./pages/Settings'));

function App() {
  return (
    <>
      <nav>
        <Link to="/">Dashboard</Link>
        <Link to="/analytics">Analytics</Link>
        <Link to="/settings">Settings</Link>
      </nav>

      <Suspense fallback={<Loading />}>
        <Routes>
          <Route path="/" element={<Dashboard />} />
          <Route path="/analytics" element={<Analytics />} />
          <Route path="/settings" element={<Settings />} />
        </Routes>
      </Suspense>
    </>
  );
}
// User clicks link -> waits for chunk download -> sees loading -> page renders
```

**Problems:**
- Users see loading spinners on every navigation
- Chunks only start downloading after the user clicks
- No anticipation of user intent leads to perceived slowness
- Wasted idle time that could be used for preloading

## Correct

```tsx
// ✅ Good: Prefetch on hover/focus for instant-feeling navigation
import { lazy, Suspense, useCallback } from 'react';
import { Routes, Route, Link, LinkProps } from 'react-router-dom';

function lazyWithPreload<T extends React.ComponentType<any>>(
  factory: () => Promise<{ default: T }>
) {
  const Component = lazy(factory);
  (Component as any).preload = factory;
  return Component as typeof Component & { preload: typeof factory };
}

const Dashboard = lazyWithPreload(() => import('./pages/Dashboard'));
const Analytics = lazyWithPreload(() => import('./pages/Analytics'));
const Settings = lazyWithPreload(() => import('./pages/Settings'));

interface PrefetchLinkProps extends LinkProps {
  preload?: () => Promise<any>;
}

function PrefetchLink({ preload, onMouseEnter, onFocus, ...props }: PrefetchLinkProps) {
  const handlePreload = useCallback(() => {
    preload?.();
  }, [preload]);

  return (
    <Link
      {...props}
      onMouseEnter={(e) => {
        handlePreload();
        onMouseEnter?.(e);
      }}
      onFocus={(e) => {
        handlePreload();
        onFocus?.(e);
      }}
    />
  );
}

function App() {
  return (
    <>
      <nav>
        <PrefetchLink to="/" preload={Dashboard.preload}>Dashboard</PrefetchLink>
        <PrefetchLink to="/analytics" preload={Analytics.preload}>Analytics</PrefetchLink>
        <PrefetchLink to="/settings" preload={Settings.preload}>Settings</PrefetchLink>
      </nav>

      <Suspense fallback={<Loading />}>
        <Routes>
          <Route path="/" element={<Dashboard />} />
          <Route path="/analytics" element={<Analytics />} />
          <Route path="/settings" element={<Settings />} />
        </Routes>
      </Suspense>
    </>
  );
}
// User hovers link -> chunk downloads -> user clicks -> instant navigation
```

```tsx
// ✅ Good: Prefetch based on viewport visibility
import { useEffect, useRef } from 'react';

interface PrefetchOnVisibleProps {
  children: React.ReactNode;
  preload: () => Promise<any>;
  rootMargin?: string;
}

export function PrefetchOnVisible({
  children,
  preload,
  rootMargin = '200px',
}: PrefetchOnVisibleProps) {
  const ref = useRef<HTMLDivElement>(null);
  const prefetched = useRef(false);

  useEffect(() => {
    if (!ref.current || prefetched.current) return;

    const observer = new IntersectionObserver(
      (entries) => {
        if (entries[0].isIntersecting && !prefetched.current) {
          prefetched.current = true;
          preload();
          observer.disconnect();
        }
      },
      { rootMargin }
    );

    observer.observe(ref.current);
    return () => observer.disconnect();
  }, [preload, rootMargin]);

  return <div ref={ref}>{children}</div>;
}
```

```tsx
// ✅ Good: Prefetch after idle time
import { useEffect, useRef } from 'react';

export function usePrefetchAfterIdle(
  preloadFns: Array<() => Promise<any>>,
  delay: number = 2000
) {
  const prefetched = useRef(false);

  useEffect(() => {
    if (prefetched.current) return;

    const prefetch = () => {
      if (prefetched.current) return;
      prefetched.current = true;

      preloadFns.forEach((fn) => {
        if ('requestIdleCallback' in window) {
          requestIdleCallback(() => fn(), { timeout: 5000 });
        } else {
          setTimeout(fn, 100);
        }
      });
    };

    const timeoutId = setTimeout(prefetch, delay);
    return () => clearTimeout(timeoutId);
  }, [preloadFns, delay]);
}

// Usage
function App() {
  usePrefetchAfterIdle([Analytics.preload, Settings.preload], 2000);
  return (/* ... */);
}
```

**Benefits:**
- Code loads while users decide, making clicks feel instantaneous
- Eliminates loading spinners for common navigation paths
- Prefetching during idle time does not compete with critical resources
- Maintains code splitting benefits with smarter preloading
- Users on slow connections benefit the most from preloading

| Strategy | Trigger | Best For |
|----------|---------|----------|
| Hover/Focus | User intent signal | Navigation links |
| Viewport Entry | Scroll position | Below-fold sections |
| Idle Time | After initial load | Common routes |
| `modulepreload` | Page load | Critical vendors |

Reference: [Vite modulePreload](https://vitejs.dev/config/build-options.html#build-modulepreload) | [React lazy](https://react.dev/reference/react/lazy)
