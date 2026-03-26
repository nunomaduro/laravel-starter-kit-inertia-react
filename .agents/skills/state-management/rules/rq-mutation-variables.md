---
title: Mutation Variables Pattern
impact: MEDIUM
section: Mutation & Updates
tags: react-query, mutations, variables
---

# Mutation Variables

**Impact: MEDIUM**


Mutation variables are the data passed to mutate() and forwarded to mutationFn and callbacks. Proper typing and structure ensure predictable mutations.

## Bad Example

```tsx
// Anti-pattern: Untyped mutation variables
const mutation = useMutation({
  mutationFn: (data: any) => updateUser(data), // No type safety
});

// Anti-pattern: Multiple parameters instead of object
const mutation = useMutation({
  mutationFn: (id: string, name: string, email: string) =>
    updateUser(id, name, email), // mutate() only accepts one argument
});

// Anti-pattern: Including derived data in variables
const mutation = useMutation({
  mutationFn: async (variables) => {
    const { userId, formData, timestamp, userAgent } = variables;
    // timestamp and userAgent should be added in mutationFn, not passed
    return api.update(userId, formData);
  },
});

// Anti-pattern: Large objects when only ID is needed
const mutation = useMutation({
  mutationFn: (todo: Todo) => deleteTodo(todo), // Only needs todo.id
});

mutation.mutate(entireTodoObject); // Passing more than necessary
```

## Good Example

```tsx
// Properly typed mutation variables
interface UpdateUserVariables {
  id: string;
  data: {
    name?: string;
    email?: string;
    avatar?: string;
  };
}

const mutation = useMutation<User, ApiError, UpdateUserVariables>({
  mutationFn: ({ id, data }) => updateUserApi(id, data),
});

// Usage
mutation.mutate({
  id: userId,
  data: { name: 'New Name' },
});

// Object parameter for multiple values
interface CreateOrderVariables {
  items: OrderItem[];
  shippingAddress: Address;
  paymentMethod: string;
}

function useCreateOrder() {
  return useMutation<Order, ApiError, CreateOrderVariables>({
    mutationFn: (variables) => createOrderApi(variables),
    onSuccess: (order, variables) => {
      // Access variables in callbacks
      analytics.track('order_created', {
        itemCount: variables.items.length,
        total: order.total,
      });
    },
  });
}

// Simple ID-based mutations
function useDeleteTodo() {
  return useMutation<void, ApiError, string>({
    mutationFn: (todoId) => deleteTodoApi(todoId),
    onMutate: (todoId) => {
      // todoId is typed as string
      console.log(`Deleting todo: ${todoId}`);
    },
  });
}

// Usage
deleteMutation.mutate(todo.id); // Pass only what's needed

// Variables with validation
interface UploadVariables {
  file: File;
  folder: string;
}

function useUploadFile() {
  return useMutation<UploadResult, ApiError, UploadVariables>({
    mutationFn: async ({ file, folder }) => {
      // Validation in mutationFn
      if (file.size > 10 * 1024 * 1024) {
        throw new Error('File too large (max 10MB)');
      }

      const formData = new FormData();
      formData.append('file', file);
      formData.append('folder', folder);

      return uploadApi(formData);
    },
  });
}

// Accessing variables throughout mutation lifecycle
function useUpdateSettings() {
  const queryClient = useQueryClient();

  return useMutation<Settings, ApiError, Partial<Settings>>({
    mutationFn: updateSettingsApi,

    onMutate: async (newSettings) => {
      // newSettings available here
      await queryClient.cancelQueries({ queryKey: ['settings'] });
      const previous = queryClient.getQueryData<Settings>(['settings']);

      queryClient.setQueryData<Settings>(['settings'], (old) => ({
        ...old!,
        ...newSettings,
      }));

      return { previous, newSettings };
    },

    onSuccess: (result, variables, context) => {
      // variables === newSettings passed to mutate()
      // context.newSettings also available
      console.log('Updated:', Object.keys(variables));
    },

    onError: (error, variables, context) => {
      // Can log which settings failed to update
      console.error('Failed to update:', variables);
      if (context?.previous) {
        queryClient.setQueryData(['settings'], context.previous);
      }
    },
  });
}

// Deriving data inside mutationFn, not variables
function useCreatePost() {
  const { user } = useAuth();

  return useMutation<Post, ApiError, { title: string; content: string }>({
    mutationFn: async (variables) => {
      // Add metadata in mutationFn, not in variables
      return createPostApi({
        ...variables,
        authorId: user.id,
        createdAt: new Date().toISOString(),
      });
    },
  });
}

// Usage - clean variables
createPost.mutate({
  title: 'My Post',
  content: 'Content here...',
});
```

## Why

1. **Type Safety**: Properly typed variables catch errors at compile time and provide autocomplete.

2. **Single Argument**: mutate() accepts exactly one argument; use an object for multiple values.

3. **Callback Access**: Variables are passed to all callbacks, enabling logging, analytics, and rollback.

4. **Clean Interface**: Pass only the data needed for the mutation, derive metadata inside mutationFn.

5. **Predictability**: Consistent variable shapes make mutations easier to understand and test.

6. **Separation**: Keep user input (variables) separate from system-generated data (timestamps, IDs).

Variable flow:
```
mutate(variables)
    ↓
onMutate(variables) - can use for optimistic updates
    ↓
mutationFn(variables) - performs the actual mutation
    ↓
onSuccess(data, variables, context)
onError(error, variables, context)
    ↓
onSettled(data, error, variables, context)
```

TypeScript generics order:
```typescript
useMutation<TData, TError, TVariables, TContext>
```
- TData: Return type of mutationFn
- TError: Error type
- TVariables: Type passed to mutate()
- TContext: Return type of onMutate
