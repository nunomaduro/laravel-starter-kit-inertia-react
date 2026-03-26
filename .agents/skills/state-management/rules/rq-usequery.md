---
title: useQuery Hook Patterns
impact: CRITICAL
section: Query Fundamentals
tags: react-query, hooks, data-fetching
---

# useQuery Hook Patterns

**Impact: CRITICAL**

## Why It Matters

useQuery is the primary hook for fetching data. Proper usage ensures efficient caching, automatic refetching, and proper loading/error states.

## Basic Usage

```tsx
import { useQuery } from '@tanstack/react-query'

function Posts() {
  const {
    data,           // The fetched data
    isLoading,      // First load, no data yet
    isFetching,     // Any fetch, including background
    isError,        // Fetch failed
    error,          // Error object
    isSuccess,      // Fetch succeeded
    refetch,        // Manually refetch
  } = useQuery({
    queryKey: ['posts'],
    queryFn: fetchPosts,
  })

  if (isLoading) return <Spinner />
  if (isError) return <Error message={error.message} />

  return (
    <ul>
      {data?.map((post) => (
        <li key={post.id}>{post.title}</li>
      ))}
    </ul>
  )
}
```

## Query Function

```tsx
// Simple fetch
const queryFn = async () => {
  const response = await fetch('/api/posts')
  if (!response.ok) {
    throw new Error('Network response was not ok')
  }
  return response.json()
}

// With axios
const queryFn = async () => {
  const { data } = await axios.get('/api/posts')
  return data
}

// With parameters (from query key)
const queryFn = async ({ queryKey }) => {
  const [_key, postId] = queryKey
  const { data } = await axios.get(`/api/posts/${postId}`)
  return data
}
```

## Query Key Best Practices

```tsx
// ✅ Array with unique identifiers
queryKey: ['posts']
queryKey: ['posts', 'list']
queryKey: ['posts', 'detail', postId]
queryKey: ['posts', 'list', { status: 'published', page: 1 }]

// ✅ Query key factory pattern
export const postKeys = {
  all: ['posts'] as const,
  lists: () => [...postKeys.all, 'list'] as const,
  list: (filters: Filters) => [...postKeys.lists(), filters] as const,
  details: () => [...postKeys.all, 'detail'] as const,
  detail: (id: number) => [...postKeys.details(), id] as const,
}

// Usage
useQuery({
  queryKey: postKeys.detail(5),
  queryFn: () => fetchPost(5),
})
```

## Configuration Options

```tsx
useQuery({
  queryKey: ['posts'],
  queryFn: fetchPosts,

  // Caching
  staleTime: 1000 * 60 * 5,    // Data fresh for 5 minutes
  gcTime: 1000 * 60 * 30,      // Keep in cache for 30 minutes

  // Refetching
  refetchOnWindowFocus: true,   // Refetch when window regains focus
  refetchOnMount: true,         // Refetch when component mounts
  refetchOnReconnect: true,     // Refetch when network reconnects
  refetchInterval: 1000 * 60,   // Poll every minute

  // Retry
  retry: 3,                     // Retry failed requests 3 times
  retryDelay: (attempt) => Math.min(1000 * 2 ** attempt, 30000),

  // Initial data
  initialData: [],              // Data before first fetch
  placeholderData: previousData, // Show while fetching

  // Conditional
  enabled: !!userId,            // Only fetch if userId exists
})
```

## Handling States

```tsx
function Posts() {
  const { data, status, fetchStatus } = useQuery({
    queryKey: ['posts'],
    queryFn: fetchPosts,
  })

  // status: 'pending' | 'error' | 'success'
  // fetchStatus: 'fetching' | 'paused' | 'idle'

  // First load (no cached data)
  if (status === 'pending') {
    return <Skeleton />
  }

  // Error state
  if (status === 'error') {
    return <ErrorMessage />
  }

  // Success with potential background refresh
  return (
    <>
      {fetchStatus === 'fetching' && <RefreshIndicator />}
      <PostList posts={data} />
    </>
  )
}
```

## TypeScript

```tsx
interface Post {
  id: number
  title: string
  body: string
}

// Type the response
const { data } = useQuery<Post[]>({
  queryKey: ['posts'],
  queryFn: fetchPosts,
})

// Type error as well
const { data, error } = useQuery<Post[], Error>({
  queryKey: ['posts'],
  queryFn: fetchPosts,
})

// With custom error type
interface ApiError {
  message: string
  code: number
}

const { data, error } = useQuery<Post[], ApiError>({
  queryKey: ['posts'],
  queryFn: fetchPosts,
})
```

## Custom Hook Pattern

```tsx
// hooks/usePosts.ts
export function usePosts(filters?: PostFilters) {
  return useQuery({
    queryKey: postKeys.list(filters ?? {}),
    queryFn: () => fetchPosts(filters),
    staleTime: 1000 * 60 * 5,
  })
}

export function usePost(id: number) {
  return useQuery({
    queryKey: postKeys.detail(id),
    queryFn: () => fetchPost(id),
    enabled: !!id,
  })
}

// Usage
function PostPage({ id }: { id: number }) {
  const { data: post, isLoading } = usePost(id)
  // ...
}
```

## Dependent Queries

```tsx
// Fetch user first, then their posts
function UserPosts({ userId }: { userId: number }) {
  const { data: user } = useQuery({
    queryKey: ['user', userId],
    queryFn: () => fetchUser(userId),
  })

  const { data: posts } = useQuery({
    queryKey: ['posts', { authorId: user?.id }],
    queryFn: () => fetchPostsByAuthor(user!.id),
    enabled: !!user?.id,  // Only run after user is loaded
  })

  // ...
}
```

## Impact

- Automatic caching and deduplication
- Background refetching
- Proper loading/error states
- Optimized performance
