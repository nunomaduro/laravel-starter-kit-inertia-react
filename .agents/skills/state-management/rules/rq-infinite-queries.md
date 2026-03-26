---
title: Infinite Queries Pattern
impact: HIGH
section: Advanced Queries
tags: react-query, infinite-scroll, pagination
---

# Infinite Queries for Paginated Lists

**Impact: HIGH**


Infinite queries handle "load more" patterns where data is fetched in pages and accumulated. They manage page parameters and provide seamless scrolling experiences.

## Bad Example

```tsx
// Anti-pattern: Manual infinite scroll with useQuery
function PostList() {
  const [pages, setPages] = useState<Post[][]>([]);
  const [pageParam, setPageParam] = useState(0);

  const { data, isLoading } = useQuery({
    queryKey: ['posts', pageParam],
    queryFn: () => fetchPosts(pageParam),
  });

  // Manually managing accumulated pages
  useEffect(() => {
    if (data) {
      setPages((prev) => [...prev, data.posts]);
    }
  }, [data]);

  // Problems: duplicate posts, complex state management, no cache benefits
}

// Anti-pattern: Using cursor as regular query parameter
const { data } = useQuery({
  queryKey: ['posts', cursor],
  queryFn: () => fetchPosts(cursor),
  // Each cursor creates separate cache entry
  // Can't easily access all loaded pages
});

// Anti-pattern: Infinite query without proper getNextPageParam
const { data } = useInfiniteQuery({
  queryKey: ['posts'],
  queryFn: ({ pageParam }) => fetchPosts(pageParam),
  initialPageParam: 0,
  getNextPageParam: () => undefined, // Always returns undefined - can't load more
});
```

## Good Example

```tsx
// Proper infinite query setup
interface PostsResponse {
  posts: Post[];
  nextCursor: string | null;
  hasMore: boolean;
}

function useInfinitePosts() {
  return useInfiniteQuery({
    queryKey: ['posts', 'infinite'],
    queryFn: async ({ pageParam }): Promise<PostsResponse> => {
      const response = await fetch(`/api/posts?cursor=${pageParam}`);
      if (!response.ok) throw new Error('Failed to fetch posts');
      return response.json();
    },
    initialPageParam: '',
    getNextPageParam: (lastPage) => lastPage.nextCursor ?? undefined,
    getPreviousPageParam: (firstPage) => firstPage.prevCursor ?? undefined,
  });
}

// Component with load more button
function PostList() {
  const {
    data,
    fetchNextPage,
    hasNextPage,
    isFetchingNextPage,
    isLoading,
    isError,
  } = useInfinitePosts();

  if (isLoading) return <LoadingSpinner />;
  if (isError) return <ErrorMessage />;

  // Flatten pages into single array
  const allPosts = data?.pages.flatMap((page) => page.posts) ?? [];

  return (
    <div>
      {allPosts.map((post) => (
        <PostCard key={post.id} post={post} />
      ))}

      {hasNextPage && (
        <button
          onClick={() => fetchNextPage()}
          disabled={isFetchingNextPage}
        >
          {isFetchingNextPage ? 'Loading...' : 'Load More'}
        </button>
      )}
    </div>
  );
}

// Infinite scroll with Intersection Observer
function InfinitePostList() {
  const {
    data,
    fetchNextPage,
    hasNextPage,
    isFetchingNextPage,
  } = useInfinitePosts();

  const loadMoreRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const observer = new IntersectionObserver(
      (entries) => {
        if (entries[0].isIntersecting && hasNextPage && !isFetchingNextPage) {
          fetchNextPage();
        }
      },
      { threshold: 0.1 }
    );

    if (loadMoreRef.current) {
      observer.observe(loadMoreRef.current);
    }

    return () => observer.disconnect();
  }, [fetchNextPage, hasNextPage, isFetchingNextPage]);

  const allPosts = data?.pages.flatMap((page) => page.posts) ?? [];

  return (
    <div>
      {allPosts.map((post) => (
        <PostCard key={post.id} post={post} />
      ))}

      <div ref={loadMoreRef} style={{ height: 20 }}>
        {isFetchingNextPage && <LoadingSpinner />}
      </div>
    </div>
  );
}

// Offset-based pagination
function useInfiniteProducts(category: string) {
  const limit = 20;

  return useInfiniteQuery({
    queryKey: ['products', category, 'infinite'],
    queryFn: async ({ pageParam }) => {
      const offset = pageParam * limit;
      const response = await fetch(
        `/api/products?category=${category}&offset=${offset}&limit=${limit}`
      );
      return response.json();
    },
    initialPageParam: 0,
    getNextPageParam: (lastPage, allPages) => {
      // If last page has fewer items than limit, no more pages
      return lastPage.products.length === limit ? allPages.length : undefined;
    },
  });
}

// Bidirectional infinite scroll
function useInfiniteMessages(channelId: string) {
  return useInfiniteQuery({
    queryKey: ['messages', channelId],
    queryFn: ({ pageParam }) => fetchMessages(channelId, pageParam),
    initialPageParam: { cursor: null, direction: 'backward' },
    getNextPageParam: (lastPage) =>
      lastPage.hasOlder ? { cursor: lastPage.oldestId, direction: 'backward' } : undefined,
    getPreviousPageParam: (firstPage) =>
      firstPage.hasNewer ? { cursor: firstPage.newestId, direction: 'forward' } : undefined,
    maxPages: 10, // Limit memory usage
  });
}

// With select for transformation
const { data } = useInfiniteQuery({
  queryKey: ['posts'],
  queryFn: fetchPostsPage,
  initialPageParam: 0,
  getNextPageParam: (lastPage) => lastPage.nextPage,
  select: (data) => ({
    pages: data.pages,
    pageParams: data.pageParams,
    // Add computed properties
    totalCount: data.pages.reduce((sum, page) => sum + page.posts.length, 0),
    allPosts: data.pages.flatMap((page) => page.posts),
  }),
});
```

## Why

1. **Memory Efficiency**: Infinite queries store all pages efficiently in a single cache entry.

2. **Seamless UX**: Automatic page tracking enables smooth infinite scroll without manual state management.

3. **Cache Benefits**: All loaded pages share staleness, refetching updates the entire list consistently.

4. **Bidirectional Loading**: Support for both next and previous pages enables chat-like interfaces.

5. **Memory Limits**: `maxPages` option prevents memory issues with very long lists.

6. **Refetch Behavior**: Refetching an infinite query refetches all pages, ensuring data consistency.

Key properties:
- `data.pages`: Array of page data
- `data.pageParams`: Array of page parameters used
- `fetchNextPage()`: Fetch the next page
- `fetchPreviousPage()`: Fetch the previous page
- `hasNextPage`: Boolean indicating if more pages exist
- `hasPreviousPage`: Boolean for previous pages
- `isFetchingNextPage`: Loading state for next page
- `isFetchingPreviousPage`: Loading state for previous page
