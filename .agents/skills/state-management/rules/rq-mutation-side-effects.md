---
title: Mutation Side Effects
impact: MEDIUM
section: Mutation & Updates
tags: react-query, mutations, side-effects
---

# Mutation Side Effects

**Impact: MEDIUM**


Side effects are actions triggered by mutation results, such as cache updates, navigation, notifications, and analytics. Proper organization keeps mutations maintainable.

## Bad Example

```tsx
// Anti-pattern: All side effects in mutationFn
const mutation = useMutation({
  mutationFn: async (data) => {
    const result = await createUser(data);

    // Side effects mixed with data fetching
    queryClient.invalidateQueries({ queryKey: ['users'] });
    toast.success('User created!');
    analytics.track('user_created', { id: result.id });
    router.push(`/users/${result.id}`);
    localStorage.setItem('lastCreatedUser', result.id);

    return result;
  },
});

// Anti-pattern: Side effects that throw
const mutation = useMutation({
  mutationFn: updateUser,
  onSuccess: async (data) => {
    await riskyAsyncOperation(); // If this throws, mutation appears to fail
    queryClient.invalidateQueries({ queryKey: ['users'] });
  },
});

// Anti-pattern: Duplicate side effects across components
// Component A
const mutation = useMutation({
  mutationFn: updateUser,
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: ['users'] });
    analytics.track('user_updated');
  },
});

// Component B - duplicates the same side effects
const mutation = useMutation({
  mutationFn: updateUser,
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: ['users'] });
    analytics.track('user_updated');
  },
});
```

## Good Example

```tsx
// Centralize cache-related side effects in custom hook
function useUpdateUser() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: updateUserApi,
    onSuccess: (updatedUser) => {
      // Cache updates - always needed
      queryClient.invalidateQueries({ queryKey: ['users'] });
      queryClient.setQueryData(['user', updatedUser.id], updatedUser);
    },
    onError: (error) => {
      // Error logging - always needed
      errorReporter.capture(error);
    },
  });
}

// Component adds UI-specific side effects
function EditUserPage({ userId }: { userId: string }) {
  const navigate = useNavigate();
  const updateUser = useUpdateUser();

  const handleSubmit = (data: UserFormData) => {
    updateUser.mutate(
      { id: userId, data },
      {
        // UI-specific side effects
        onSuccess: (user) => {
          toast.success(`${user.name} updated successfully`);
          navigate(`/users/${user.id}`);
        },
        onError: (error) => {
          toast.error(error.message);
        },
      }
    );
  };

  return <UserForm onSubmit={handleSubmit} />;
}

// Organized side effects by category
function useCreateOrder() {
  const queryClient = useQueryClient();
  const { track } = useAnalytics();
  const { user } = useAuth();

  return useMutation({
    mutationFn: createOrderApi,

    onSuccess: (order, variables) => {
      // 1. Cache updates
      queryClient.invalidateQueries({ queryKey: ['orders'] });
      queryClient.invalidateQueries({ queryKey: ['cart'] });
      queryClient.setQueryData(['order', order.id], order);

      // 2. Analytics (non-critical, wrapped in try-catch)
      try {
        track('order_created', {
          orderId: order.id,
          total: order.total,
          itemCount: variables.items.length,
          userId: user?.id,
        });
      } catch (e) {
        // Analytics failure shouldn't affect mutation
        console.warn('Analytics failed:', e);
      }
    },

    onError: (error, variables) => {
      // 3. Error tracking
      errorReporter.capture(error, {
        context: 'order_creation',
        itemCount: variables.items.length,
      });
    },

    onSettled: () => {
      // 4. Cleanup
      queryClient.invalidateQueries({ queryKey: ['inventory'] });
    },
  });
}

// Safe async side effects
function useDeleteAccount() {
  const queryClient = useQueryClient();
  const { logout } = useAuth();

  return useMutation({
    mutationFn: deleteAccountApi,
    onSuccess: async () => {
      // Critical side effect
      queryClient.clear(); // Clear all cached data

      // Non-critical async side effects - handle errors
      try {
        await Promise.all([
          clearLocalStorage(),
          unsubscribeFromNotifications(),
          revokeTokens(),
        ]);
      } catch (e) {
        // Log but don't fail the mutation
        console.error('Cleanup error:', e);
      }

      // Navigate last
      logout();
    },
  });
}

// Conditional side effects
function useUpdatePost() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: updatePostApi,
    onSuccess: (post, variables) => {
      // Always update caches
      queryClient.invalidateQueries({ queryKey: ['posts'] });
      queryClient.setQueryData(['post', post.id], post);

      // Conditional side effects based on what changed
      if (variables.data.status === 'published' && !post.wasPublished) {
        // Newly published - notify followers
        notifyFollowers(post.authorId, post.id);
      }

      if (variables.data.tags !== undefined) {
        // Tags changed - update tag caches
        queryClient.invalidateQueries({ queryKey: ['tags'] });
      }
    },
  });
}

// Global mutation side effects via MutationCache
const mutationCache = new MutationCache({
  onError: (error, variables, context, mutation) => {
    // Global error handling for all mutations
    if (error instanceof ApiError && error.status === 401) {
      // Redirect to login for any 401
      logout();
      return;
    }

    // Report to error tracking
    errorReporter.capture(error, {
      mutation: mutation.options.mutationKey,
    });
  },
  onSuccess: (data, variables, context, mutation) => {
    // Global success tracking
    analytics.track('mutation_success', {
      mutation: mutation.options.mutationKey,
    });
  },
});

const queryClient = new QueryClient({ mutationCache });
```

## Why

1. **Maintainability**: Separating side effects by type (cache, UI, analytics) makes code easier to understand.

2. **Reusability**: Hook-level side effects are shared; component-level are specific to that use case.

3. **Reliability**: Wrapping non-critical side effects prevents them from breaking the mutation flow.

4. **Testability**: Pure mutationFn is easier to test; side effects can be tested separately.

5. **Consistency**: Global side effects ensure consistent behavior across the app (error tracking, analytics).

Side effect categories:
1. **Cache updates** - Critical, in hook
2. **Navigation** - UI-specific, in component
3. **Notifications** - UI-specific, in component
4. **Analytics** - Non-critical, wrap in try-catch
5. **Error tracking** - Critical, in hook or global
6. **Cleanup** - In onSettled, runs regardless of outcome

Order of execution:
```
Hook onSuccess → Component onSuccess → Hook onSettled → Component onSettled
```
