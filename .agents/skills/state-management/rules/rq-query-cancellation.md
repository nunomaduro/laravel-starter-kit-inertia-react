---
title: Query Cancellation
impact: MEDIUM
section: Advanced Queries
tags: react-query, cancellation, cleanup
---

# Query Cancellation

**Impact: MEDIUM**


Query cancellation stops in-flight requests when they're no longer needed. This prevents race conditions, saves bandwidth, and improves performance.

## Bad Example

```tsx
// Anti-pattern: Not using AbortController signal
const { data } = useQuery({
  queryKey: ['search', searchTerm],
  queryFn: async () => {
    // No cancellation support - old requests may resolve after new ones
    const response = await fetch(`/api/search?q=${searchTerm}`);
    return response.json();
  },
});

// Anti-pattern: Manual cancellation state
function SearchComponent() {
  const [abortController, setAbortController] = useState<AbortController | null>(null);

  const { data } = useQuery({
    queryKey: ['search', searchTerm],
    queryFn: async () => {
      // Cancel previous request manually - React Query handles this
      abortController?.abort();
      const controller = new AbortController();
      setAbortController(controller);

      const response = await fetch(`/api/search?q=${searchTerm}`, {
        signal: controller.signal,
      });
      return response.json();
    },
  });
}

// Anti-pattern: Ignoring cancellation errors
const { data } = useQuery({
  queryKey: ['data'],
  queryFn: async ({ signal }) => {
    try {
      const response = await fetch('/api/data', { signal });
      return response.json();
    } catch (error) {
      // Treating cancellation as an error
      throw error; // This throws AbortError unnecessarily
    }
  },
});
```

## Good Example

```tsx
// Using the signal from query context
const { data } = useQuery({
  queryKey: ['search', searchTerm],
  queryFn: async ({ signal }) => {
    const response = await fetch(`/api/search?q=${searchTerm}`, { signal });

    if (!response.ok) {
      throw new Error('Search failed');
    }

    return response.json();
  },
});

// Proper cancellation with axios
import axios from 'axios';

const { data } = useQuery({
  queryKey: ['users'],
  queryFn: async ({ signal }) => {
    const response = await axios.get('/api/users', { signal });
    return response.data;
  },
});

// Cancellation for multiple requests
const { data } = useQuery({
  queryKey: ['dashboard'],
  queryFn: async ({ signal }) => {
    const [users, posts, stats] = await Promise.all([
      fetch('/api/users', { signal }).then((r) => r.json()),
      fetch('/api/posts', { signal }).then((r) => r.json()),
      fetch('/api/stats', { signal }).then((r) => r.json()),
    ]);

    return { users, posts, stats };
  },
});

// Cancel queries manually before optimistic update
const mutation = useMutation({
  mutationFn: updateTodo,
  onMutate: async (newTodo) => {
    // Cancel any outgoing refetches to prevent overwriting optimistic update
    await queryClient.cancelQueries({ queryKey: ['todos'] });
    await queryClient.cancelQueries({ queryKey: ['todo', newTodo.id] });

    const previousTodos = queryClient.getQueryData(['todos']);
    queryClient.setQueryData(['todos'], (old: Todo[]) =>
      old.map((t) => (t.id === newTodo.id ? newTodo : t))
    );

    return { previousTodos };
  },
});

// Search with debounce and proper cancellation
function useSearchQuery(searchTerm: string) {
  const debouncedTerm = useDebounce(searchTerm, 300);

  return useQuery({
    queryKey: ['search', debouncedTerm],
    queryFn: async ({ signal }) => {
      const response = await fetch(
        `/api/search?q=${encodeURIComponent(debouncedTerm)}`,
        { signal }
      );
      return response.json();
    },
    enabled: debouncedTerm.length >= 2,
  });
}

// Type-safe cancellation handling
async function fetchWithCancellation<T>(
  url: string,
  signal: AbortSignal
): Promise<T> {
  const response = await fetch(url, { signal });

  if (!response.ok) {
    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
  }

  return response.json();
}

const { data } = useQuery({
  queryKey: ['products', category],
  queryFn: ({ signal }) =>
    fetchWithCancellation<Product[]>(`/api/products?category=${category}`, signal),
});

// Cancel queries on component unmount (automatic with React Query)
// But for custom cleanup:
function ExpensiveDataComponent({ id }: { id: string }) {
  const queryClient = useQueryClient();

  useEffect(() => {
    return () => {
      // Cancel ongoing queries when component unmounts
      queryClient.cancelQueries({ queryKey: ['expensive-data', id] });
    };
  }, [id, queryClient]);

  const { data } = useQuery({
    queryKey: ['expensive-data', id],
    queryFn: ({ signal }) => fetchExpensiveData(id, signal),
  });

  return <DataView data={data} />;
}

// GraphQL client with cancellation (e.g., urql, Apollo)
const { data } = useQuery({
  queryKey: ['graphql', 'users'],
  queryFn: async ({ signal }) => {
    const result = await graphqlClient.query(
      {
        query: USERS_QUERY,
        variables: { limit: 10 },
      },
      {
        fetch: (url, options) =>
          fetch(url, { ...options, signal }),
      }
    );
    return result.data;
  },
});

// WebSocket-based queries with cleanup
function useRealtimeData(channelId: string) {
  return useQuery({
    queryKey: ['realtime', channelId],
    queryFn: ({ signal }) =>
      new Promise((resolve, reject) => {
        const ws = new WebSocket(`wss://api.example.com/channel/${channelId}`);

        ws.onmessage = (event) => {
          resolve(JSON.parse(event.data));
        };

        ws.onerror = (error) => {
          reject(error);
        };

        // Clean up WebSocket on abort
        signal.addEventListener('abort', () => {
          ws.close();
          reject(new DOMException('Aborted', 'AbortError'));
        });
      }),
    staleTime: 0,
  });
}
```

## Why

1. **Race Condition Prevention**: Ensures old requests don't overwrite newer data.

2. **Bandwidth Savings**: Cancelled requests don't consume network resources.

3. **Performance**: Prevents unnecessary processing of stale response data.

4. **User Experience**: Faster UI updates when parameters change rapidly (search, filters).

5. **Resource Cleanup**: Properly cleans up WebSocket connections and other resources.

6. **Optimistic Updates**: Cancelling queries prevents them from overwriting optimistic cache updates.

When cancellation happens automatically:
- Query key changes (new query starts, old one cancelled)
- Component unmounts (query no longer observed)
- Manual `queryClient.cancelQueries()` call

Important notes:
- Always pass `signal` to fetch/axios for automatic cancellation
- AbortError is NOT treated as a query error by React Query
- Cancellation resets query to previous state (no error state)
- Use `cancelQueries` before optimistic updates to prevent race conditions
