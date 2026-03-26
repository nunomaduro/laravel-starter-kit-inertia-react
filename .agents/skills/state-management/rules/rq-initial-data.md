---
title: Initial Data Configuration
impact: MEDIUM
section: Cache & Performance
tags: react-query, initial-data, prefetch
---

# Initial Data for Cache Seeding

**Impact: MEDIUM**


Initial data seeds the query cache with known data before or instead of fetching. It's cached immediately and affects query staleness, unlike placeholderData.

## Bad Example

```tsx
// Anti-pattern: Using initialData for loading placeholders
const { data } = useQuery({
  queryKey: ['user', userId],
  queryFn: () => fetchUser(userId),
  initialData: { name: 'Loading...' }, // This is cached as real data!
  // If staleTime > 0, this fake data will be served
});

// Anti-pattern: Initial data without staleTime consideration
const { data } = useQuery({
  queryKey: ['product', productId],
  queryFn: () => fetchProduct(productId),
  initialData: partialProduct, // From list view
  // Without initialDataUpdatedAt, React Query doesn't know how old this is
});

// Anti-pattern: Initial data that might be stale
function ProductDetail({ product }: { product: Product }) {
  const { data } = useQuery({
    queryKey: ['product', product.id],
    queryFn: () => fetchProduct(product.id),
    initialData: product,
    staleTime: 5 * 60 * 1000, // 5 minutes
    // Product from props might be hours old, but won't refetch for 5 minutes
  });

  return <ProductView product={data} />;
}
```

## Good Example

```tsx
// SSR/SSG: Seed cache with server-fetched data
// In Next.js pages
export async function getServerSideProps() {
  const queryClient = new QueryClient();

  await queryClient.prefetchQuery({
    queryKey: ['user', userId],
    queryFn: () => fetchUser(userId),
  });

  return {
    props: {
      dehydratedState: dehydrate(queryClient),
    },
  };
}

// Client component uses the pre-seeded cache
function UserProfile({ userId }: { userId: string }) {
  const { data } = useQuery({
    queryKey: ['user', userId],
    queryFn: () => fetchUser(userId),
    // Data is already in cache from SSR
  });

  return <Profile user={data} />;
}

// Use initialData with proper staleness tracking
function ProductDetail({ listProduct }: { listProduct: ListProduct }) {
  const { data: product } = useQuery({
    queryKey: ['product', listProduct.id],
    queryFn: () => fetchProduct(listProduct.id),
    initialData: listProduct,
    initialDataUpdatedAt: listProduct.fetchedAt, // Track when list was fetched
    staleTime: 60 * 1000, // 1 minute
    // Will refetch if listProduct.fetchedAt is older than staleTime
  });

  return <ProductView product={product} />;
}

// Seeding from another query's cache
function UserDetail({ userId }: { userId: string }) {
  const queryClient = useQueryClient();

  const { data: user } = useQuery({
    queryKey: ['user', userId],
    queryFn: () => fetchUser(userId),
    initialData: () => {
      const usersCache = queryClient.getQueryData<User[]>(['users']);
      return usersCache?.find(u => u.id === userId);
    },
    initialDataUpdatedAt: () => {
      // Get the timestamp of the users list query
      return queryClient.getQueryState(['users'])?.dataUpdatedAt;
    },
  });

  return <UserProfile user={user} />;
}

// Conditional initial data based on source
interface ProductDetailProps {
  productId: string;
  initialProduct?: Product;
  productTimestamp?: number;
}

function ProductDetail({
  productId,
  initialProduct,
  productTimestamp,
}: ProductDetailProps) {
  const { data } = useQuery({
    queryKey: ['product', productId],
    queryFn: () => fetchProduct(productId),
    ...(initialProduct && {
      initialData: initialProduct,
      initialDataUpdatedAt: productTimestamp,
    }),
    staleTime: 30 * 1000,
  });

  return <Product data={data} />;
}

// Pre-seeding cache on application load
async function initializeApp() {
  const queryClient = new QueryClient();

  // Pre-fetch critical data
  await Promise.all([
    queryClient.prefetchQuery({
      queryKey: ['currentUser'],
      queryFn: fetchCurrentUser,
    }),
    queryClient.prefetchQuery({
      queryKey: ['config'],
      queryFn: fetchAppConfig,
    }),
  ]);

  return queryClient;
}

// Direct cache manipulation for known data
function handleRouteData(data: RouteData) {
  // Seed cache with data from route/navigation state
  queryClient.setQueryData(['resource', data.id], data.resource, {
    updatedAt: data.timestamp,
  });
}
```

## Why

1. **SSR Hydration**: Initial data enables seamless hydration of server-rendered content into the React Query cache.

2. **Navigation Optimization**: Data from list views can seed detail view caches, eliminating loading states.

3. **Offline Support**: Known data can be pre-seeded to support offline-first experiences.

4. **Perceived Performance**: Instant data display from cache feels faster than waiting for network requests.

5. **Staleness Tracking**: `initialDataUpdatedAt` ensures proper refetching when seeded data is old.

6. **Type Safety**: Initial data must match the query return type, catching errors at compile time.

Initial Data vs Placeholder Data:
| Feature | initialData | placeholderData |
|---------|-------------|-----------------|
| Cached | Yes | No |
| Affects staleTime | Yes | No |
| Triggers background refetch | If stale | Always |
| Use case | Known valid data | Temporary UI |
| With updatedAt | Track staleness | N/A |

Best practices:
- Always use `initialDataUpdatedAt` when initial data might be stale
- Use `placeholderData` for temporary display data
- Use `initialData` for valid data from another source (SSR, cache, route state)
- Consider data completeness (list items may have fewer fields than detail views)
