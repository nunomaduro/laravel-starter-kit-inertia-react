---
title: Stale Time Configuration
impact: HIGH
section: Cache & Performance
tags: react-query, caching, stale-time
---

# Stale Time Configuration

**Impact: HIGH**

Stale time determines how long data is considered fresh. During this period, React Query will return cached data without refetching.

## Bad Example

```tsx
// Anti-pattern: Using default staleTime (0) for static data
const { data: countries } = useQuery({
  queryKey: ['countries'],
  queryFn: fetchCountries,
  // Default staleTime is 0, causing unnecessary refetches
});

// Anti-pattern: Setting staleTime too high for dynamic data
const { data: notifications } = useQuery({
  queryKey: ['notifications'],
  queryFn: fetchNotifications,
  staleTime: Infinity, // User might miss important notifications
});

// Anti-pattern: Inconsistent staleTime across related queries
const { data: user } = useQuery({
  queryKey: ['user', userId],
  queryFn: () => fetchUser(userId),
  staleTime: 5 * 60 * 1000, // 5 minutes
});

const { data: userPosts } = useQuery({
  queryKey: ['user', userId, 'posts'],
  queryFn: () => fetchUserPosts(userId),
  staleTime: 0, // Posts might show even when user is stale
});
```

## Good Example

```tsx
// Configure staleTime based on data volatility

// Static reference data - rarely changes
const { data: countries } = useQuery({
  queryKey: ['countries'],
  queryFn: fetchCountries,
  staleTime: 24 * 60 * 60 * 1000, // 24 hours
});

// Semi-static data - changes occasionally
const { data: categories } = useQuery({
  queryKey: ['categories'],
  queryFn: fetchCategories,
  staleTime: 60 * 60 * 1000, // 1 hour
});

// User profile - moderate freshness needed
const { data: user } = useQuery({
  queryKey: userKeys.detail(userId),
  queryFn: () => fetchUser(userId),
  staleTime: 5 * 60 * 1000, // 5 minutes
});

// Real-time data - always fresh
const { data: notifications } = useQuery({
  queryKey: ['notifications'],
  queryFn: fetchNotifications,
  staleTime: 0, // Always refetch on mount
  refetchInterval: 30 * 1000, // Also poll every 30 seconds
});

// Global defaults in QueryClient
const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 60 * 1000, // 1 minute default
    },
  },
});

// Query-specific overrides with factory
const createStaticQuery = <T,>(queryKey: QueryKey, queryFn: () => Promise<T>) => ({
  queryKey,
  queryFn,
  staleTime: Infinity,
  gcTime: Infinity, // Keep in cache forever (formerly cacheTime)
});

// Usage
const staticQueries = {
  countries: createStaticQuery(['countries'], fetchCountries),
  currencies: createStaticQuery(['currencies'], fetchCurrencies),
};

const { data } = useQuery(staticQueries.countries);
```

## Why

1. **Performance**: Appropriate staleTime reduces unnecessary network requests and improves perceived performance.

2. **User Experience**: Fresh data when needed ensures users see up-to-date information without manual refreshes.

3. **Server Load**: Proper staleTime configuration reduces load on backend servers by avoiding redundant requests.

4. **Consistency**: Related queries should have consistent staleness to avoid showing mismatched data.

5. **Battery/Data Usage**: On mobile devices, reducing refetches preserves battery life and cellular data.

6. **Offline Support**: Longer staleTime means cached data remains usable longer during connectivity issues.

Guidelines for staleTime:
- Static reference data: `Infinity` or 24+ hours
- Configuration/settings: 1-24 hours
- User data: 1-10 minutes
- Dynamic/social data: 0-60 seconds
- Real-time data: 0 (with refetchInterval)
