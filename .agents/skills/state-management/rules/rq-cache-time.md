---
title: Cache Time Configuration
impact: HIGH
section: Cache & Performance
tags: react-query, caching, gc-time
---

# Cache Time (gcTime) Configuration

**Impact: HIGH**

Cache time (renamed to gcTime in v5) determines how long inactive query data remains in memory before garbage collection. This is different from staleTime.

## Bad Example

```tsx
// Anti-pattern: Confusing gcTime with staleTime
const { data } = useQuery({
  queryKey: ['user', userId],
  queryFn: () => fetchUser(userId),
  gcTime: 0, // Data immediately removed when component unmounts
  // This causes refetch every time component remounts
});

// Anti-pattern: Infinite gcTime for large datasets
const { data: allProducts } = useQuery({
  queryKey: ['products', 'all'],
  queryFn: fetchAllProducts, // Returns thousands of items
  gcTime: Infinity, // Memory leak potential
});

// Anti-pattern: Short gcTime with long staleTime
const { data } = useQuery({
  queryKey: ['settings'],
  queryFn: fetchSettings,
  staleTime: 60 * 60 * 1000, // 1 hour
  gcTime: 5 * 60 * 1000, // 5 minutes - data removed before it goes stale
});
```

## Good Example

```tsx
// gcTime should generally be >= staleTime
const { data: user } = useQuery({
  queryKey: userKeys.detail(userId),
  queryFn: () => fetchUser(userId),
  staleTime: 5 * 60 * 1000, // Fresh for 5 minutes
  gcTime: 30 * 60 * 1000, // Keep in cache for 30 minutes
});

// Static data can have infinite cache time
const { data: countries } = useQuery({
  queryKey: ['countries'],
  queryFn: fetchCountries,
  staleTime: Infinity,
  gcTime: Infinity, // Never garbage collect
});

// Large datasets with reasonable limits
const { data: products } = useQuery({
  queryKey: ['products', { page, filters }],
  queryFn: () => fetchProducts({ page, filters }),
  staleTime: 2 * 60 * 1000, // 2 minutes
  gcTime: 10 * 60 * 1000, // 10 minutes - reasonable for paginated data
});

// Configure sensible defaults
const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 60 * 1000, // 1 minute
      gcTime: 5 * 60 * 1000, // 5 minutes (default)
    },
  },
});

// Different cache strategies for different data types
const cacheStrategies = {
  // Reference data - keep forever
  static: {
    staleTime: Infinity,
    gcTime: Infinity,
  },
  // User-specific data - moderate caching
  user: {
    staleTime: 5 * 60 * 1000,
    gcTime: 30 * 60 * 1000,
  },
  // List data - shorter caching
  list: {
    staleTime: 60 * 1000,
    gcTime: 5 * 60 * 1000,
  },
  // Real-time data - minimal caching
  realtime: {
    staleTime: 0,
    gcTime: 60 * 1000,
  },
};

// Usage with strategies
const { data } = useQuery({
  queryKey: ['users', 'list'],
  queryFn: fetchUsers,
  ...cacheStrategies.list,
});
```

## Why

1. **Memory Management**: gcTime prevents memory leaks by removing unused data from the cache.

2. **Performance**: Keeping data in cache (even stale) allows instant display while refetching in the background.

3. **Navigation Experience**: Users navigating back to a page see cached data immediately, improving perceived performance.

4. **Resource Efficiency**: Proper gcTime balances memory usage with the benefits of caching.

5. **Relationship with staleTime**: gcTime should typically be longer than staleTime to benefit from background refetching.

Key differences:
- `staleTime`: How long until data is considered stale (triggers background refetch)
- `gcTime`: How long inactive data stays in cache before garbage collection

Timeline example:
```
Query made -> Data fresh (staleTime period)
           -> Data stale (will refetch on next access)
           -> Component unmounts (gcTime countdown starts)
           -> gcTime expires -> Data removed from cache
```

Best practices:
- gcTime >= staleTime (usually 2-5x longer)
- Consider data size when setting gcTime
- Use Infinity sparingly and only for small, truly static data
- Monitor memory usage in production
