---
name: state-management
description: React Query and Zustand patterns for state management. Use when implementing data fetching, caching, mutations, or client-side state. Triggers on tasks involving useQuery, useMutation, Zustand stores, caching, or state management.
license: MIT
metadata:
  author: agent-skills
  version: "1.1.0"
---

# State Management with React Query + Zustand

**Version 1.1.0** | TanStack Query v5 | Zustand v5 | March 2026

> **Note:**
> This document provides comprehensive patterns for AI agents and LLMs working with
> TanStack Query v5 and Zustand v5. All examples are verified against v5 APIs.
> Optimized for automated refactoring, code generation, and state management best practices.

## v5 Breaking Changes (Quick Reference)

**TanStack Query v5:**
- `cacheTime` → `gcTime`
- `keepPreviousData` option → `placeholderData: keepPreviousData` (imported helper)
- `isPreviousData` → `isPlaceholderData`
- `onSuccess`/`onError`/`onSettled` removed from `useQuery` — still valid on `useMutation`
- `suspense: true` on `useQuery` removed → use `useSuspenseQuery`

**Zustand v5:**
- `shallow` as 2nd arg removed → `useShallow` from `zustand/shallow`
- Selectors returning new references need `useShallow` to avoid infinite loops

## Security: Persist Middleware

> **Never persist auth tokens, passwords, or secrets to localStorage/sessionStorage.**
> Use `partialize` to persist only non-sensitive state. Manage tokens via HttpOnly cookies.

---

Comprehensive patterns for server state (React Query) and client state (Zustand). Contains 26+ rules for efficient data fetching and state management.

## When to Apply

Reference these guidelines when:
- Fetching data from APIs
- Managing server state and caching
- Handling mutations and optimistic updates
- Creating client-side stores
- Combining React Query with Zustand

## Rule Categories by Priority

| Priority | Category | Impact | Prefix |
|----------|----------|--------|--------|
| 1 | React Query Basics | CRITICAL | `rq-` |
| 2 | Zustand Store Patterns | CRITICAL | `zs-` |
| 3 | Caching & Invalidation | HIGH | `cache-` |
| 4 | Mutations & Updates | HIGH | `mut-` |
| 5 | Optimistic Updates | MEDIUM | `opt-` |
| 6 | DevTools & Debugging | MEDIUM | `dev-` |
| 7 | Advanced Patterns | LOW | `adv-` |

## Quick Reference

### 1. React Query Basics (CRITICAL)

- `rq-setup` - QueryClient and Provider setup
- `rq-usequery` - Basic useQuery patterns
- `rq-querykeys` - Query key organization
- `rq-loading-error` - Handle loading and error states
- `rq-enabled` - Conditional queries

### 2. Zustand Store Patterns (CRITICAL)

- `zs-create-store` - Create basic store
- `zs-typescript` - TypeScript store patterns
- `zs-selectors` - Efficient selectors
- `zs-actions` - Action patterns
- `zs-persist` - Persist state to storage

### 3. Caching & Invalidation (HIGH)

- `cache-stale-time` - Configure stale time
- `cache-gc-time` - Configure garbage collection
- `cache-invalidation` - Invalidate queries
- `cache-prefetch` - Prefetch data
- `cache-initial-data` - Set initial data

### 4. Mutations & Updates (HIGH)

- `mut-usemutation` - Basic useMutation
- `mut-callbacks` - onSuccess, onError callbacks
- `mut-invalidate` - Invalidate after mutation
- `mut-update-cache` - Direct cache updates

### 5. Optimistic Updates (MEDIUM)

- `opt-basic` - Basic optimistic updates
- `opt-rollback` - Rollback on error
- `opt-variables` - Use mutation variables

### 6. DevTools & Debugging (MEDIUM)

- `dev-react-query` - React Query DevTools
- `dev-zustand` - Zustand DevTools
- `dev-debugging` - Debug strategies

### 7. Advanced Patterns (LOW)

- `adv-infinite-queries` - Infinite scrolling
- `adv-parallel-queries` - Parallel requests
- `adv-dependent-queries` - Dependent queries
- `adv-query-zustand` - Combine RQ with Zustand

## React Query Patterns

### Setup

