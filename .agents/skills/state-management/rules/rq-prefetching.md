---
title: Data Prefetching
impact: MEDIUM
section: Cache & Performance
tags: react-query, prefetching, performance
---

# Prefetching Strategies

**Impact: MEDIUM**


Prefetching loads data before it's needed, eliminating loading states when users navigate to new views. Strategic prefetching dramatically improves perceived performance.

## Bad Example

```tsx
// Anti-pattern: No prefetching - every navigation shows loading
function UserList() {
  const { data: users } = useQuery({
    queryKey: ['users'],
    queryFn: fetchUsers,
  });

  return (
    <ul>
      {users?.map((user) => (
        <Link key={user.id} to={`/users/${user.id}`}>
          {user.name}
        </Link>
        // No prefetch - clicking shows loading spinner
      ))}
    </ul>
  );
}

// Anti-pattern: Prefetching everything eagerly
function App() {
  const queryClient = useQueryClient();

  useEffect(() => {
    // Prefetching all possible data on mount - wasteful
    queryClient.prefetchQuery({ queryKey: ['users'], queryFn: fetchUsers });
    queryClient.prefetchQuery({ queryKey: ['posts'], queryFn: fetchPosts });
    queryClient.prefetchQuery({ queryKey: ['products'], queryFn: fetchProducts });
    // ... hundreds more
  }, []);
}

// Anti-pattern: Prefetch without stale consideration
function ProductCard({ product }: { product: Product }) {
  const queryClient = useQueryClient();

  const handleHover = () => {
    // Always fetches, even if data is fresh in cache
    queryClient.prefetchQuery({
      queryKey: ['product', product.id],
      queryFn: () => fetchProduct(product.id),
    });
  };

  return <Card onMouseEnter={handleHover}>{product.name}</Card>;
}
```

## Good Example

```tsx
// Prefetch on hover with stale time consideration
function UserListItem({ user }: { user: User }) {
  const queryClient = useQueryClient();

  const handleMouseEnter = () => {
    // Only prefetches if data is stale or missing
    queryClient.prefetchQuery({
      queryKey: ['user', user.id],
      queryFn: () => fetchUser(user.id),
      staleTime: 5 * 60 * 1000, // Won't refetch if cached and fresh
    });
  };

  return (
    <Link
      to={`/users/${user.id}`}
      onMouseEnter={handleMouseEnter}
    >
      {user.name}
    </Link>
  );
}

// Prefetch next page in pagination
function PaginatedList() {
  const queryClient = useQueryClient();
  const [page, setPage] = useState(1);

  const { data } = useQuery({
    queryKey: ['items', page],
    queryFn: () => fetchItems(page),
    placeholderData: (previousData) => previousData,
  });

  // Prefetch next page when current page loads
  useEffect(() => {
    if (data?.hasNextPage) {
      queryClient.prefetchQuery({
        queryKey: ['items', page + 1],
        queryFn: () => fetchItems(page + 1),
      });
    }
  }, [data, page, queryClient]);

  return (
    <div>
      <ItemList items={data?.items} />
      <Pagination
        page={page}
        hasNext={data?.hasNextPage}
        onNext={() => setPage((p) => p + 1)}
        onPrev={() => setPage((p) => p - 1)}
      />
    </div>
  );
}

// Router-based prefetching
function AppRouter() {
  const queryClient = useQueryClient();

  return (
    <Routes>
      <Route
        path="/dashboard"
        element={<Dashboard />}
        loader={async () => {
          // Prefetch dashboard data during route transition
          await Promise.all([
            queryClient.prefetchQuery({
              queryKey: ['user'],
              queryFn: fetchUser,
            }),
            queryClient.prefetchQuery({
              queryKey: ['notifications'],
              queryFn: fetchNotifications,
            }),
          ]);
          return null;
        }}
      />
      <Route
        path="/products/:id"
        element={<ProductDetail />}
        loader={async ({ params }) => {
          await queryClient.prefetchQuery({
            queryKey: ['product', params.id],
            queryFn: () => fetchProduct(params.id!),
          });
          return null;
        }}
      />
    </Routes>
  );
}

// Prefetch with Intersection Observer (viewport-based)
function LazyPrefetchCard({ productId }: { productId: string }) {
  const queryClient = useQueryClient();
  const cardRef = useRef<HTMLDivElement>(null);
  const prefetched = useRef(false);

  useEffect(() => {
    const observer = new IntersectionObserver(
      (entries) => {
        if (entries[0].isIntersecting && !prefetched.current) {
          prefetched.current = true;
          queryClient.prefetchQuery({
            queryKey: ['product', productId],
            queryFn: () => fetchProduct(productId),
          });
        }
      },
      { rootMargin: '100px' } // Start prefetching 100px before visible
    );

    if (cardRef.current) {
      observer.observe(cardRef.current);
    }

    return () => observer.disconnect();
  }, [productId, queryClient]);

  return (
    <div ref={cardRef}>
      <Link to={`/products/${productId}`}>View Product</Link>
    </div>
  );
}

// SSR prefetching for initial page load
// In Next.js or similar
export async function getServerSideProps() {
  const queryClient = new QueryClient();

  await queryClient.prefetchQuery({
    queryKey: ['initialData'],
    queryFn: fetchInitialData,
  });

  return {
    props: {
      dehydratedState: dehydrate(queryClient),
    },
  };
}

// Smart prefetching based on user behavior
function useSmartPrefetch() {
  const queryClient = useQueryClient();
  const hoverTimerRef = useRef<NodeJS.Timeout>();

  const prefetchOnIntent = (queryKey: QueryKey, queryFn: () => Promise<any>) => {
    return {
      onMouseEnter: () => {
        // Delay prefetch to avoid prefetching on quick mouse passes
        hoverTimerRef.current = setTimeout(() => {
          queryClient.prefetchQuery({ queryKey, queryFn });
        }, 100);
      },
      onMouseLeave: () => {
        // Cancel if mouse leaves quickly
        if (hoverTimerRef.current) {
          clearTimeout(hoverTimerRef.current);
        }
      },
      onFocus: () => {
        // Keyboard navigation - prefetch immediately
        queryClient.prefetchQuery({ queryKey, queryFn });
      },
    };
  };

  return { prefetchOnIntent };
}

// Usage
function ProductLink({ product }: { product: Product }) {
  const { prefetchOnIntent } = useSmartPrefetch();

  return (
    <Link
      to={`/products/${product.id}`}
      {...prefetchOnIntent(
        ['product', product.id],
        () => fetchProduct(product.id)
      )}
    >
      {product.name}
    </Link>
  );
}
```

## Why

1. **Zero Loading States**: Prefetched data is available immediately when the user navigates.

2. **Perceived Performance**: The app feels instant because data is ready before it's needed.

3. **Bandwidth Optimization**: Prefetching during idle time utilizes available bandwidth.

4. **Stale-Aware**: `prefetchQuery` respects staleTime, avoiding redundant fetches.

5. **Background Loading**: Prefetches don't block the UI; they load in the background.

6. **SSR Integration**: Prefetching integrates seamlessly with server-side rendering.

Prefetch strategies by priority:
1. **SSR/SSG**: Most critical data for initial render
2. **Route loaders**: Data needed for the destination page
3. **Hover/Focus**: High-intent user interactions
4. **Viewport**: Items about to scroll into view
5. **Idle**: Low-priority data during browser idle time

Key methods:
- `prefetchQuery`: Prefetch into cache
- `prefetchInfiniteQuery`: Prefetch infinite query
- `ensureQueryData`: Return cached data or fetch if missing
