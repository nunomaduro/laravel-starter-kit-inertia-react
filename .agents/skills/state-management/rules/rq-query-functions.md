---
title: Query Functions Best Practices
impact: CRITICAL
section: Query Fundamentals
tags: react-query, query-functions, error-handling
---

# Query Functions Best Practices

**Impact: CRITICAL**

Query functions are the data fetching logic passed to useQuery. They should be pure, handle errors properly, and return consistent data shapes.

## Bad Example

```tsx
// Anti-pattern: Inline query function with side effects
const { data } = useQuery({
  queryKey: ['user', userId],
  queryFn: async () => {
    // Side effect in query function
    analytics.track('user_fetched');

    const response = await fetch(`/api/users/${userId}`);
    // Not handling errors properly
    return response.json();
  },
});

// Anti-pattern: Query function that doesn't throw on error
const { data, isError } = useQuery({
  queryKey: ['posts'],
  queryFn: async () => {
    const response = await fetch('/api/posts');
    if (!response.ok) {
      // Returning error data instead of throwing
      return { error: true, message: 'Failed to fetch' };
    }
    return response.json();
  },
});

// Anti-pattern: Mutating external state in query function
let cache = {};
const { data } = useQuery({
  queryKey: ['data'],
  queryFn: async () => {
    const result = await fetchData();
    cache = result; // Mutating external state
    return result;
  },
});
```

## Good Example

```tsx
// Create reusable, testable query functions
async function fetchUser(userId: string): Promise<User> {
  const response = await fetch(`/api/users/${userId}`);

  if (!response.ok) {
    throw new Error(`Failed to fetch user: ${response.statusText}`);
  }

  return response.json();
}

async function fetchPosts(params: PostsParams): Promise<PostsResponse> {
  const searchParams = new URLSearchParams();
  if (params.page) searchParams.set('page', String(params.page));
  if (params.limit) searchParams.set('limit', String(params.limit));
  if (params.category) searchParams.set('category', params.category);

  const response = await fetch(`/api/posts?${searchParams}`);

  if (!response.ok) {
    const error = await response.json().catch(() => ({}));
    throw new ApiError(response.status, error.message || 'Failed to fetch posts');
  }

  return response.json();
}

// Custom error class for better error handling
class ApiError extends Error {
  constructor(public status: number, message: string) {
    super(message);
    this.name = 'ApiError';
  }
}

// Usage with clean separation
const { data: user, error } = useQuery({
  queryKey: userKeys.detail(userId),
  queryFn: () => fetchUser(userId),
});

// With query function context for cancellation
const { data: posts } = useQuery({
  queryKey: postKeys.list(params),
  queryFn: async ({ signal }) => {
    const response = await fetch(`/api/posts`, { signal });
    if (!response.ok) throw new Error('Failed to fetch');
    return response.json();
  },
});

// Handle side effects outside query function
const { data } = useQuery({
  queryKey: ['user', userId],
  queryFn: () => fetchUser(userId),
});

useEffect(() => {
  if (data) {
    analytics.track('user_fetched');
  }
}, [data]);
```

## Why

1. **Testability**: Extracted query functions can be unit tested independently of React components.

2. **Error Handling**: Throwing errors allows React Query to properly track error state and trigger retries.

3. **Separation of Concerns**: Query functions should only fetch data; side effects belong in useEffect or mutation callbacks.

4. **Cancellation Support**: Using the signal from query context enables proper request cancellation when queries are invalidated.

5. **Reusability**: Standalone query functions can be reused across multiple components and even in non-React contexts.

6. **Type Safety**: Explicitly typed return values ensure consistent data shapes and better TypeScript integration.

7. **Debugging**: Clear error messages and proper error classes make debugging network issues much easier.
