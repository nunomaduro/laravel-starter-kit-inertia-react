---
title: Dependent Queries Pattern
impact: HIGH
section: Advanced Queries
tags: react-query, dependent-queries, sequential
---

# Dependent Queries

**Impact: HIGH**


Dependent queries are queries that rely on data from previous queries. They should only execute when their dependencies are available.

## Bad Example

```tsx
// Anti-pattern: Not disabling dependent query
function UserPosts({ userId }: { userId: string }) {
  const { data: user } = useQuery({
    queryKey: ['user', userId],
    queryFn: () => fetchUser(userId),
  });

  // This runs immediately, even before user is loaded
  const { data: posts } = useQuery({
    queryKey: ['posts', user?.id], // user?.id is undefined initially
    queryFn: () => fetchUserPosts(user!.id), // Crashes or fetches wrong data
  });
}

// Anti-pattern: Early return breaking hooks
function ProfileWithPosts({ userId }: { userId: string }) {
  const { data: user, isLoading } = useQuery({
    queryKey: ['user', userId],
    queryFn: () => fetchUser(userId),
  });

  if (isLoading || !user) {
    return <Loading />;
  }

  // This hook is conditionally called - breaks React rules!
  const { data: posts } = useQuery({
    queryKey: ['posts', user.teamId],
    queryFn: () => fetchTeamPosts(user.teamId),
  });

  return <div>...</div>;
}

// Anti-pattern: Nested queries in callback
// ❌ onSuccess on useQuery was removed in TanStack Query v5 — do not use
function BadDependentQueries({ userId }: { userId: string }) {
  const [posts, setPosts] = useState([]);

  const { data: user } = useQuery({
    queryKey: ['user', userId],
    queryFn: () => fetchUser(userId),
    // onSuccess no longer exists in v5 — and even in v4 this was wrong:
    // fetching inside callbacks has no caching, no loading state, no error handling
  });
}
```

## Good Example

```tsx
// Proper dependent query with enabled option
function UserPosts({ userId }: { userId: string }) {
  const { data: user, isLoading: isUserLoading } = useQuery({
    queryKey: ['user', userId],
    queryFn: () => fetchUser(userId),
  });

  const {
    data: posts,
    isLoading: isPostsLoading,
  } = useQuery({
    queryKey: ['posts', user?.id],
    queryFn: () => fetchUserPosts(user!.id),
    enabled: !!user?.id, // Only fetch when user.id exists
  });

  if (isUserLoading) return <UserSkeleton />;
  if (!user) return <NotFound />;

  return (
    <div>
      <UserProfile user={user} />
      {isPostsLoading ? (
        <PostsSkeleton />
      ) : (
        <PostList posts={posts} />
      )}
    </div>
  );
}

// Multiple dependent queries
function ProjectDashboard({ projectId }: { projectId: string }) {
  // Level 1: Project
  const { data: project } = useQuery({
    queryKey: ['project', projectId],
    queryFn: () => fetchProject(projectId),
  });

  // Level 2: Depends on project
  const { data: team } = useQuery({
    queryKey: ['team', project?.teamId],
    queryFn: () => fetchTeam(project!.teamId),
    enabled: !!project?.teamId,
  });

  const { data: settings } = useQuery({
    queryKey: ['project-settings', projectId],
    queryFn: () => fetchProjectSettings(projectId),
    enabled: !!project, // Only fetch if project exists
  });

  // Level 3: Depends on team
  const { data: members } = useQuery({
    queryKey: ['team-members', team?.id],
    queryFn: () => fetchTeamMembers(team!.id),
    enabled: !!team?.id,
  });

  return (
    <Dashboard
      project={project}
      team={team}
      settings={settings}
      members={members}
    />
  );
}

// Dependent query with combined loading state
function useUserWithPosts(userId: string) {
  const userQuery = useQuery({
    queryKey: ['user', userId],
    queryFn: () => fetchUser(userId),
  });

  const postsQuery = useQuery({
    queryKey: ['posts', userQuery.data?.id],
    queryFn: () => fetchUserPosts(userQuery.data!.id),
    enabled: !!userQuery.data?.id,
  });

  return {
    user: userQuery.data,
    posts: postsQuery.data,
    isLoading: userQuery.isLoading || (userQuery.isSuccess && postsQuery.isLoading),
    isError: userQuery.isError || postsQuery.isError,
    error: userQuery.error || postsQuery.error,
  };
}

// Dependent query with transformation
function OrderDetails({ orderId }: { orderId: string }) {
  const { data: order } = useQuery({
    queryKey: ['order', orderId],
    queryFn: () => fetchOrder(orderId),
  });

  // Fetch all products for the order items
  const { data: products } = useQuery({
    queryKey: ['products', order?.items.map(i => i.productId)],
    queryFn: () => fetchProducts(order!.items.map(i => i.productId)),
    enabled: !!order?.items.length,
    select: (products) => {
      // Create lookup map for easy access
      return new Map(products.map(p => [p.id, p]));
    },
  });

  // Enrich order items with product details
  const enrichedItems = order?.items.map(item => ({
    ...item,
    product: products?.get(item.productId),
  }));

  return <OrderView order={order} items={enrichedItems} />;
}

// Parallel independent + dependent queries
function Dashboard({ userId }: { userId: string }) {
  // These can run in parallel
  const { data: user } = useQuery({
    queryKey: ['user', userId],
    queryFn: () => fetchUser(userId),
  });

  const { data: notifications } = useQuery({
    queryKey: ['notifications', userId],
    queryFn: () => fetchNotifications(userId),
  });

  // This depends on user
  const { data: recommendations } = useQuery({
    queryKey: ['recommendations', user?.preferences],
    queryFn: () => fetchRecommendations(user!.preferences),
    enabled: !!user?.preferences,
  });

  return (
    <div>
      <Header user={user} notifications={notifications} />
      <Recommendations items={recommendations} />
    </div>
  );
}
```

## Why

1. **Avoid Invalid Requests**: Dependent queries prevent fetching with undefined parameters.

2. **Hook Rules**: Using `enabled` instead of conditional returns keeps hooks unconditional.

3. **Proper Caching**: Each query has its own cache entry and can be invalidated independently.

4. **Loading States**: Each query's loading state can be tracked separately for granular UI feedback.

5. **Error Boundaries**: Errors in dependent queries don't affect parent queries.

6. **Automatic Refetching**: When parent data changes, dependent queries automatically re-execute.

Query states with `enabled: false`:
- `isPending`: true (waiting to be enabled)
- `fetchStatus`: 'idle'
- `status`: 'pending'
- `isLoading`: false (not actively loading)

The query transitions to loading state only when `enabled` becomes true.
