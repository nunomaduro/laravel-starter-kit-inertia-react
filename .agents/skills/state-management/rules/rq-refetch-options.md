---
title: Refetch Configuration
impact: HIGH
section: Cache & Performance
tags: react-query, refetching, background-updates
---

# Refetch Options Configuration

**Impact: HIGH**


Refetch options control when and how React Query automatically refetches data. Proper configuration prevents unnecessary requests while keeping data fresh.

## Bad Example

```tsx
// Anti-pattern: Refetching on every window focus for static data
const { data: countries } = useQuery({
  queryKey: ['countries'],
  queryFn: fetchCountries,
  // Default refetchOnWindowFocus: true causes unnecessary refetches
});

// Anti-pattern: Aggressive refetching for non-critical data
const { data: userPreferences } = useQuery({
  queryKey: ['preferences'],
  queryFn: fetchPreferences,
  refetchInterval: 1000, // Refetching every second is excessive
  refetchOnMount: 'always',
  refetchOnWindowFocus: 'always',
  refetchOnReconnect: 'always',
});

// Anti-pattern: Disabling all refetches for dynamic data
const { data: notifications } = useQuery({
  queryKey: ['notifications'],
  queryFn: fetchNotifications,
  refetchOnMount: false,
  refetchOnWindowFocus: false,
  refetchOnReconnect: false,
  // User never sees updated notifications
});
```

## Good Example

```tsx
// Static data - disable automatic refetching
const { data: countries } = useQuery({
  queryKey: ['countries'],
  queryFn: fetchCountries,
  staleTime: Infinity,
  refetchOnMount: false,
  refetchOnWindowFocus: false,
  refetchOnReconnect: false,
});

// Dynamic data - smart refetching
const { data: notifications } = useQuery({
  queryKey: ['notifications'],
  queryFn: fetchNotifications,
  staleTime: 0,
  refetchOnMount: true, // Refetch when component mounts
  refetchOnWindowFocus: true, // Refetch when user returns
  refetchOnReconnect: true, // Refetch after network recovery
  refetchInterval: 30 * 1000, // Poll every 30 seconds
  refetchIntervalInBackground: false, // Don't poll when tab is hidden
});

// Conditional polling based on state
const { data: orderStatus } = useQuery({
  queryKey: ['order', orderId, 'status'],
  queryFn: () => fetchOrderStatus(orderId),
  refetchInterval: (query) => {
    // Stop polling when order is complete
    if (query.state.data?.status === 'delivered') {
      return false;
    }
    // Poll more frequently for active orders
    if (query.state.data?.status === 'in_transit') {
      return 10 * 1000; // 10 seconds
    }
    return 60 * 1000; // 1 minute for other statuses
  },
});

// User-initiated refetch
function DataDisplay() {
  const { data, refetch, isFetching } = useQuery({
    queryKey: ['data'],
    queryFn: fetchData,
    refetchOnWindowFocus: false, // Manual control only
  });

  return (
    <div>
      <button onClick={() => refetch()} disabled={isFetching}>
        {isFetching ? 'Refreshing...' : 'Refresh'}
      </button>
      <DataView data={data} />
    </div>
  );
}

// Global defaults with sensible refetch behavior
const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      refetchOnMount: true,
      refetchOnWindowFocus: true,
      refetchOnReconnect: true,
      refetchInterval: false,
      refetchIntervalInBackground: false,
    },
  },
});

// Query-specific refetch configurations
const refetchConfigs = {
  static: {
    refetchOnMount: false,
    refetchOnWindowFocus: false,
    refetchOnReconnect: false,
  },
  background: {
    refetchOnMount: true,
    refetchOnWindowFocus: true,
    refetchOnReconnect: true,
  },
  polling: (interval: number) => ({
    refetchInterval: interval,
    refetchIntervalInBackground: false,
  }),
};
```

## Why

1. **Data Freshness**: Proper refetch settings ensure users see current data when returning to the app or recovering from network issues.

2. **Performance**: Disabling unnecessary refetches for static data reduces server load and improves performance.

3. **User Experience**: Smart polling keeps real-time features updated without excessive battery/data usage.

4. **Resource Efficiency**: `refetchIntervalInBackground: false` prevents polling when the tab is not visible.

5. **Control**: The `refetch()` function gives users manual control when automatic refetching is disabled.

Refetch options explained:
- `refetchOnMount`: Refetch when component using the query mounts
  - `true`: Refetch if stale
  - `false`: Never refetch on mount
  - `'always'`: Always refetch, even if fresh
- `refetchOnWindowFocus`: Refetch when window regains focus
- `refetchOnReconnect`: Refetch when network reconnects
- `refetchInterval`: Polling interval in milliseconds (or function returning interval)
- `refetchIntervalInBackground`: Continue polling when tab is hidden
