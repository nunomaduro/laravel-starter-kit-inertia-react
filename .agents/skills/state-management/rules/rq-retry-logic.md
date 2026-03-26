---
title: Retry Logic Configuration
impact: MEDIUM
section: Cache & Performance
tags: react-query, retry, error-handling
---

# Retry Logic for Mutations

**Impact: MEDIUM**


Retry logic determines how React Query handles failed mutations. Unlike queries, mutations don't retry by default and require careful configuration.

## Bad Example

```tsx
// Anti-pattern: Enabling retries for non-idempotent mutations
const mutation = useMutation({
  mutationFn: createOrder, // Creates a new order each time
  retry: 3, // Might create duplicate orders!
});

// Anti-pattern: Retrying authentication mutations
const loginMutation = useMutation({
  mutationFn: loginUser,
  retry: 5, // Will lock out user after failed attempts
});

// Anti-pattern: Same retry for all error types
const mutation = useMutation({
  mutationFn: updateUser,
  retry: 3, // Retries even 400 Bad Request errors
});

// Anti-pattern: No delay between retries
const mutation = useMutation({
  mutationFn: apiCall,
  retry: 3,
  retryDelay: 0, // Hammers server immediately on failure
});
```

## Good Example

```tsx
// Default: mutations don't retry (safest for non-idempotent operations)
const createOrderMutation = useMutation({
  mutationFn: createOrder,
  // retry defaults to 0 for mutations
});

// Retry only for idempotent operations
const updateSettingsMutation = useMutation({
  mutationFn: updateSettings, // PUT operation - idempotent
  retry: 2,
  retryDelay: (attemptIndex) => Math.min(1000 * 2 ** attemptIndex, 30000),
});

// Conditional retry based on error type
const mutation = useMutation({
  mutationFn: apiCall,
  retry: (failureCount, error) => {
    // Don't retry client errors (4xx)
    if (error instanceof ApiError && error.status >= 400 && error.status < 500) {
      return false;
    }
    // Don't retry auth errors
    if (error instanceof ApiError && error.status === 401) {
      return false;
    }
    // Retry server errors and network issues up to 3 times
    return failureCount < 3;
  },
  retryDelay: (attemptIndex) => {
    // Exponential backoff: 1s, 2s, 4s...
    return Math.min(1000 * 2 ** attemptIndex, 30000);
  },
});

// Custom retry logic for specific error scenarios
function useUploadFile() {
  return useMutation({
    mutationFn: uploadFile,
    retry: (failureCount, error) => {
      // Retry network errors
      if (error.message === 'Network Error') {
        return failureCount < 5;
      }
      // Retry timeout errors
      if (error.code === 'ECONNABORTED') {
        return failureCount < 3;
      }
      // Retry 503 Service Unavailable
      if (error instanceof ApiError && error.status === 503) {
        return failureCount < 3;
      }
      // Don't retry other errors
      return false;
    },
    retryDelay: (attemptIndex, error) => {
      // Check for Retry-After header
      if (error instanceof ApiError && error.retryAfter) {
        return error.retryAfter * 1000;
      }
      // Default exponential backoff
      return Math.min(1000 * 2 ** attemptIndex, 30000);
    },
  });
}

// Retry with user feedback
function SaveButton() {
  const mutation = useMutation({
    mutationFn: saveData,
    retry: 2,
    retryDelay: 2000,
  });

  return (
    <div>
      <button
        onClick={() => mutation.mutate(data)}
        disabled={mutation.isPending}
      >
        {mutation.isPending ? 'Saving...' : 'Save'}
      </button>
      {mutation.failureCount > 0 && mutation.isPending && (
        <span>Retrying... (attempt {mutation.failureCount + 1})</span>
      )}
    </div>
  );
}

// Global mutation defaults
const queryClient = new QueryClient({
  defaultOptions: {
    mutations: {
      retry: (failureCount, error) => {
        // Global retry logic
        if (isNetworkError(error)) {
          return failureCount < 3;
        }
        return false;
      },
      retryDelay: (attemptIndex) => Math.min(1000 * 2 ** attemptIndex, 30000),
    },
  },
});

// Mutation with manual retry control
function useOptimisticUpdate() {
  const [retryCount, setRetryCount] = useState(0);

  const mutation = useMutation({
    mutationFn: updateData,
    retry: false, // Disable automatic retry
    onError: (error) => {
      if (retryCount < 3 && isRetryableError(error)) {
        setRetryCount((c) => c + 1);
        // User can manually retry
      }
    },
  });

  const handleRetry = () => {
    mutation.reset();
    mutation.mutate(lastVariables);
  };

  return { mutation, handleRetry, canRetry: retryCount < 3 };
}
```

## Why

1. **Data Integrity**: Non-idempotent mutations (POST) shouldn't retry to avoid duplicates.

2. **Error Differentiation**: Client errors (4xx) shouldn't retry; server/network errors might succeed on retry.

3. **Server Protection**: Exponential backoff prevents overwhelming servers during outages.

4. **User Experience**: Automatic retries for transient failures improve reliability without user intervention.

5. **Retry-After Respect**: Honoring server-provided retry timing shows good API citizenship.

6. **Feedback**: The `failureCount` property enables showing retry status to users.

Idempotency guidelines:
- **Safe to retry**: GET, PUT, DELETE, PATCH (usually)
- **Unsafe to retry**: POST (creates new resources)
- **Check your API**: Some POST endpoints are idempotent by design

Retry configuration options:
- `retry: number` - Fixed number of retries
- `retry: boolean` - Enable/disable (true = 3 retries)
- `retry: (failureCount, error) => boolean` - Conditional retry
- `retryDelay: number` - Fixed delay between retries (ms)
- `retryDelay: (attemptIndex, error) => number` - Dynamic delay
