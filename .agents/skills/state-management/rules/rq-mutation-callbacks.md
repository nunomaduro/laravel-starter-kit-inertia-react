---
title: Mutation Callbacks
impact: HIGH
section: Mutation & Updates
tags: react-query, mutations, callbacks
---

# Mutation Callbacks

**Impact: HIGH**


Mutation callbacks (onSuccess, onError, onSettled, onMutate) provide hooks into the mutation lifecycle for side effects, cache updates, and error handling.

## Bad Example

```tsx
// Anti-pattern: Side effects in mutationFn
const mutation = useMutation({
  mutationFn: async (data) => {
    const result = await createUser(data);
    toast.success('User created!'); // Side effect in mutationFn
    queryClient.invalidateQueries({ queryKey: ['users'] }); // Cache update here
    navigate('/users'); // Navigation here
    return result;
  },
});

// Anti-pattern: Duplicate callbacks in hook and mutate call
const mutation = useMutation({
  mutationFn: createUser,
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: ['users'] });
  },
});

// Later in component
mutation.mutate(data, {
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: ['users'] }); // Duplicate!
    navigate('/users');
  },
});

// Anti-pattern: Not handling errors
const mutation = useMutation({
  mutationFn: createUser,
  onSuccess: (data) => {
    navigate(`/users/${data.id}`);
  },
  // No onError - user has no idea if it failed
});
```

## Good Example

```tsx
// Separate concerns: hook handles cache, component handles UI
function useCreateUser() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: createUser,
    // Cache-related side effects in the hook
    onSuccess: (newUser) => {
      // Update cache
      queryClient.invalidateQueries({ queryKey: ['users'] });
      // Or optimistically add to cache
      queryClient.setQueryData<User[]>(['users'], (old) =>
        old ? [...old, newUser] : [newUser]
      );
    },
    onError: (error) => {
      // Log errors for monitoring
      console.error('Create user failed:', error);
    },
  });
}

// Component handles UI-specific side effects
function CreateUserPage() {
  const navigate = useNavigate();
  const createUser = useCreateUser();

  const handleSubmit = (data: CreateUserInput) => {
    createUser.mutate(data, {
      // UI-specific callbacks
      onSuccess: (newUser) => {
        toast.success(`Welcome, ${newUser.name}!`);
        navigate(`/users/${newUser.id}`);
      },
      onError: (error) => {
        toast.error(error.message);
      },
    });
  };

  return <UserForm onSubmit={handleSubmit} isLoading={createUser.isPending} />;
}

// All callbacks with proper typing
interface MutationContext {
  previousUsers: User[] | undefined;
}

const mutation = useMutation<User, ApiError, CreateUserInput, MutationContext>({
  mutationFn: createUser,
  onMutate: async (variables) => {
    // Called before mutationFn
    // Cancel outgoing refetches
    await queryClient.cancelQueries({ queryKey: ['users'] });

    // Snapshot current value
    const previousUsers = queryClient.getQueryData<User[]>(['users']);

    // Return context for rollback
    return { previousUsers };
  },
  onSuccess: (data, variables, context) => {
    // data: returned from mutationFn
    // variables: passed to mutate()
    // context: returned from onMutate
    console.log(`Created user ${data.name} with email ${variables.email}`);
  },
  onError: (error, variables, context) => {
    // Rollback on error
    if (context?.previousUsers) {
      queryClient.setQueryData(['users'], context.previousUsers);
    }
    toast.error(`Failed to create user: ${error.message}`);
  },
  onSettled: (data, error, variables, context) => {
    // Always runs after success or error
    // Good place for cleanup
    queryClient.invalidateQueries({ queryKey: ['users'] });
  },
});

// Using mutateAsync with callbacks
async function handleSequentialMutations() {
  try {
    const user = await createUserMutation.mutateAsync(userData, {
      onSuccess: () => {
        // This runs before the promise resolves
        console.log('User created, creating profile...');
      },
    });

    await createProfileMutation.mutateAsync({ userId: user.id });
  } catch (error) {
    // Catches errors from either mutation
  }
}

// Conditional callbacks based on response
const mutation = useMutation({
  mutationFn: updateUser,
  onSuccess: (updatedUser, variables) => {
    if (updatedUser.requiresVerification) {
      navigate('/verify-email');
    } else if (variables.role !== updatedUser.role) {
      // Role change requires re-login
      logout();
    } else {
      queryClient.invalidateQueries({ queryKey: ['user', updatedUser.id] });
    }
  },
});
```

## Why

1. **Separation of Concerns**: Hook-level callbacks handle cache; component-level callbacks handle UI.

2. **Callback Order**: onMutate -> mutationFn -> onSuccess/onError -> onSettled. Understanding this enables proper optimistic updates.

3. **Context Passing**: onMutate can return context used by other callbacks for rollback or additional logic.

4. **Callback Composition**: Both hook and mutate() callbacks run; hook callbacks first, then mutate() callbacks.

5. **Error Recovery**: onError with context enables rolling back optimistic updates on failure.

6. **Cleanup**: onSettled runs regardless of outcome, perfect for cleanup and final cache invalidation.

Callback execution order:
```
mutate(variables) called
    ↓
onMutate(variables) → returns context
    ↓
mutationFn(variables) executes
    ↓
Success?
  Yes → onSuccess(data, variables, context)
  No  → onError(error, variables, context)
    ↓
onSettled(data, error, variables, context)
```

Note: Callbacks defined in both useMutation and mutate() both execute. Use hook-level for shared logic and mutate-level for component-specific logic.
