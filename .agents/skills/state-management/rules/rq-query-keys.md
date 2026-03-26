---
title: Query Keys Best Practices
impact: CRITICAL
section: Query Fundamentals
tags: react-query, query-keys, caching
---

# Query Keys Best Practices

**Impact: CRITICAL**

Query keys are the foundation of React Query's caching and refetching system. They must be unique, serializable, and structured for effective cache management.

## Bad Example

```tsx
// Anti-pattern: Using primitive strings without structure
const { data: user } = useQuery({
  queryKey: ['user'],
  queryFn: () => fetchUser(userId),
});

const { data: posts } = useQuery({
  queryKey: ['posts'],
  queryFn: () => fetchUserPosts(userId, page),
});

// Anti-pattern: Inconsistent key structure
const { data: comments } = useQuery({
  queryKey: [`comments-${postId}`],
  queryFn: () => fetchComments(postId),
});

// Anti-pattern: Missing dependencies in key
const { data: filtered } = useQuery({
  queryKey: ['products'],
  queryFn: () => fetchProducts(category, sortBy, filters),
});
```

## Good Example

```tsx
// Create a query key factory for consistency
const userKeys = {
  all: ['users'] as const,
  lists: () => [...userKeys.all, 'list'] as const,
  list: (filters: UserFilters) => [...userKeys.lists(), filters] as const,
  details: () => [...userKeys.all, 'detail'] as const,
  detail: (id: string) => [...userKeys.details(), id] as const,
};

const postKeys = {
  all: ['posts'] as const,
  lists: () => [...postKeys.all, 'list'] as const,
  list: (userId: string, page: number) => [...postKeys.lists(), { userId, page }] as const,
  details: () => [...postKeys.all, 'detail'] as const,
  detail: (id: string) => [...postKeys.details(), id] as const,
  comments: (postId: string) => [...postKeys.detail(postId), 'comments'] as const,
};

// Usage with factory
const { data: user } = useQuery({
  queryKey: userKeys.detail(userId),
  queryFn: () => fetchUser(userId),
});

const { data: posts } = useQuery({
  queryKey: postKeys.list(userId, page),
  queryFn: () => fetchUserPosts(userId, page),
});

const { data: products } = useQuery({
  queryKey: ['products', { category, sortBy, filters }],
  queryFn: () => fetchProducts(category, sortBy, filters),
});

// Invalidation becomes easy
queryClient.invalidateQueries({ queryKey: userKeys.all });
queryClient.invalidateQueries({ queryKey: postKeys.lists() });
```

## Why

1. **Cache Isolation**: Structured keys ensure each unique data request has its own cache entry, preventing data collisions.

2. **Granular Invalidation**: Hierarchical keys allow invalidating specific subsets of data (e.g., all user lists vs. a specific user detail).

3. **Automatic Refetching**: When dependencies in the key change, React Query automatically refetches with the new parameters.

4. **Type Safety**: Query key factories provide TypeScript support and prevent typos.

5. **Debugging**: Consistent, structured keys make it easier to inspect and debug cache state in React Query DevTools.

6. **Maintainability**: Centralized key definitions make refactoring easier and ensure consistency across the codebase.
