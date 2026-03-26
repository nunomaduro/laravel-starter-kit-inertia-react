---
title: Mutation Setup Best Practices
impact: CRITICAL
section: Mutation & Updates
tags: react-query, mutations, create-update-delete
---

# Mutation Setup Best Practices

**Impact: CRITICAL**


Mutations handle data modifications (create, update, delete) in React Query. Proper setup ensures reliable data changes with appropriate error handling and cache updates.

## Bad Example

```tsx
// Anti-pattern: Using useQuery for mutations
const { refetch } = useQuery({
  queryKey: ['createUser'],
  queryFn: () => createUser(userData),
  enabled: false,
});
// Calling refetch() to trigger - wrong approach

// Anti-pattern: No error handling
const mutation = useMutation({
  mutationFn: createUser,
});

const handleSubmit = () => {
  mutation.mutate(userData);
  // Assuming success without checking
  navigate('/users');
};

// Anti-pattern: Inline mutation function with no typing
const mutation = useMutation({
  mutationFn: async (data) => {
    const res = await fetch('/api/users', {
      method: 'POST',
      body: JSON.stringify(data),
    });
    return res.json(); // Not checking response status
  },
});
```

## Good Example

```tsx
// Define typed mutation functions separately
interface CreateUserInput {
  name: string;
  email: string;
  role: 'admin' | 'user';
}

interface User {
  id: string;
  name: string;
  email: string;
  role: string;
  createdAt: string;
}

async function createUser(input: CreateUserInput): Promise<User> {
  const response = await fetch('/api/users', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(input),
  });

  if (!response.ok) {
    const error = await response.json().catch(() => ({}));
    throw new ApiError(response.status, error.message || 'Failed to create user');
  }

  return response.json();
}

// Basic mutation with proper setup
function CreateUserForm() {
  const queryClient = useQueryClient();

  const mutation = useMutation({
    mutationFn: createUser,
    onSuccess: (newUser) => {
      // Invalidate users list to trigger refetch
      queryClient.invalidateQueries({ queryKey: ['users'] });
    },
    onError: (error) => {
      console.error('Failed to create user:', error);
    },
  });

  const handleSubmit = (data: CreateUserInput) => {
    mutation.mutate(data);
  };

  return (
    <form onSubmit={handleSubmit}>
      {/* Form fields */}
      <button type="submit" disabled={mutation.isPending}>
        {mutation.isPending ? 'Creating...' : 'Create User'}
      </button>
      {mutation.isError && (
        <div className="error">{mutation.error.message}</div>
      )}
    </form>
  );
}

// Reusable mutation hook with all states handled
function useCreateUser() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: createUser,
    onSuccess: (newUser) => {
      queryClient.invalidateQueries({ queryKey: ['users'] });
      toast.success(`User ${newUser.name} created successfully`);
    },
    onError: (error: ApiError) => {
      toast.error(error.message);
    },
    onSettled: () => {
      // Always runs after mutation completes (success or error)
    },
  });
}

// Usage with the custom hook
function UserManager() {
  const createUser = useCreateUser();

  return (
    <div>
      <CreateUserForm
        onSubmit={createUser.mutate}
        isLoading={createUser.isPending}
        error={createUser.error}
      />
    </div>
  );
}

// Mutation with async/await for sequential operations
async function handleComplexOperation() {
  try {
    const user = await createUserMutation.mutateAsync(userData);
    const profile = await createProfileMutation.mutateAsync({
      userId: user.id,
      ...profileData,
    });
    navigate(`/users/${user.id}`);
  } catch (error) {
    // Handle any error in the chain
    console.error('Operation failed:', error);
  }
}
```

## Why

1. **Separation from Queries**: Mutations have different semantics - they modify data and should use useMutation, not useQuery.

2. **Error Handling**: Proper mutation setup includes error states that can be displayed to users.

3. **Loading States**: The `isPending` flag enables proper UI feedback during mutations.

4. **Type Safety**: Typed mutation functions catch errors at compile time and provide better developer experience.

5. **Cache Management**: Mutations should handle cache invalidation or updates to keep data consistent.

6. **Reusability**: Custom mutation hooks encapsulate logic and can be reused across components.

Key mutation properties:
- `mutate()`: Fire and forget mutation
- `mutateAsync()`: Returns promise for sequential operations
- `isPending`: Mutation is in progress
- `isSuccess`: Mutation completed successfully
- `isError`: Mutation failed
- `error`: Error object if failed
- `data`: Response data if successful
- `reset()`: Reset mutation state
