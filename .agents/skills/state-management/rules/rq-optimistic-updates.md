---
title: Optimistic Updates
impact: HIGH
section: Mutation & Updates
tags: react-query, optimistic-updates, ux
---

# Optimistic Updates

**Impact: HIGH**


Optimistic updates immediately reflect changes in the UI before the server confirms them, providing instant feedback. Proper implementation includes rollback on failure.

## Bad Example

```tsx
// Anti-pattern: Optimistic update without rollback
const mutation = useMutation({
  mutationFn: updateTodo,
  onMutate: (updatedTodo) => {
    queryClient.setQueryData(['todos'], (old: Todo[]) =>
      old.map(t => t.id === updatedTodo.id ? updatedTodo : t)
    );
    // No snapshot for rollback!
  },
  onError: () => {
    toast.error('Update failed');
    // Can't rollback - previous data is lost
  },
});

// Anti-pattern: Not canceling outgoing queries
const mutation = useMutation({
  mutationFn: toggleTodo,
  onMutate: async (todoId) => {
    // Race condition: refetch might overwrite optimistic update
    const previous = queryClient.getQueryData(['todos']);
    queryClient.setQueryData(['todos'], /* update */);
    return { previous };
  },
});

// Anti-pattern: Optimistic update for complex operations
const mutation = useMutation({
  mutationFn: reorderTodos, // Server calculates new positions
  onMutate: (newOrder) => {
    // Client might calculate positions differently than server
    queryClient.setQueryData(['todos'], sortByOrder(newOrder));
  },
});
```

## Good Example

```tsx
// Complete optimistic update pattern
function useToggleTodo() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (todoId: string) => toggleTodoApi(todoId),

    onMutate: async (todoId) => {
      // 1. Cancel outgoing refetches to prevent race conditions
      await queryClient.cancelQueries({ queryKey: ['todos'] });

      // 2. Snapshot current state for rollback
      const previousTodos = queryClient.getQueryData<Todo[]>(['todos']);

      // 3. Optimistically update the cache
      queryClient.setQueryData<Todo[]>(['todos'], (old) =>
        old?.map((todo) =>
          todo.id === todoId
            ? { ...todo, completed: !todo.completed }
            : todo
        )
      );

      // 4. Return context with snapshot
      return { previousTodos };
    },

    onError: (error, todoId, context) => {
      // 5. Rollback on error
      if (context?.previousTodos) {
        queryClient.setQueryData(['todos'], context.previousTodos);
      }
      toast.error('Failed to update todo');
    },

    onSettled: () => {
      // 6. Refetch to ensure server state
      queryClient.invalidateQueries({ queryKey: ['todos'] });
    },
  });
}

// Optimistic update with multiple cache entries
function useUpdateUser() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: updateUserApi,

    onMutate: async (updatedUser) => {
      await queryClient.cancelQueries({ queryKey: ['users'] });
      await queryClient.cancelQueries({ queryKey: ['user', updatedUser.id] });

      // Snapshot both caches
      const previousUsers = queryClient.getQueryData<User[]>(['users']);
      const previousUser = queryClient.getQueryData<User>(['user', updatedUser.id]);

      // Update list cache
      queryClient.setQueryData<User[]>(['users'], (old) =>
        old?.map((u) => (u.id === updatedUser.id ? { ...u, ...updatedUser } : u))
      );

      // Update detail cache
      queryClient.setQueryData<User>(['user', updatedUser.id], (old) =>
        old ? { ...old, ...updatedUser } : old
      );

      return { previousUsers, previousUser };
    },

    onError: (error, variables, context) => {
      // Rollback both caches
      if (context?.previousUsers) {
        queryClient.setQueryData(['users'], context.previousUsers);
      }
      if (context?.previousUser) {
        queryClient.setQueryData(['user', variables.id], context.previousUser);
      }
    },

    onSettled: (data, error, variables) => {
      queryClient.invalidateQueries({ queryKey: ['users'] });
      queryClient.invalidateQueries({ queryKey: ['user', variables.id] });
    },
  });
}

// Optimistic add to list
function useAddTodo() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: addTodoApi,

    onMutate: async (newTodo) => {
      await queryClient.cancelQueries({ queryKey: ['todos'] });

      const previousTodos = queryClient.getQueryData<Todo[]>(['todos']);

      // Add optimistic todo with temporary ID
      const optimisticTodo: Todo = {
        id: `temp-${Date.now()}`,
        ...newTodo,
        createdAt: new Date().toISOString(),
        isOptimistic: true, // Flag for UI styling
      };

      queryClient.setQueryData<Todo[]>(['todos'], (old) =>
        old ? [optimisticTodo, ...old] : [optimisticTodo]
      );

      return { previousTodos, optimisticTodo };
    },

    onSuccess: (serverTodo, variables, context) => {
      // Replace optimistic todo with server response
      queryClient.setQueryData<Todo[]>(['todos'], (old) =>
        old?.map((t) =>
          t.id === context?.optimisticTodo.id ? serverTodo : t
        )
      );
    },

    onError: (error, variables, context) => {
      if (context?.previousTodos) {
        queryClient.setQueryData(['todos'], context.previousTodos);
      }
    },
  });
}

// Component showing optimistic state
function TodoItem({ todo }: { todo: Todo }) {
  const toggleTodo = useToggleTodo();

  return (
    <li
      className={todo.isOptimistic ? 'opacity-50' : ''}
      onClick={() => toggleTodo.mutate(todo.id)}
    >
      <input
        type="checkbox"
        checked={todo.completed}
        readOnly
      />
      {todo.title}
      {todo.isOptimistic && <span>(saving...)</span>}
    </li>
  );
}
```

## Why

1. **Instant Feedback**: Users see changes immediately, making the app feel fast and responsive.

2. **Reduced Perceived Latency**: No waiting for server round-trip before UI updates.

3. **Graceful Failure**: Proper rollback ensures data consistency if the server rejects the change.

4. **Race Condition Prevention**: Canceling queries prevents refetches from overwriting optimistic updates.

5. **User Confidence**: Visual indicators for optimistic state keep users informed.

When to use optimistic updates:
- Simple, predictable changes (toggles, increments, text edits)
- High-confidence operations (usually succeed)
- Actions where immediate feedback improves UX

When NOT to use:
- Complex server-side calculations
- Operations with high failure rates
- Changes depending on server validation
- Financial or critical data modifications
