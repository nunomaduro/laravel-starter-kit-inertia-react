---
title: Query Invalidation
impact: HIGH
section: Cache & Performance
tags: react-query, invalidation, cache-updates
---

# Query Invalidation Strategies

**Impact: HIGH**


Query invalidation marks cached data as stale, triggering refetch for active queries. Proper invalidation keeps data fresh after mutations while avoiding over-fetching.

## Bad Example

```tsx
// Anti-pattern: Invalidating everything
const mutation = useMutation({
  mutationFn: updateUser,
  onSuccess: () => {
    queryClient.invalidateQueries(); // Invalidates ALL queries - overkill
  },
});

// Anti-pattern: Not invalidating after mutation
const mutation = useMutation({
  mutationFn: createPost,
  onSuccess: () => {
    toast.success('Post created!');
    // Forgot to invalidate - list still shows old data
  },
});

// Anti-pattern: Using exact match when broader invalidation needed
const mutation = useMutation({
  mutationFn: updateUser,
  onSuccess: () => {
    queryClient.invalidateQueries({
      queryKey: ['user', userId],
      exact: true,
    });
    // Doesn't invalidate ['user', userId, 'posts'] or ['users']
  },
});

// Anti-pattern: Using onSettled for invalidation without optimistic updates
// (wastes bandwidth on error; use onSuccess instead when not using optimistic updates)
const mutation = useMutation({
  mutationFn: updateUser,
  onSettled: () => {
    queryClient.invalidateQueries({ queryKey: ['users'] });
    // ❌ Refetches even when the mutation failed — prefer onSuccess here
    // ✅ onSettled IS correct when paired with optimistic updates (see rq-optimistic-updates)
  },
});
```

## Good Example

```tsx
// Targeted invalidation after mutation
const mutation = useMutation({
  mutationFn: updateUser,
  onSuccess: (updatedUser) => {
    // Invalidate specific user
    queryClient.invalidateQueries({ queryKey: ['user', updatedUser.id] });
    // Invalidate users list
    queryClient.invalidateQueries({ queryKey: ['users'] });
  },
});

// Using query key hierarchy for efficient invalidation
const userKeys = {
  all: ['users'] as const,
  lists: () => [...userKeys.all, 'list'] as const,
  list: (filters: Filters) => [...userKeys.lists(), filters] as const,
  details: () => [...userKeys.all, 'detail'] as const,
  detail: (id: string) => [...userKeys.details(), id] as const,
};

const mutation = useMutation({
  mutationFn: updateUser,
  onSuccess: (user) => {
    // Invalidate all queries starting with ['users', 'detail', id]
    queryClient.invalidateQueries({ queryKey: userKeys.detail(user.id) });
    // Invalidate all list queries
    queryClient.invalidateQueries({ queryKey: userKeys.lists() });
  },
});

// Conditional invalidation based on changes
const mutation = useMutation({
  mutationFn: updatePost,
  onSuccess: (updatedPost, variables) => {
    // Always invalidate the specific post
    queryClient.invalidateQueries({ queryKey: ['post', updatedPost.id] });

    // Only invalidate lists if status changed (affects filtering)
    if (variables.status !== undefined) {
      queryClient.invalidateQueries({ queryKey: ['posts'] });
    }

    // Invalidate tag queries if tags changed
    if (variables.tags !== undefined) {
      queryClient.invalidateQueries({ queryKey: ['tags'] });
    }
  },
});

// Direct cache update + background invalidation
const mutation = useMutation({
  mutationFn: toggleTodoComplete,
  onMutate: async (todoId) => {
    await queryClient.cancelQueries({ queryKey: ['todos'] });
    const previous = queryClient.getQueryData(['todos']);

    // Optimistic update
    queryClient.setQueryData(['todos'], (old: Todo[]) =>
      old.map((t) =>
        t.id === todoId ? { ...t, completed: !t.completed } : t
      )
    );

    return { previous };
  },
  onError: (err, todoId, context) => {
    queryClient.setQueryData(['todos'], context?.previous);
  },
  onSettled: () => {
    // Refetch to ensure server state after optimistic update
    queryClient.invalidateQueries({ queryKey: ['todos'] });
  },
});

// Invalidate with predicate for complex matching
const mutation = useMutation({
  mutationFn: bulkUpdatePosts,
  onSuccess: (_, variables) => {
    queryClient.invalidateQueries({
      predicate: (query) => {
        // Invalidate any post query that was updated
        if (query.queryKey[0] === 'post') {
          const postId = query.queryKey[1] as string;
          return variables.postIds.includes(postId);
        }
        // Invalidate posts lists
        if (query.queryKey[0] === 'posts') {
          return true;
        }
        return false;
      },
    });
  },
});

// Invalidation with refetch options
function useDeleteUser() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: deleteUserApi,
    onSuccess: (_, userId) => {
      // Remove specific user from cache entirely
      queryClient.removeQueries({ queryKey: ['user', userId] });

      // Invalidate lists but don't refetch inactive ones
      queryClient.invalidateQueries({
        queryKey: ['users'],
        refetchType: 'active', // Only refetch currently rendered queries
      });
    },
  });
}

// Batch invalidation for related resources
function useCreateOrder() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: createOrderApi,
    onSuccess: (order) => {
      // Invalidate all related queries
      queryClient.invalidateQueries({ queryKey: ['orders'] });
      queryClient.invalidateQueries({ queryKey: ['cart'] });
      queryClient.invalidateQueries({ queryKey: ['inventory'] });

      // Set the new order directly in cache
      queryClient.setQueryData(['order', order.id], order);
    },
  });
}

// Await invalidation before navigation
async function handleOrderSubmit(data: OrderData) {
  const order = await createOrderMutation.mutateAsync(data);

  // Wait for invalidation to complete before navigating
  await queryClient.invalidateQueries({ queryKey: ['orders'] });

  navigate(`/orders/${order.id}`);
}
```

## Why

1. **Data Freshness**: Invalidation ensures UI reflects server state after mutations.

2. **Performance**: Targeted invalidation avoids refetching unrelated data.

3. **Hierarchy Support**: Query key prefixes enable invalidating related queries efficiently.

4. **Flexibility**: Predicates allow complex invalidation logic for bulk operations.

5. **Control**: `refetchType` option controls whether inactive queries are refetched.

6. **Await Support**: Invalidation returns a promise for awaiting completion.

Invalidation options:
- `queryKey`: Match queries starting with this key
- `exact`: Only match exact key (default: false)
- `predicate`: Custom function to determine which queries to invalidate
- `refetchType`: 'active' | 'inactive' | 'all' | 'none'

Related methods:
- `invalidateQueries`: Mark as stale, trigger refetch if active
- `refetchQueries`: Force immediate refetch
- `resetQueries`: Reset to initial state
- `removeQueries`: Remove from cache entirely
- `cancelQueries`: Cancel in-flight requests
