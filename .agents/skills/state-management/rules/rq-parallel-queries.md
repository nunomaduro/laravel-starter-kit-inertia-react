---
title: Parallel Queries Pattern
impact: HIGH
section: Advanced Queries
tags: react-query, parallel-queries, performance
---

# Parallel Queries

**Impact: HIGH**


Parallel queries fetch multiple independent data sources simultaneously, maximizing performance by eliminating waterfall requests.

## Bad Example

```tsx
// Anti-pattern: Sequential fetching with await
async function fetchDashboardData() {
  const user = await fetchUser(); // Wait
  const posts = await fetchPosts(); // Then wait
  const notifications = await fetchNotifications(); // Then wait
  return { user, posts, notifications };
}

// Anti-pattern: Single query for unrelated data
const { data } = useQuery({
  queryKey: ['dashboard'],
  queryFn: async () => ({
    user: await fetchUser(),
    posts: await fetchPosts(),
    notifications: await fetchNotifications(),
  }),
  // All-or-nothing: if one fails, all fail
  // Can't invalidate user without invalidating posts
});

// Anti-pattern: Using state to coordinate queries
function Dashboard() {
  const [userData, setUserData] = useState(null);
  const [postsData, setPostsData] = useState(null);

  useEffect(() => {
    fetchUser().then(setUserData);
    fetchPosts().then(setPostsData);
  }, []);
  // No caching, no error handling, no loading states
}
```

## Good Example

```tsx
// Multiple independent useQuery calls run in parallel
function Dashboard() {
  const userQuery = useQuery({
    queryKey: ['user'],
    queryFn: fetchUser,
  });

  const postsQuery = useQuery({
    queryKey: ['posts'],
    queryFn: fetchPosts,
  });

  const notificationsQuery = useQuery({
    queryKey: ['notifications'],
    queryFn: fetchNotifications,
  });

  // Each query has independent:
  // - Loading state
  // - Error state
  // - Cache entry
  // - Refetch timing

  const isLoading =
    userQuery.isLoading ||
    postsQuery.isLoading ||
    notificationsQuery.isLoading;

  if (isLoading) return <DashboardSkeleton />;

  return (
    <div>
      <UserCard user={userQuery.data} />
      <PostList posts={postsQuery.data} />
      <Notifications items={notificationsQuery.data} />
    </div>
  );
}

// useQueries for dynamic parallel queries
function ProductComparison({ productIds }: { productIds: string[] }) {
  const productQueries = useQueries({
    queries: productIds.map((id) => ({
      queryKey: ['product', id],
      queryFn: () => fetchProduct(id),
      staleTime: 5 * 60 * 1000,
    })),
  });

  const isLoading = productQueries.some((q) => q.isLoading);
  const isError = productQueries.some((q) => q.isError);
  const products = productQueries.map((q) => q.data).filter(Boolean);

  if (isLoading) return <LoadingGrid count={productIds.length} />;
  if (isError) return <ErrorMessage />;

  return <ComparisonTable products={products} />;
}

// Combining parallel queries with combine option
function UserDashboard({ userId }: { userId: string }) {
  const { data, isLoading } = useQueries({
    queries: [
      {
        queryKey: ['user', userId],
        queryFn: () => fetchUser(userId),
      },
      {
        queryKey: ['user', userId, 'posts'],
        queryFn: () => fetchUserPosts(userId),
      },
      {
        queryKey: ['user', userId, 'followers'],
        queryFn: () => fetchUserFollowers(userId),
      },
    ],
    combine: (results) => ({
      data: {
        user: results[0].data,
        posts: results[1].data,
        followers: results[2].data,
      },
      isLoading: results.some((r) => r.isLoading),
      isError: results.some((r) => r.isError),
    }),
  });

  if (isLoading) return <Skeleton />;

  return (
    <div>
      <Profile user={data.user} />
      <Posts posts={data.posts} />
      <Followers followers={data.followers} />
    </div>
  );
}

// Parallel queries with different stale times
function SettingsPage() {
  // Static reference data - long cache
  const countriesQuery = useQuery({
    queryKey: ['countries'],
    queryFn: fetchCountries,
    staleTime: Infinity,
  });

  // User preferences - moderate cache
  const preferencesQuery = useQuery({
    queryKey: ['preferences'],
    queryFn: fetchPreferences,
    staleTime: 5 * 60 * 1000,
  });

  // Usage stats - fresh data
  const statsQuery = useQuery({
    queryKey: ['usage-stats'],
    queryFn: fetchUsageStats,
    staleTime: 0,
  });

  return (
    <Settings
      countries={countriesQuery.data}
      preferences={preferencesQuery.data}
      stats={statsQuery.data}
      isLoading={{
        countries: countriesQuery.isLoading,
        preferences: preferencesQuery.isLoading,
        stats: statsQuery.isLoading,
      }}
    />
  );
}

// Suspense mode for cleaner parallel queries
function SuspenseDashboard() {
  // With Suspense, these automatically run in parallel
  // and suspend together until all resolve
  const { data: user } = useSuspenseQuery({
    queryKey: ['user'],
    queryFn: fetchUser,
  });

  const { data: posts } = useSuspenseQuery({
    queryKey: ['posts'],
    queryFn: fetchPosts,
  });

  // No loading checks needed - Suspense handles it
  return (
    <div>
      <UserProfile user={user} />
      <PostList posts={posts} />
    </div>
  );
}

// With React Suspense boundary
function App() {
  return (
    <Suspense fallback={<DashboardSkeleton />}>
      <SuspenseDashboard />
    </Suspense>
  );
}
```

## Why

1. **Performance**: Parallel requests reduce total loading time compared to sequential waterfalls.

2. **Independent Caching**: Each query has its own cache entry with separate staleness and invalidation.

3. **Granular Loading**: Different parts of the UI can show content as it becomes available.

4. **Error Isolation**: Failure in one query doesn't affect others; components can show partial data.

5. **Flexible Invalidation**: Queries can be invalidated independently based on user actions.

6. **Dynamic Queries**: `useQueries` handles variable numbers of parallel queries cleanly.

Performance comparison:
```
Sequential (waterfall):
  User ─────────> 200ms
                  Posts ─────────> 200ms
                                   Notifications ─────────> 200ms
  Total: ~600ms

Parallel:
  User ─────────────────> 200ms
  Posts ────────────────> 200ms
  Notifications ────────> 200ms
  Total: ~200ms (3x faster)
```

When to use each approach:
- **Multiple useQuery**: Fixed number of known queries
- **useQueries**: Dynamic/variable number of queries
- **useSuspenseQueries**: With React Suspense for cleaner code
