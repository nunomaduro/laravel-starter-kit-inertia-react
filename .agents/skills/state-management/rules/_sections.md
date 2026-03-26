# Rule Sections

## Priority Levels

| Level | Description | When to Apply |
|-------|-------------|---------------|
| CRITICAL | Essential for production apps | Always |
| HIGH | Significant performance impact | Most projects |
| MEDIUM | Noticeable improvements | When optimizing |
| LOW | Minor optimizations | Large-scale apps |

## Section Overview

### Query Fundamentals (CRITICAL)
Rules for basic React Query patterns. These are essential for any data fetching implementation, covering useQuery hooks, query keys, query functions, and conditional execution.

**Impact:** Critical foundation for server state management. Proper implementation prevents cache collisions, enables automatic refetching, and ensures type safety.

**Key concepts:**
- useQuery hook patterns and configuration
- Query key factory patterns
- Query function best practices
- Conditional query execution with `enabled`
- Data transformation with `select`

### Mutation & Updates (CRITICAL)
Rules for creating, updating, and deleting data with React Query mutations. Covers setup, callbacks, optimistic updates, and cache invalidation strategies.

**Impact:** Essential for write operations. Proper mutation handling ensures data consistency, provides instant user feedback, and handles errors gracefully.

**Key concepts:**
- useMutation setup and configuration
- Mutation callbacks (onSuccess, onError, onSettled)
- Optimistic updates with rollback
- Mutation variables and context
- Side effects and cache updates

### Zustand Stores (CRITICAL)
Rules for client-side state management with Zustand. Covers store creation, TypeScript patterns, middleware, and integration with React Query.

**Impact:** Critical for managing UI state, user preferences, and local-first data. Zustand provides lightweight, performant state management without boilerplate.

**Key concepts:**
- Store creation and TypeScript patterns
- Selectors for performance optimization
- Persist middleware for localStorage
- DevTools integration
- Combining with React Query for hybrid state management

### Advanced Queries (HIGH)
Rules for complex query patterns including infinite scrolling, pagination, dependent queries, and parallel fetching.

**Impact:** High value for data-heavy applications. These patterns enable sophisticated UX like infinite scroll, complex data relationships, and optimized parallel loading.

**Key concepts:**
- Infinite queries for load-more patterns
- Paginated queries with page management
- Dependent queries (sequential data fetching)
- Parallel queries for independent data
- Query cancellation and cleanup

### Cache & Performance (HIGH-MEDIUM)
Rules for optimizing caching behavior, prefetching data, and configuring staleness. Covers staleTime, gcTime, invalidation, and retry logic.

**Impact:** High impact on perceived performance and server load. Proper caching reduces network requests while ensuring data freshness.

**Key concepts:**
- staleTime configuration for freshness
- gcTime (cache time) for memory management
- Query invalidation strategies
- Prefetching for anticipated navigation
- Retry logic and error recovery
- Placeholder and initial data patterns
- Refetch configuration

### DevTools & Patterns (MEDIUM)
Rules for debugging, testing, and advanced patterns. Covers React Query DevTools, Zustand DevTools, Suspense integration, and best practices.

**Impact:** Medium impact on developer experience and debugging. These tools and patterns help identify performance issues and streamline development.

**Key concepts:**
- React Query DevTools usage
- Zustand DevTools integration
- Suspense mode for React 18+
- Testing strategies
- Common pitfalls and solutions

## Section Relationships

```
Query Fundamentals → Everything else
    ↓
Mutation & Updates → Cache & Performance
    ↓
Advanced Queries → Cache & Performance
    ↓
Zustand Stores ← → All Query Patterns
    ↓
DevTools & Patterns (observes all)
```

## When to Apply Each Section

### Starting a new project
1. **Query Fundamentals** - Set up QueryClient and basic patterns
2. **Zustand Stores** - Create stores for UI state
3. **Mutation & Updates** - Implement write operations
4. **Cache & Performance** - Configure caching strategies

### Optimizing existing app
1. **Cache & Performance** - Audit staleTime/gcTime settings
2. **Advanced Queries** - Replace manual pagination with useInfiniteQuery
3. **Mutation & Updates** - Add optimistic updates for better UX
4. **DevTools & Patterns** - Use DevTools to identify issues

### Scaling up
1. **Advanced Queries** - Implement dependent and parallel queries
2. **Cache & Performance** - Add prefetching for common paths
3. **Zustand Stores** - Extract complex component state to stores
4. **DevTools & Patterns** - Implement comprehensive testing
