---
section: navigation
priority: high
description: Maintain component state during navigation with preserveState option
keywords: [preserveState, state, navigation, filters, tabs, ui-state]
---

# Navigation Preserve State

Use preserveState to maintain local component state during navigation, useful for tabs, filters, and accordion states.

## Bad Example

```tsx
// Anti-pattern: Losing local state on navigation
import { Link } from '@inertiajs/react';
import { useState } from 'react';

export default function UserList({ users, filters }) {
  const [expandedRows, setExpandedRows] = useState<number[]>([]);
  const [viewMode, setViewMode] = useState<'grid' | 'list'>('list');

  return (
    <div>
      <div className="flex gap-2">
        {/* These links will reset expandedRows and viewMode */}
        <Link href={route('users.index', { status: 'active' })}>
          Active
        </Link>
        <Link href={route('users.index', { status: 'inactive' })}>
          Inactive
        </Link>
      </div>

      {/* User list that loses expanded state when filtering */}
      {users.map(user => (
        <UserRow
          key={user.id}
          user={user}
          expanded={expandedRows.includes(user.id)}
          onToggle={() => {
            setExpandedRows(prev =>
              prev.includes(user.id)
                ? prev.filter(id => id !== user.id)
                : [...prev, user.id]
            );
          }}
        />
      ))}
    </div>
  );
}
```

## Good Example

```tsx
// resources/js/Pages/Users/Index.tsx
import { Link, router } from '@inertiajs/react';
import { useState } from 'react';

interface UsersIndexProps {
  users: PaginatedData<User>;
  filters: {
    status: string;
    search: string;
  };
}

export default function Index({ users, filters }: UsersIndexProps) {
  // Local UI state that should persist during navigation
  const [expandedRows, setExpandedRows] = useState<number[]>([]);
  const [viewMode, setViewMode] = useState<'grid' | 'list'>('list');
  const [selectedIds, setSelectedIds] = useState<number[]>([]);

  // Filter navigation with state preservation
  const filterByStatus = (status: string) => {
    router.get(
      route('users.index'),
      { ...filters, status },
      {
        preserveState: true,  // Keep expandedRows, viewMode, selectedIds
        preserveScroll: true, // Keep scroll position too
      }
    );
  };

  // Search with debounce and state preservation
  const handleSearch = (search: string) => {
    router.get(
      route('users.index'),
      { ...filters, search },
      {
        preserveState: true,
        preserveScroll: true,
        replace: true, // Don't add to history for each keystroke
      }
    );
  };

  return (
    <div>
      {/* Search input */}
      <input
        type="search"
        defaultValue={filters.search}
        onChange={(e) => handleSearch(e.target.value)}
        placeholder="Search users..."
      />

      {/* Status filter tabs */}
      <div className="flex gap-2 border-b">
        {['all', 'active', 'inactive', 'pending'].map((status) => (
          <button
            key={status}
            onClick={() => filterByStatus(status)}
            className={`px-4 py-2 ${
              filters.status === status
                ? 'border-b-2 border-blue-500 text-blue-600'
                : 'text-gray-500'
            }`}
          >
            {status.charAt(0).toUpperCase() + status.slice(1)}
          </button>
        ))}
      </div>

      {/* View mode toggle - persists across filter changes */}
      <div className="flex gap-2">
        <button
          onClick={() => setViewMode('list')}
          className={viewMode === 'list' ? 'bg-blue-100' : ''}
        >
          List View
        </button>
        <button
          onClick={() => setViewMode('grid')}
          className={viewMode === 'grid' ? 'bg-blue-100' : ''}
        >
          Grid View
        </button>
      </div>

      {/* Bulk actions using preserved selection */}
      {selectedIds.length > 0 && (
        <div className="bg-blue-50 p-4">
          <span>{selectedIds.length} users selected</span>
          <button onClick={() => bulkDelete(selectedIds)}>
            Delete Selected
          </button>
        </div>
      )}

      {/* User list/grid */}
      <div className={viewMode === 'grid' ? 'grid grid-cols-3 gap-4' : 'space-y-2'}>
        {users.data.map((user) => (
          <UserCard
            key={user.id}
            user={user}
            viewMode={viewMode}
            expanded={expandedRows.includes(user.id)}
            selected={selectedIds.includes(user.id)}
            onToggleExpand={() => toggleExpanded(user.id)}
            onToggleSelect={() => toggleSelected(user.id)}
          />
        ))}
      </div>

      {/* Pagination with state preservation */}
      <Pagination
        links={users.links}
        preserveState={true}
        preserveScroll={true}
      />
    </div>
  );

  function toggleExpanded(id: number) {
    setExpandedRows((prev) =>
      prev.includes(id) ? prev.filter((i) => i !== id) : [...prev, id]
    );
  }

  function toggleSelected(id: number) {
    setSelectedIds((prev) =>
      prev.includes(id) ? prev.filter((i) => i !== id) : [...prev, id]
    );
  }
}

// Reusable Pagination component with preserveState option
interface PaginationProps {
  links: PaginationLink[];
  preserveState?: boolean;
  preserveScroll?: boolean;
}

function Pagination({ links, preserveState = false, preserveScroll = false }: PaginationProps) {
  return (
    <div className="flex gap-1">
      {links.map((link, index) => (
        <Link
          key={index}
          href={link.url || '#'}
          preserveState={preserveState}
          preserveScroll={preserveScroll}
          className={`px-3 py-1 rounded ${
            link.active ? 'bg-blue-600 text-white' : 'bg-gray-100'
          } ${!link.url ? 'opacity-50 cursor-not-allowed' : ''}`}
          dangerouslySetInnerHTML={{ __html: link.label }}
        />
      ))}
    </div>
  );
}
```

## Why

Preserving state during navigation provides a better user experience:

1. **UI Continuity**: Expanded rows, view modes, and selections persist
2. **Filter Experience**: Users can filter without losing their context
3. **Pagination**: Navigate pages without resetting UI preferences
4. **Reduced Friction**: No need to re-select items or re-expand details
5. **Natural Feel**: Behaves like a traditional SPA or desktop application
6. **Complementary**: Combine with preserveScroll for complete state retention
7. **Selective Use**: Only preserve state when it makes sense for the interaction