```tsx
// lib/queryClient.ts
import { QueryClient } from '@tanstack/react-query'

export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 1000 * 60 * 5, // 5 minutes
      gcTime: 1000 * 60 * 30,   // 30 minutes (formerly cacheTime)
      retry: 1,
      refetchOnWindowFocus: false,
    },
  },
})

// App.tsx
import { QueryClientProvider } from '@tanstack/react-query'
import { ReactQueryDevtools } from '@tanstack/react-query-devtools'
import { queryClient } from './lib/queryClient'

function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <Router />
      <ReactQueryDevtools initialIsOpen={false} />
    </QueryClientProvider>
  )
}
```

### Query Keys Factory

```tsx
// lib/queryKeys.ts
export const queryKeys = {
  // All posts
  posts: {
    all: ['posts'] as const,
    lists: () => [...queryKeys.posts.all, 'list'] as const,
    list: (filters: PostFilters) =>
      [...queryKeys.posts.lists(), filters] as const,
    details: () => [...queryKeys.posts.all, 'detail'] as const,
    detail: (id: number) => [...queryKeys.posts.details(), id] as const,
  },

  // All users
  users: {
    all: ['users'] as const,
    detail: (id: number) => [...queryKeys.users.all, id] as const,
    posts: (userId: number) => [...queryKeys.users.all, userId, 'posts'] as const,
  },
}
```

### useQuery Hook

```tsx
// hooks/usePosts.ts
import { useQuery } from '@tanstack/react-query'
import { queryKeys } from '@/lib/queryKeys'
import { fetchPosts, fetchPost } from '@/api/posts'

export function usePosts(filters?: PostFilters) {
  return useQuery({
    queryKey: queryKeys.posts.list(filters ?? {}),
    queryFn: () => fetchPosts(filters),
  })
}

export function usePost(id: number) {
  return useQuery({
    queryKey: queryKeys.posts.detail(id),
    queryFn: () => fetchPost(id),
    enabled: !!id, // Only run if id exists
  })
}

// Usage in component
function PostList() {
  const { data: posts, isLoading, error } = usePosts()

  if (isLoading) return <Spinner />
  if (error) return <Error message={error.message} />

  return (
    <ul>
      {posts?.map((post) => (
        <li key={post.id}>{post.title}</li>
      ))}
    </ul>
  )
}
```

### useMutation Hook

```tsx
// hooks/useCreatePost.ts
import { useMutation, useQueryClient } from '@tanstack/react-query'
import { queryKeys } from '@/lib/queryKeys'
import { createPost } from '@/api/posts'

export function useCreatePost() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: createPost,
    onSuccess: (newPost) => {
      // Invalidate and refetch posts list
      queryClient.invalidateQueries({
        queryKey: queryKeys.posts.lists(),
      })
    },
    onError: (error) => {
      console.error('Failed to create post:', error)
    },
  })
}

// Usage
function CreatePostForm() {
  const { mutate, isPending } = useCreatePost()

  const handleSubmit = (data: CreatePostData) => {
    mutate(data)
  }

  return (
    <form onSubmit={handleSubmit}>
      {/* form fields */}
      <button disabled={isPending}>
        {isPending ? 'Creating...' : 'Create'}
      </button>
    </form>
  )
}
```

### Optimistic Updates

```tsx
export function useUpdatePost() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: updatePost,
    onMutate: async (updatedPost) => {
      // Cancel outgoing refetches
      await queryClient.cancelQueries({
        queryKey: queryKeys.posts.detail(updatedPost.id),
      })

      // Snapshot previous value
      const previousPost = queryClient.getQueryData(
        queryKeys.posts.detail(updatedPost.id)
      )

      // Optimistically update
      queryClient.setQueryData(
        queryKeys.posts.detail(updatedPost.id),
        updatedPost
      )

      return { previousPost }
    },
    onError: (err, updatedPost, context) => {
      // Rollback on error
      queryClient.setQueryData(
        queryKeys.posts.detail(updatedPost.id),
        context?.previousPost
      )
    },
    onSettled: (data, error, variables) => {
      // Refetch after settle
      queryClient.invalidateQueries({
        queryKey: queryKeys.posts.detail(variables.id),
      })
    },
  })
}
```

## Zustand Patterns

### Basic Store

