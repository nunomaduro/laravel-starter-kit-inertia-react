# State Management Patterns

**Version 1.1.0** | TanStack Query v5 | Zustand v5 | March 2026

> **Note:**
> This document is designed for AI agents and LLMs when implementing, refactoring,
> or generating state management code using TanStack Query v5 and Zustand v5.
> All patterns verified against v5 APIs. Optimized for automated workflows and consistent patterns.

## v5 Breaking Changes (Quick Reference)

**TanStack Query v5:**
- `cacheTime` → `gcTime`
- `keepPreviousData` option → `placeholderData: keepPreviousData` (imported helper)
- `isPreviousData` → `isPlaceholderData`
- `onSuccess`/`onError`/`onSettled` removed from `useQuery` — still valid on `useMutation`
- `suspense: true` on `useQuery` removed → use `useSuspenseQuery`

**Zustand v5:**
- `shallow` as 2nd arg removed → `useShallow` from `zustand/shallow`
- Selectors returning new object/array references need `useShallow` to avoid infinite loops

## Security: Persist Middleware

> **Never persist auth tokens, passwords, or secrets to localStorage/sessionStorage.**
> These are accessible to any JavaScript — XSS fully exposes them.
> Use `partialize` to include only non-sensitive state. Manage tokens via HttpOnly cookies server-side.

---

## Abstract

Comprehensive guide for React Query (TanStack Query) and Zustand state management patterns, designed for AI agents and LLMs. Contains 26+ rules across 6 categories, prioritized by impact from critical (query fundamentals, mutations, stores) to medium (advanced patterns, caching strategies). Each rule includes detailed explanations with bad vs. good code examples, TypeScript patterns, and specific use cases. Covers server state with React Query (data fetching, caching, mutations, optimistic updates) and client state with Zustand (stores, persistence, selectors). Optimized for automated refactoring and state management best practices.

---

## Table of Contents

