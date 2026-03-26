---
title: Paginated Queries
impact: HIGH
section: Advanced Queries
tags: react-query, pagination, lists
---

# Paginated Queries

**Impact: HIGH**


Paginated queries fetch data one page at a time with navigation controls. Unlike infinite queries, they replace content when changing pages rather than accumulating.

## Bad Example

```tsx
// Anti-pattern: Page flicker on navigation
function PaginatedTable() {
  const [page, setPage] = useState(1);

  const { data, isLoading } = useQuery({
    queryKey: ['users', page],
    queryFn: () => fetchUsers(page),
    // No placeholderData - UI flickers on page change
  });

  if (isLoading) return <Skeleton />; // Shows skeleton on every page change

  return <Table data={data} />;
}

// Anti-pattern: Losing previous page data immediately
function UserList() {
  const [page, setPage] = useState(1);

  const { data } = useQuery({
    queryKey: ['users', page],
    queryFn: () => fetchUsers(page),
    gcTime: 0, // Previous page removed immediately
  });

  // Going back requires refetch
}

// Anti-pattern: Separate loading state for each page
function BadPagination() {
  const [page, setPage] = useState(1);
  const [isChangingPage, setIsChangingPage] = useState(false);

  const { data, isLoading } = useQuery({
    queryKey: ['data', page],
    queryFn: () => fetchData(page),
  });

  const handlePageChange = async (newPage: number) => {
    setIsChangingPage(true);
    setPage(newPage);
    // Manually tracking loading state is redundant
  };
}
```

## Good Example

```tsx
// Keep previous data while fetching new page
function PaginatedUserTable() {
  const [page, setPage] = useState(1);

  const { data, isLoading, isFetching, isPlaceholderData } = useQuery({
    queryKey: ['users', page],
    queryFn: () => fetchUsers(page),
    placeholderData: (previousData) => previousData, // Keep showing old data
    staleTime: 5 * 60 * 1000, // 5 minutes
  });

  return (
    <div>
      {/* Show loading indicator without hiding content */}
      {isFetching && <div className="loading-bar" />}

      <Table
        data={data?.users}
        className={isPlaceholderData ? 'opacity-50' : ''}
      />

      <Pagination
        currentPage={page}
        totalPages={data?.totalPages}
        onPageChange={setPage}
        disabled={isFetching}
      />
    </div>
  );
}

// Prefetch adjacent pages for instant navigation
function SmartPaginatedList() {
  const queryClient = useQueryClient();
  const [page, setPage] = useState(1);

  const { data, isFetching, isPlaceholderData } = useQuery({
    queryKey: ['products', page],
    queryFn: () => fetchProducts(page),
    placeholderData: (previousData) => previousData,
  });

  // Prefetch next page on hover or when current page loads
  useEffect(() => {
    if (data?.hasNextPage) {
      queryClient.prefetchQuery({
        queryKey: ['products', page + 1],
        queryFn: () => fetchProducts(page + 1),
      });
    }
  }, [data, page, queryClient]);

  const handleNextPage = () => {
    if (!isPlaceholderData && data?.hasNextPage) {
      setPage((p) => p + 1);
    }
  };

  const handlePrevPage = () => {
    setPage((p) => Math.max(1, p - 1));
  };

  return (
    <div>
      <ProductGrid products={data?.products} isLoading={isPlaceholderData} />

      <div className="pagination">
        <button onClick={handlePrevPage} disabled={page === 1}>
          Previous
        </button>
        <span>Page {page} of {data?.totalPages}</span>
        <button
          onClick={handleNextPage}
          disabled={isPlaceholderData || !data?.hasNextPage}
        >
          Next
        </button>
      </div>
    </div>
  );
}

// Pagination with filters and sorting
interface TableFilters {
  search: string;
  status: string;
  sortBy: string;
  sortOrder: 'asc' | 'desc';
}

function FilterablePaginatedTable() {
  const [page, setPage] = useState(1);
  const [filters, setFilters] = useState<TableFilters>({
    search: '',
    status: 'all',
    sortBy: 'createdAt',
    sortOrder: 'desc',
  });

  // Reset to page 1 when filters change
  const handleFilterChange = (newFilters: Partial<TableFilters>) => {
    setFilters((prev) => ({ ...prev, ...newFilters }));
    setPage(1); // Reset pagination
  };

  const { data, isFetching, isPlaceholderData } = useQuery({
    queryKey: ['orders', { page, ...filters }],
    queryFn: () => fetchOrders({ page, ...filters }),
    placeholderData: (previousData) => previousData,
  });

  return (
    <div>
      <FilterBar filters={filters} onChange={handleFilterChange} />

      <div className="relative">
        {isFetching && (
          <div className="absolute inset-0 bg-white/50 flex items-center justify-center">
            <Spinner />
          </div>
        )}
        <OrderTable
          orders={data?.orders}
          sortBy={filters.sortBy}
          sortOrder={filters.sortOrder}
          onSort={(field) =>
            handleFilterChange({
              sortBy: field,
              sortOrder:
                filters.sortBy === field && filters.sortOrder === 'asc'
                  ? 'desc'
                  : 'asc',
            })
          }
        />
      </div>

      <TablePagination
        page={page}
        totalPages={data?.totalPages}
        totalItems={data?.totalItems}
        itemsPerPage={data?.itemsPerPage}
        onPageChange={setPage}
      />
    </div>
  );
}

// URL-synced pagination
function URLPaginatedList() {
  const [searchParams, setSearchParams] = useSearchParams();
  const page = Number(searchParams.get('page')) || 1;

  const { data, isFetching } = useQuery({
    queryKey: ['articles', page],
    queryFn: () => fetchArticles(page),
    placeholderData: (previousData) => previousData,
  });

  const setPage = (newPage: number) => {
    setSearchParams({ page: String(newPage) });
  };

  return (
    // ... component using page and setPage
  );
}
```

## Why

1. **No Flicker**: `placeholderData: previousData` keeps content visible during page transitions.

2. **Visual Feedback**: `isPlaceholderData` and `isFetching` enable subtle loading indicators without hiding content.

3. **Prefetching**: Preloading adjacent pages makes navigation feel instant.

4. **URL Sync**: Syncing pagination with URL enables bookmarking, sharing, and browser navigation.

5. **Filter Reset**: Resetting to page 1 when filters change prevents showing empty pages.

6. **Cache Efficiency**: Each page is cached separately, allowing quick navigation to previously visited pages.

Pagination vs Infinite Queries:
| Aspect | Paginated | Infinite |
|--------|-----------|----------|
| UI Pattern | Page numbers, prev/next | Load more, infinite scroll |
| Cache | Separate per page | All pages in one entry |
| Memory | Constant (one page) | Grows with loaded pages |
| Navigation | Any page directly | Sequential only |
| Best for | Tables, admin panels | Feeds, galleries |