```tsx
// stores/useCounterStore.ts
import { create } from 'zustand'

interface CounterState {
  count: number
  increment: () => void
  decrement: () => void
  reset: () => void
}

export const useCounterStore = create<CounterState>((set) => ({
  count: 0,
  increment: () => set((state) => ({ count: state.count + 1 })),
  decrement: () => set((state) => ({ count: state.count - 1 })),
  reset: () => set({ count: 0 }),
}))

// Usage
function Counter() {
  const { count, increment, decrement } = useCounterStore()

  return (
    <div>
      <span>{count}</span>
      <button onClick={increment}>+</button>
      <button onClick={decrement}>-</button>
    </div>
  )
}
```

### Store with TypeScript and Middleware

```tsx
// stores/useAuthStore.ts
import { create } from 'zustand'
import { persist, devtools } from 'zustand/middleware'

interface User {
  id: number
  name: string
  email: string
}

interface AuthState {
  user: User | null
  isAuthenticated: boolean
  login: (user: User) => void
  logout: () => void
}

// ✅ Never persist tokens to localStorage — use HttpOnly cookies server-side
export const useAuthStore = create<AuthState>()(
  devtools(
    persist(
      (set) => ({
        user: null,
        isAuthenticated: false,

        login: (user) =>
          set({
            user,
            isAuthenticated: true,
          }),

        logout: () =>
          set({
            user: null,
            isAuthenticated: false,
          }),
      }),
      {
        name: 'auth-storage',
        // Only persist display info and auth flag — tokens must NOT be included
        partialize: (state) => ({
          user: state.user,
          isAuthenticated: state.isAuthenticated,
        }),
      }
    )
  )
)
```

### Selectors for Performance

```tsx
// Use selectors to prevent unnecessary re-renders
function UserName() {
  // Only re-renders when user.name changes
  const name = useAuthStore((state) => state.user?.name)
  return <span>{name}</span>
}

// Multiple selectors
function UserInfo() {
  const user = useAuthStore((state) => state.user)
  const isAuthenticated = useAuthStore((state) => state.isAuthenticated)

  if (!isAuthenticated) return <LoginButton />
  return <span>{user?.name}</span>
}
```

### Combining React Query + Zustand

```tsx
// Server state: React Query (what comes from API)
const { data: posts } = usePosts()

// Client state: Zustand (UI state)
const { selectedPostId, selectPost } = useUIStore()

// Use together
const selectedPost = posts?.find((p) => p.id === selectedPostId)
```

## How to Use

Read individual rule files for detailed explanations and code examples:

```
rules/rq-usequery.md
rules/rq-query-keys.md
rules/rq-mutation-setup.md
rules/rq-optimistic-updates.md
rules/zs-create-store.md
rules/zs-persist.md
rules/rq-query-invalidation.md
rules/rq-prefetching.md
```

---

## References

### React Query (TanStack Query)
1. [TanStack Query Documentation](https://tanstack.com/query/latest)
2. [React Query Overview](https://tanstack.com/query/latest/docs/react/overview)
3. [Queries Guide](https://tanstack.com/query/latest/docs/react/guides/queries)
4. [Mutations Guide](https://tanstack.com/query/latest/docs/react/guides/mutations)
5. [Query Keys Guide](https://tanstack.com/query/latest/docs/react/guides/query-keys)
6. [Optimistic Updates](https://tanstack.com/query/latest/docs/react/guides/optimistic-updates)
7. [Infinite Queries](https://tanstack.com/query/latest/docs/react/guides/infinite-queries)
8. [Paginated Queries](https://tanstack.com/query/latest/docs/react/guides/paginated-queries)
9. [React Query DevTools](https://tanstack.com/query/latest/docs/react/devtools)

### Zustand
1. [Zustand Demo](https://zustand-demo.pmnd.rs)
2. [Zustand GitHub](https://github.com/pmndrs/zustand)
3. [Getting Started](https://docs.pmnd.rs/zustand/getting-started/introduction)
4. [TypeScript Guide](https://docs.pmnd.rs/zustand/guides/typescript)
5. [Persisting Store Data](https://docs.pmnd.rs/zustand/integrations/persisting-store-data)
6. [Zustand Recipes](https://github.com/pmndrs/zustand#recipes)

---

## License

This skill is provided as-is for educational and development purposes. React Query is MIT licensed by TanStack. Zustand is MIT licensed by Poimandres (pmnd.rs).
