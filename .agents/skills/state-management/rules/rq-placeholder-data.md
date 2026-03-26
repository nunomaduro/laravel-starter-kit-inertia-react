---
title: Placeholder Data Pattern
impact: MEDIUM
section: Cache & Performance
tags: react-query, placeholder-data, ux
---

# Placeholder Data for Immediate Display

**Impact: MEDIUM**


Placeholder data provides temporary data to display while the actual query is loading. Unlike initialData, it doesn't persist to the cache and doesn't affect staleness.

## Bad Example

```tsx
// Anti-pattern: Using initialData when you want temporary display data
const { data: user } = useQuery({
  queryKey: ['user', userId],
  queryFn: () => fetchUser(userId),
  initialData: { name: 'Loading...', email: '' }, // This goes into cache!
  // Cache now contains fake data until refetch
});

// Anti-pattern: Conditional rendering that causes layout shift
function UserProfile({ userId }: { userId: string }) {
  const { data, isLoading } = useQuery({
    queryKey: ['user', userId],
    queryFn: () => fetchUser(userId),
  });

  if (isLoading) {
    return <Skeleton />; // Layout might shift when data loads
  }

  return <ProfileCard user={data} />;
}

// Anti-pattern: placeholderData that doesn't match data shape
const { data } = useQuery({
  queryKey: ['users'],
  queryFn: fetchUsers,
  placeholderData: [], // Missing expected fields that component might access
});
```

## Good Example

```tsx
// Use placeholderData for immediate display
const { data: user, isPlaceholderData } = useQuery({
  queryKey: ['user', userId],
  queryFn: () => fetchUser(userId),
  placeholderData: {
    id: userId,
    name: 'Loading...',
    email: '',
    avatar: '/placeholder-avatar.png',
  },
});

// Show loading state while using placeholder
return (
  <div className={isPlaceholderData ? 'opacity-50' : ''}>
    <ProfileCard user={user} />
  </div>
);

// Use previous data as placeholder when filters change
function ProductList({ category }: { category: string }) {
  const { data: products, isPlaceholderData } = useQuery({
    queryKey: ['products', category],
    queryFn: () => fetchProducts(category),
    placeholderData: (previousData) => previousData, // Keep showing old data
  });

  return (
    <div className={isPlaceholderData ? 'loading' : ''}>
      <ProductGrid products={products} />
    </div>
  );
}

// Generate placeholder from query client cache
function UserDetail({ userId }: { userId: string }) {
  const queryClient = useQueryClient();

  const { data: user } = useQuery({
    queryKey: ['user', userId],
    queryFn: () => fetchUser(userId),
    placeholderData: () => {
      // Look for user in the users list cache
      const users = queryClient.getQueryData<User[]>(['users']);
      return users?.find(u => u.id === userId);
    },
  });

  return <UserProfile user={user} />;
}

// Placeholder with realistic structure
interface Post {
  id: string;
  title: string;
  content: string;
  author: User;
  createdAt: string;
}

const createPostPlaceholder = (postId: string): Post => ({
  id: postId,
  title: '...',
  content: '...',
  author: {
    id: '',
    name: 'Loading...',
    avatar: '/placeholder.png',
  },
  createdAt: new Date().toISOString(),
});

function PostDetail({ postId }: { postId: string }) {
  const { data: post, isPlaceholderData } = useQuery({
    queryKey: ['post', postId],
    queryFn: () => fetchPost(postId),
    placeholderData: () => createPostPlaceholder(postId),
  });

  return (
    <article aria-busy={isPlaceholderData}>
      <h1>{post.title}</h1>
      <p>{post.content}</p>
      <AuthorBadge author={post.author} />
    </article>
  );
}

// Conditional placeholder — only use previous data if query is similar
const { data, isPlaceholderData } = useQuery({
  queryKey: ['search', searchTerm],
  queryFn: () => search(searchTerm),
  placeholderData: (previousData, previousQuery) => {
    // Only use previous data if it's from a similar query
    if (previousQuery?.queryKey[1]?.toString().startsWith(searchTerm[0])) {
      return previousData;
    }
    return undefined;
  },
});
```

## Why

1. **Instant UI**: Users see content structure immediately instead of loading spinners, reducing perceived latency.

2. **Layout Stability**: Placeholder data maintains layout while real data loads, preventing cumulative layout shift.

3. **Cache Integrity**: Unlike initialData, placeholder data never enters the cache, so stale checks work correctly.

4. **Smooth Transitions**: Using previous data as placeholder creates smooth transitions when query parameters change.

5. **Progressive Enhancement**: Components can render optimistically and enhance when real data arrives.

6. **Accessibility**: The `isPlaceholderData` flag allows proper ARIA states and visual loading indicators.

Placeholder vs Initial Data:
| Aspect | placeholderData | initialData |
|--------|----------------|-------------|
| Enters cache | No | Yes |
| Affects staleness | No | Yes (with staleTime) |
| Available on first render | Yes | Yes |
| Triggers refetch | Always | Depends on staleTime |
| Use case | Temporary display | Known good data |
