---
title: Query Conditional Execution
impact: CRITICAL
section: Query Fundamentals
tags: react-query, conditional-queries
---

# Enabled Option for Conditional Queries

**Impact: CRITICAL**


The enabled option controls whether a query should execute. It's essential for dependent queries, conditional fetching, and avoiding unnecessary requests.

## Bad Example

```tsx
// Anti-pattern: Fetching with undefined/null parameters
const { data: userPosts } = useQuery({
  queryKey: ['posts', userId],
  queryFn: () => fetchUserPosts(userId), // userId might be undefined
});

// Anti-pattern: Using early return to prevent query
function UserProfile({ userId }: { userId?: string }) {
  if (!userId) {
    return <div>Select a user</div>;
  }

  // This causes hook order issues when userId changes from undefined to defined
  const { data } = useQuery({
    queryKey: ['user', userId],
    queryFn: () => fetchUser(userId),
  });

  return <div>{data?.name}</div>;
}

// Anti-pattern: Complex conditions in queryFn
const { data } = useQuery({
  queryKey: ['data', someCondition],
  queryFn: async () => {
    if (!someCondition) {
      return null; // Returning null instead of disabling query
    }
    return fetchData();
  },
});
```

## Good Example

```tsx
// Dependent query - wait for userId
function UserProfile({ userId }: { userId?: string }) {
  const { data, isLoading } = useQuery({
    queryKey: ['user', userId],
    queryFn: () => fetchUser(userId!),
    enabled: !!userId, // Only fetch when userId exists
  });

  if (!userId) {
    return <div>Select a user</div>;
  }

  if (isLoading) {
    return <div>Loading...</div>;
  }

  return <div>{data?.name}</div>;
}

// Chained/dependent queries
function UserPosts({ userId }: { userId: string }) {
  const { data: user } = useQuery({
    queryKey: ['user', userId],
    queryFn: () => fetchUser(userId),
  });

  const { data: posts } = useQuery({
    queryKey: ['posts', user?.id],
    queryFn: () => fetchUserPosts(user!.id),
    enabled: !!user?.id, // Only fetch after user is loaded
  });

  return (
    <div>
      <h1>{user?.name}'s Posts</h1>
      <PostList posts={posts} />
    </div>
  );
}

// Feature flag or permission-based queries
function AdminDashboard() {
  const { user } = useAuth();

  const { data: stats } = useQuery({
    queryKey: ['admin', 'stats'],
    queryFn: fetchAdminStats,
    enabled: user?.role === 'admin', // Only fetch for admins
  });

  const { data: auditLogs } = useQuery({
    queryKey: ['admin', 'audit-logs'],
    queryFn: fetchAuditLogs,
    enabled: user?.permissions.includes('view_audit_logs'),
  });

  return <Dashboard stats={stats} logs={auditLogs} />;
}

// Toggle-based fetching
function SearchResults() {
  const [searchTerm, setSearchTerm] = useState('');
  const [shouldSearch, setShouldSearch] = useState(false);

  const { data, isFetching } = useQuery({
    queryKey: ['search', searchTerm],
    queryFn: () => search(searchTerm),
    enabled: shouldSearch && searchTerm.length >= 3,
  });

  const handleSearch = () => {
    if (searchTerm.length >= 3) {
      setShouldSearch(true);
    }
  };

  return (
    <div>
      <input
        value={searchTerm}
        onChange={(e) => {
          setSearchTerm(e.target.value);
          setShouldSearch(false);
        }}
      />
      <button onClick={handleSearch} disabled={isFetching}>
        Search
      </button>
      {data && <Results data={data} />}
    </div>
  );
}

// Multiple conditions
const { data } = useQuery({
  queryKey: ['protected-data', resourceId],
  queryFn: () => fetchProtectedData(resourceId),
  enabled: Boolean(
    isAuthenticated &&
    hasPermission &&
    resourceId &&
    !isOffline
  ),
});
```

## Why

1. **Avoid Invalid Requests**: Prevents fetching with undefined or invalid parameters that would cause API errors.

2. **Hook Rules Compliance**: React hooks must be called unconditionally; `enabled` allows conditional execution without violating hook rules.

3. **Query Dependencies**: Enables proper sequencing of dependent queries where one query needs data from another.

4. **Performance**: Prevents unnecessary API calls when data isn't needed or conditions aren't met.

5. **Authorization**: Allows queries to be gated by authentication state or user permissions.

6. **User Intent**: Supports patterns where fetching should only occur after explicit user action.

Query states when `enabled: false`:
- `status`: 'pending' (no data yet) or 'success' (has previous data)
- `fetchStatus`: 'idle' (not fetching)
- `isLoading`: false (even if no data)
- `isPending`: true (if no data) - use this to show placeholders