1. [Query Fundamentals](#1-query-fundamentals) — **CRITICAL**
   - 1.1 [useQuery Hook Patterns](rules/rq-usequery.md)
   - 1.2 [Query Keys Best Practices](rules/rq-query-keys.md)
   - 1.3 [Query Functions Best Practices](rules/rq-query-functions.md)
   - 1.4 [Query Conditional Execution](rules/rq-enabled-option.md)
   - 1.5 [Query Data Transformation](rules/rq-select-transform.md)

2. [Mutation & Updates](#2-mutation--updates) — **CRITICAL**
   - 2.1 [Mutation Setup Best Practices](rules/rq-mutation-setup.md)
   - 2.2 [Mutation Callbacks](rules/rq-mutation-callbacks.md)
   - 2.3 [Optimistic Updates](rules/rq-optimistic-updates.md)
   - 2.4 [Mutation Variables Pattern](rules/rq-mutation-variables.md)
   - 2.5 [Mutation Side Effects](rules/rq-mutation-side-effects.md)

3. [Zustand Stores](#3-zustand-stores) — **CRITICAL**
   - 3.1 [Creating Zustand Stores](rules/zs-create-store.md)
   - 3.2 [Persist Middleware](rules/zs-persist.md)

4. [Advanced Queries](#4-advanced-queries) — **HIGH**
   - 4.1 [Infinite Queries Pattern](rules/rq-infinite-queries.md)
   - 4.2 [Paginated Queries](rules/rq-paginated-queries.md)
   - 4.3 [Dependent Queries Pattern](rules/rq-dependent-queries.md)
   - 4.4 [Parallel Queries Pattern](rules/rq-parallel-queries.md)
   - 4.5 [Query Cancellation](rules/rq-query-cancellation.md)
   - 4.6 [Suspense Mode Integration](rules/rq-suspense-mode.md)

5. [Cache & Performance](#5-cache--performance) — **HIGH-MEDIUM**
   - 5.1 [Stale Time Configuration](rules/rq-stale-time.md)
   - 5.2 [Cache Time Configuration](rules/rq-cache-time.md)
   - 5.3 [Query Invalidation](rules/rq-query-invalidation.md)
   - 5.4 [Data Prefetching](rules/rq-prefetching.md)
   - 5.5 [Retry Logic Configuration](rules/rq-retry-logic.md)
   - 5.6 [Placeholder Data Pattern](rules/rq-placeholder-data.md)
   - 5.7 [Initial Data Configuration](rules/rq-initial-data.md)
   - 5.8 [Refetch Configuration](rules/rq-refetch-options.md)

6. [DevTools & Patterns](#6-devtools--patterns) — **MEDIUM**
   - 6.1 React Query DevTools
   - 6.2 Zustand DevTools
   - 6.3 Testing Strategies
   - 6.4 Common Pitfalls

---

## 1. Query Fundamentals

**Impact: CRITICAL**

Query fundamentals are essential for any React Query implementation. These patterns form the foundation of server state management, covering data fetching, caching, and type safety.

### Key Principles

- **Query Keys**: Use hierarchical, serializable arrays for cache organization
- **Query Functions**: Pure functions that throw on errors
- **Conditional Execution**: Use `enabled` option for dependent logic
- **Data Transformation**: Use `select` for derived data
- **Type Safety**: Leverage TypeScript for query responses

### When to Apply

- Starting any new React Query implementation
- Refactoring existing data fetching code
- Setting up QueryClient configuration
- Creating query key factories

### Example: Basic Setup

```tsx
// Query key factory
export const userKeys = {
  all: ['users'] as const,
  lists: () => [...userKeys.all, 'list'] as const,
  list: (filters: UserFilters) => [...userKeys.lists(), filters] as const,
  details: () => [...userKeys.all, 'detail'] as const,
  detail: (id: string) => [...userKeys.details(), id] as const,
}

// Query function
async function fetchUser(id: string): Promise<User> {
  const response = await fetch(`/api/users/${id}`)
  if (!response.ok) throw new Error('Failed to fetch user')
  return response.json()
}

// Usage
function UserProfile({ userId }: { userId: string }) {
  const { data: user, isLoading, error } = useQuery({
    queryKey: userKeys.detail(userId),
    queryFn: () => fetchUser(userId),
    staleTime: 5 * 60 * 1000,
  })

  if (isLoading) return <Spinner />
  if (error) return <Error message={error.message} />
  if (!user) return null

  return <div>{user.name}</div>
}
```

---

## 2. Mutation & Updates

**Impact: CRITICAL**

Mutations handle all write operations (create, update, delete). Proper mutation patterns ensure data consistency, instant user feedback, and graceful error handling.

### Key Principles

- **Error Handling**: Always handle onError with user feedback
- **Cache Updates**: Invalidate or update cache after mutations
- **Optimistic Updates**: Provide instant feedback with rollback
- **Type Safety**: Type mutation variables and responses
- **Callbacks**: Use onSuccess, onError, onSettled appropriately

### When to Apply

- Implementing create/update/delete operations
- Adding user feedback for actions
- Optimizing perceived performance
- Ensuring data consistency

### Example: Complete Mutation

```tsx
function useUpdateUser() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (user: UpdateUserInput) => updateUserApi(user),

    onMutate: async (updatedUser) => {
      // Cancel outgoing queries
      await queryClient.cancelQueries({ queryKey: userKeys.detail(updatedUser.id) })

      // Snapshot for rollback
      const previousUser = queryClient.getQueryData(userKeys.detail(updatedUser.id))

      // Optimistic update
      queryClient.setQueryData(userKeys.detail(updatedUser.id), updatedUser)

      return { previousUser }
    },

    onError: (error, variables, context) => {
      // Rollback
      if (context?.previousUser) {
        queryClient.setQueryData(userKeys.detail(variables.id), context.previousUser)
      }
      toast.error('Failed to update user')
    },

    onSuccess: (data) => {
      toast.success('User updated successfully')
    },

    onSettled: (data, error, variables) => {
      // Refetch to ensure server state
      queryClient.invalidateQueries({ queryKey: userKeys.detail(variables.id) })
    },
  })
}
```

---

## 3. Zustand Stores

**Impact: CRITICAL**

Zustand provides lightweight, performant client-side state management. Use it for UI state, user preferences, and any local-first data that doesn't come from the server.

### Key Principles

- **Minimal Boilerplate**: Simple create() function
- **TypeScript First**: Explicit typing for state and actions
- **Selectors**: Use selectors to prevent unnecessary re-renders
- **Middleware**: Use persist, devtools for enhanced functionality
- **Separation**: Server state in React Query, client state in Zustand

### When to Apply

- Managing UI state (modals, sidebars, selected items)
- User preferences and settings
- Form state across multiple steps
- Any client-only state

### Example: Complete Store

```tsx
interface TodoStore {
  todos: Todo[]
  filter: 'all' | 'active' | 'completed'

  addTodo: (text: string) => void
  toggleTodo: (id: string) => void
  removeTodo: (id: string) => void
  setFilter: (filter: TodoStore['filter']) => void
}

export const useTodoStore = create<TodoStore>()(
  devtools(
    persist(
      (set) => ({
        todos: [],
        filter: 'all',

        addTodo: (text) => set((state) => ({
          todos: [...state.todos, {
            id: crypto.randomUUID(),
            text,
            completed: false,
          }],
        })),

        toggleTodo: (id) => set((state) => ({
          todos: state.todos.map((todo) =>
            todo.id === id ? { ...todo, completed: !todo.completed } : todo
          ),
        })),

        removeTodo: (id) => set((state) => ({
          todos: state.todos.filter((todo) => todo.id !== id),
        })),

        setFilter: (filter) => set({ filter }),
      }),
      { name: 'todo-storage' }
    )
  )
)

// Usage with selectors
function TodoList() {
  const todos = useTodoStore((state) => state.todos)
  const filter = useTodoStore((state) => state.filter)
  const toggleTodo = useTodoStore((state) => state.toggleTodo)

  const filteredTodos = useMemo(() => {
    switch (filter) {
      case 'active': return todos.filter(t => !t.completed)
      case 'completed': return todos.filter(t => t.completed)
      default: return todos
    }
  }, [todos, filter])

  return (
    <ul>
      {filteredTodos.map((todo) => (
        <li key={todo.id} onClick={() => toggleTodo(todo.id)}>
          {todo.text}
        </li>
      ))}
    </ul>
  )
}
```

---

## 4. Advanced Queries

**Impact: HIGH**

Advanced query patterns enable sophisticated UX like infinite scroll, pagination, complex data relationships, and optimized parallel loading.

### Key Principles

- **Infinite Queries**: Use useInfiniteQuery for load-more patterns
- **Pagination**: Track page state explicitly
- **Dependencies**: Use enabled for sequential queries
- **Parallelism**: Fetch independent data simultaneously
- **Cancellation**: Clean up abandoned requests

### When to Apply

- Implementing infinite scroll or load more
- Building paginated tables or lists
- Fetching related data sequentially
- Optimizing initial page load with parallel queries

### Example: Infinite Query

```tsx
function useInfinitePosts(category: string) {
  return useInfiniteQuery({
    queryKey: ['posts', 'infinite', category],
    queryFn: async ({ pageParam }): Promise<PostsPage> => {
      const response = await fetch(
        `/api/posts?category=${category}&cursor=${pageParam}`
      )
      if (!response.ok) throw new Error('Failed to fetch')
      return response.json()
    },
    initialPageParam: '',
    getNextPageParam: (lastPage) => lastPage.nextCursor ?? undefined,
  })
}

function PostList({ category }: { category: string }) {
  const { data, fetchNextPage, hasNextPage, isFetchingNextPage } =
    useInfinitePosts(category)

  const allPosts = data?.pages.flatMap(page => page.posts) ?? []

  return (
    <div>
      {allPosts.map(post => <PostCard key={post.id} post={post} />)}

      {hasNextPage && (
        <button
          onClick={() => fetchNextPage()}
          disabled={isFetchingNextPage}
        >
          {isFetchingNextPage ? 'Loading...' : 'Load More'}
        </button>
      )}
    </div>
  )
}
```

---

## 5. Cache & Performance

**Impact: HIGH-MEDIUM**

Caching strategies have high impact on perceived performance and server load. Proper configuration reduces network requests while ensuring data freshness.

### Key Principles

- **staleTime**: Configure based on data volatility
- **gcTime**: Keep data in cache longer than staleTime
- **Invalidation**: Invalidate on mutations and user actions
- **Prefetching**: Anticipate user navigation
- **Retry**: Configure based on operation criticality

### When to Apply

- Optimizing existing applications
- Reducing server load
- Improving perceived performance
- Handling unreliable networks

### Example: Caching Strategy

```tsx
// Global configuration
const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 60 * 1000, // 1 minute default
      gcTime: 5 * 60 * 1000, // 5 minutes
      retry: 1,
      refetchOnWindowFocus: false,
    },
  },
})

// Per-query overrides
const staticQuery = {
  staleTime: Infinity,
  gcTime: Infinity,
}

const realtimeQuery = {
  staleTime: 0,
  refetchInterval: 30 * 1000,
}

// Usage
const { data: countries } = useQuery({
  queryKey: ['countries'],
  queryFn: fetchCountries,
  ...staticQuery,
})

const { data: notifications } = useQuery({
  queryKey: ['notifications'],
  queryFn: fetchNotifications,
  ...realtimeQuery,
})
```

---

## 6. DevTools & Patterns

**Impact: MEDIUM**

DevTools and debugging patterns help identify performance issues and streamline development workflows.

### Key Principles

- **React Query DevTools**: Visual cache inspection
- **Zustand DevTools**: Redux DevTools integration
- **Testing**: Mock query client for tests
- **Error Boundaries**: Catch query errors
- **Suspense**: React 18+ integration

### When to Apply

- Debugging cache issues
- Understanding query behavior
- Setting up testing infrastructure
- Implementing error handling

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
