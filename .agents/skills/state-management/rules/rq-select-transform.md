---
title: Query Data Transformation
impact: HIGH
section: Query Fundamentals
tags: react-query, select, data-transformation
---

# Select Option for Data Transformation

**Impact: HIGH**


The select option transforms or filters query data before it reaches the component. It provides derived data without affecting the cached data and optimizes re-renders.

## Bad Example

```tsx
// Anti-pattern: Transforming data in component body
function UserList() {
  const { data: users } = useQuery({
    queryKey: ['users'],
    queryFn: fetchUsers,
  });

  // Transformation runs on every render
  const activeUsers = users?.filter(user => user.isActive);
  const sortedUsers = activeUsers?.sort((a, b) => a.name.localeCompare(b.name));

  return <List items={sortedUsers} />;
}

// Anti-pattern: Transforming in queryFn (affects cache)
const { data } = useQuery({
  queryKey: ['users'],
  queryFn: async () => {
    const users = await fetchUsers();
    // Cache only contains filtered data - can't access inactive users
    return users.filter(user => user.isActive);
  },
});

// Anti-pattern: Using useMemo for simple transformations
function UserStats() {
  const { data: users } = useQuery({
    queryKey: ['users'],
    queryFn: fetchUsers,
  });

  // Unnecessary useMemo when select would suffice
  const userCount = useMemo(() => users?.length ?? 0, [users]);
  const adminCount = useMemo(
    () => users?.filter(u => u.role === 'admin').length ?? 0,
    [users]
  );

  return <Stats total={userCount} admins={adminCount} />;
}
```

## Good Example

```tsx
// Use select for filtering
function ActiveUserList() {
  const { data: activeUsers } = useQuery({
    queryKey: ['users'],
    queryFn: fetchUsers,
    select: (users) => users.filter(user => user.isActive),
  });

  return <List items={activeUsers} />;
}

// Use select for transformation with memoization
function UserList() {
  const { data: sortedUsers } = useQuery({
    queryKey: ['users'],
    queryFn: fetchUsers,
    select: useCallback(
      (users: User[]) =>
        [...users]
          .filter(user => user.isActive)
          .sort((a, b) => a.name.localeCompare(b.name)),
      []
    ),
  });

  return <List items={sortedUsers} />;
}

// Use select for extracting specific fields
function UserCount() {
  const { data: count } = useQuery({
    queryKey: ['users'],
    queryFn: fetchUsers,
    select: (users) => users.length,
  });

  // Component only re-renders when count changes, not when users change
  return <span>Total users: {count}</span>;
}

// Reusable selector functions
const userSelectors = {
  all: (users: User[]) => users,
  active: (users: User[]) => users.filter(u => u.isActive),
  admins: (users: User[]) => users.filter(u => u.role === 'admin'),
  byId: (id: string) => (users: User[]) => users.find(u => u.id === id),
  count: (users: User[]) => users.length,
  names: (users: User[]) => users.map(u => u.name),
};

// Usage with reusable selectors
function AdminList() {
  const { data: admins } = useQuery({
    queryKey: ['users'],
    queryFn: fetchUsers,
    select: userSelectors.admins,
  });

  return <List items={admins} />;
}

// Complex transformation with parameters
function FilteredUserList({ role, status }: FilterProps) {
  const selector = useCallback(
    (users: User[]) =>
      users.filter(
        user =>
          (role === 'all' || user.role === role) &&
          (status === 'all' || user.status === status)
      ),
    [role, status]
  );

  const { data: filteredUsers } = useQuery({
    queryKey: ['users'],
    queryFn: fetchUsers,
    select: selector,
  });

  return <List items={filteredUsers} />;
}

// Combining data from multiple sources
function UserWithPosts({ userId }: { userId: string }) {
  const { data: userName } = useQuery({
    queryKey: ['users'],
    queryFn: fetchUsers,
    select: (users) => users.find(u => u.id === userId)?.name,
  });

  const { data: postCount } = useQuery({
    queryKey: ['posts', userId],
    queryFn: () => fetchUserPosts(userId),
    select: (posts) => posts.length,
  });

  return (
    <div>
      {userName} has {postCount} posts
    </div>
  );
}
```

## Why

1. **Cache Integrity**: The full API response stays in cache; select transforms data only for the specific component.

2. **Render Optimization**: When select returns the same value (via structural sharing), the component doesn't re-render.

3. **Code Reuse**: Selector functions can be shared across components using the same cached data.

4. **Performance**: Select runs only when the source data changes, not on every render.

5. **Separation of Concerns**: Keep API responses clean and handle view-specific transformations at the component level.

6. **TypeScript Support**: Select provides proper type narrowing for transformed data.

Important notes:
- Use `useCallback` to memoize select functions that depend on external values
- Select receives the cached data, so it runs after successful fetches
- Multiple components with different selects share the same cache entry
- Structural sharing means primitive returns (numbers, strings) prevent unnecessary re-renders
