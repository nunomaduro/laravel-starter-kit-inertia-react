---
title: Suspense Mode Integration
impact: MEDIUM
section: Advanced Queries
tags: react-query, suspense, react-18
---

# Suspense Mode

**Impact: MEDIUM**


Suspense mode integrates React Query with React Suspense, enabling declarative loading states and cleaner component code without manual loading checks.

## Bad Example

```tsx
// Anti-pattern: Using suspense with regular useQuery
const { data } = useQuery({
  queryKey: ['user'],
  queryFn: fetchUser,
  suspense: true, // Deprecated in v5
});

// Anti-pattern: No Suspense boundary
function App() {
  // useSuspenseQuery without boundary will crash
  return <UserProfile />; // Missing Suspense wrapper
}

// Anti-pattern: Error without ErrorBoundary
function UserProfile() {
  const { data } = useSuspenseQuery({
    queryKey: ['user'],
    queryFn: fetchUser,
  });
  // Error will propagate without handling
  return <div>{data.name}</div>;
}

// Anti-pattern: Conditional hooks with suspense
function ConditionalData({ enabled }: { enabled: boolean }) {
  // Can't conditionally use suspense hooks
  if (!enabled) return null;

  const { data } = useSuspenseQuery({
    queryKey: ['data'],
    queryFn: fetchData,
  });

  return <div>{data}</div>;
}
```

## Good Example

```tsx
// Proper suspense setup with boundaries
function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <ErrorBoundary fallback={<ErrorFallback />}>
        <Suspense fallback={<LoadingSkeleton />}>
          <Dashboard />
        </Suspense>
      </ErrorBoundary>
    </QueryClientProvider>
  );
}

// Clean component with useSuspenseQuery
function Dashboard() {
  // No loading checks needed - Suspense handles it
  const { data: user } = useSuspenseQuery({
    queryKey: ['user'],
    queryFn: fetchUser,
  });

  const { data: stats } = useSuspenseQuery({
    queryKey: ['stats'],
    queryFn: fetchStats,
  });

  // Both queries run in parallel and suspend together
  return (
    <div>
      <h1>Welcome, {user.name}</h1>
      <StatsDisplay stats={stats} />
    </div>
  );
}

// Multiple suspense boundaries for progressive loading
function PageLayout() {
  return (
    <div>
      {/* Header loads first */}
      <Suspense fallback={<HeaderSkeleton />}>
        <Header />
      </Suspense>

      <main>
        {/* Main content */}
        <Suspense fallback={<MainContentSkeleton />}>
          <MainContent />
        </Suspense>

        {/* Sidebar can load independently */}
        <Suspense fallback={<SidebarSkeleton />}>
          <Sidebar />
        </Suspense>
      </main>
    </div>
  );
}

// useSuspenseQueries for multiple parallel queries
function ProductComparison({ productIds }: { productIds: string[] }) {
  const results = useSuspenseQueries({
    queries: productIds.map((id) => ({
      queryKey: ['product', id],
      queryFn: () => fetchProduct(id),
    })),
  });

  // All products loaded - no loading state needed
  const products = results.map((r) => r.data);

  return <ComparisonTable products={products} />;
}

// useSuspenseInfiniteQuery for infinite lists
function InfinitePostList() {
  const { data, fetchNextPage, hasNextPage, isFetchingNextPage } =
    useSuspenseInfiniteQuery({
      queryKey: ['posts'],
      queryFn: ({ pageParam }) => fetchPosts(pageParam),
      initialPageParam: 0,
      getNextPageParam: (lastPage) => lastPage.nextCursor,
    });

  const allPosts = data.pages.flatMap((page) => page.posts);

  return (
    <div>
      {allPosts.map((post) => (
        <PostCard key={post.id} post={post} />
      ))}

      {hasNextPage && (
        <button
          onClick={() => fetchNextPage()}
          disabled={isFetchingNextPage}
        >
          {isFetchingNextPage ? 'Loading...' : 'Load More'}
        </button>
      )}
    </div>
  );
}

// Error handling with ErrorBoundary
import { ErrorBoundary } from 'react-error-boundary';

function UserSection() {
  return (
    <ErrorBoundary
      fallbackRender={({ error, resetErrorBoundary }) => (
        <div className="error">
          <p>Failed to load user: {error.message}</p>
          <button onClick={resetErrorBoundary}>Retry</button>
        </div>
      )}
      onReset={() => {
        queryClient.invalidateQueries({ queryKey: ['user'] });
      }}
    >
      <Suspense fallback={<UserSkeleton />}>
        <UserProfile />
      </Suspense>
    </ErrorBoundary>
  );
}

// Conditional rendering with suspense (correct approach)
function ConditionalContent({ userId }: { userId: string | null }) {
  if (!userId) {
    return <SelectUserPrompt />;
  }

  return (
    <Suspense fallback={<UserSkeleton />}>
      <UserDetails userId={userId} />
    </Suspense>
  );
}

// UserDetails always renders with a valid userId
function UserDetails({ userId }: { userId: string }) {
  const { data } = useSuspenseQuery({
    queryKey: ['user', userId],
    queryFn: () => fetchUser(userId),
  });

  return <UserCard user={data} />;
}

// Prefetching with suspense for instant navigation
function UserLink({ userId }: { userId: string }) {
  const queryClient = useQueryClient();

  const handleMouseEnter = () => {
    queryClient.prefetchQuery({
      queryKey: ['user', userId],
      queryFn: () => fetchUser(userId),
    });
  };

  return (
    <Link
      to={`/users/${userId}`}
      onMouseEnter={handleMouseEnter}
    >
      View User
    </Link>
  );
}

// Destination page with suspense
function UserPage({ userId }: { userId: string }) {
  // If prefetched, renders instantly; otherwise suspends
  const { data } = useSuspenseQuery({
    queryKey: ['user', userId],
    queryFn: () => fetchUser(userId),
  });

  return <UserProfile user={data} />;
}
```

## Why

1. **Cleaner Code**: No manual loading state checks; components assume data exists.

2. **Declarative Loading**: Suspense boundaries define loading UI at the component tree level.

3. **Parallel Fetching**: Multiple suspense queries in one component run in parallel.

4. **Progressive Loading**: Multiple boundaries allow different parts to load independently.

5. **Consistent Patterns**: Suspense + ErrorBoundary is the standard React pattern for async.

6. **Type Safety**: `useSuspenseQuery` returns non-undefined data, eliminating null checks.

Key differences from regular hooks:
| Aspect | useQuery | useSuspenseQuery |
|--------|----------|------------------|
| Returns data | `T \| undefined` | `T` (guaranteed) |
| Loading state | Manual check | Suspense boundary |
| Error handling | isError + error | ErrorBoundary |
| Enabled option | Supported | Not supported |
| Conditional | Can be disabled | Must always run |

Suspense hooks available:
- `useSuspenseQuery`
- `useSuspenseQueries`
- `useSuspenseInfiniteQuery`

Best practices:
- Always wrap with Suspense AND ErrorBoundary
- Use multiple boundaries for progressive loading
- Prefetch data for instant suspense resolution
- Keep suspense components simple and focused
