---
section: navigation
priority: high
description: Use replace option to prevent cluttered browser history
keywords: [replace, history, browser, navigation, filters, tabs]
---

# Navigation Replace History

Use the replace option to modify the current history entry instead of adding a new one, preventing cluttered browser history.

## Bad Example

```tsx
// Anti-pattern: Every filter change adds to history
import { router } from '@inertiajs/react';
import { useState } from 'react';

export default function ProductSearch({ products, filters }) {
  const handleFilterChange = (key: string, value: string) => {
    // Each change pushes to history - back button becomes unusable
    router.get(route('products.index'), {
      ...filters,
      [key]: value,
    });
  };

  const handleSearchInput = (search: string) => {
    // Every keystroke adds history entry!
    router.get(route('products.index'), { search });
  };

  return (
    <div>
      <input
        type="search"
        onChange={(e) => handleSearchInput(e.target.value)}
      />
      <select onChange={(e) => handleFilterChange('category', e.target.value)}>
        {/* options */}
      </select>
    </div>
  );
}
```

## Good Example

```tsx
// resources/js/Pages/Products/Index.tsx
import { router, Link } from '@inertiajs/react';
import { useState, useCallback } from 'react';
import debounce from 'lodash/debounce';

interface ProductsIndexProps {
  products: PaginatedData<Product>;
  filters: {
    search: string;
    category: string;
    sort: string;
    min_price: string;
    max_price: string;
  };
  categories: Category[];
}

export default function Index({ products, filters, categories }: ProductsIndexProps) {
  const [localSearch, setLocalSearch] = useState(filters.search);

  // Debounced search that replaces history
  const debouncedSearch = useCallback(
    debounce((search: string) => {
      router.get(
        route('products.index'),
        { ...filters, search },
        {
          replace: true,      // Don't add history entry for each keystroke
          preserveState: true,
          preserveScroll: true,
        }
      );
    }, 300),
    [filters]
  );

  const handleSearchChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const value = e.target.value;
    setLocalSearch(value);
    debouncedSearch(value);
  };

  // Filter changes should replace history
  const updateFilter = (key: string, value: string) => {
    router.get(
      route('products.index'),
      { ...filters, [key]: value },
      {
        replace: true,        // Replace instead of push
        preserveState: true,
        preserveScroll: true,
      }
    );
  };

  // Clear all filters - this SHOULD add history (intentional action)
  const clearFilters = () => {
    router.get(
      route('products.index'),
      {},
      {
        replace: false, // Push to history so user can "undo"
      }
    );
  };

  return (
    <div>
      {/* Search input - replaces history */}
      <input
        type="search"
        value={localSearch}
        onChange={handleSearchChange}
        placeholder="Search products..."
        className="rounded-md border-gray-300"
      />

      {/* Category filter - replaces history */}
      <select
        value={filters.category}
        onChange={(e) => updateFilter('category', e.target.value)}
        className="rounded-md border-gray-300"
      >
        <option value="">All Categories</option>
        {categories.map((category) => (
          <option key={category.id} value={category.slug}>
            {category.name}
          </option>
        ))}
      </select>

      {/* Sort - replaces history */}
      <select
        value={filters.sort}
        onChange={(e) => updateFilter('sort', e.target.value)}
        className="rounded-md border-gray-300"
      >
        <option value="newest">Newest</option>
        <option value="price_asc">Price: Low to High</option>
        <option value="price_desc">Price: High to Low</option>
        <option value="popular">Most Popular</option>
      </select>

      {/* Price range - replaces history */}
      <div className="flex gap-2">
        <input
          type="number"
          placeholder="Min"
          value={filters.min_price}
          onChange={(e) => updateFilter('min_price', e.target.value)}
        />
        <input
          type="number"
          placeholder="Max"
          value={filters.max_price}
          onChange={(e) => updateFilter('max_price', e.target.value)}
        />
      </div>

      {/* Clear filters - adds to history */}
      <button onClick={clearFilters} className="text-blue-600 hover:underline">
        Clear All Filters
      </button>

      {/* Product grid */}
      <div className="grid grid-cols-3 gap-4">
        {products.data.map((product) => (
          <ProductCard key={product.id} product={product} />
        ))}
      </div>

      {/* Pagination - adds to history (intentional navigation) */}
      <div className="flex gap-2">
        {products.links.map((link, index) => (
          <Link
            key={index}
            href={link.url || '#'}
            replace={false} // Pagination SHOULD add history
            className={link.active ? 'font-bold' : ''}
            dangerouslySetInnerHTML={{ __html: link.label }}
          />
        ))}
      </div>
    </div>
  );
}

// Tab navigation - replace history for tab switches
function TabbedContent({ activeTab }: { activeTab: string }) {
  const switchTab = (tab: string) => {
    router.get(
      route('content.show'),
      { tab },
      {
        replace: true,        // Tab switches replace history
        preserveState: true,
      }
    );
  };

  return (
    <div>
      <div className="flex border-b">
        {['overview', 'details', 'reviews'].map((tab) => (
          <button
            key={tab}
            onClick={() => switchTab(tab)}
            className={activeTab === tab ? 'border-b-2 border-blue-500' : ''}
          >
            {tab}
          </button>
        ))}
      </div>
    </div>
  );
}
```

## Why

Using replace for history management provides:

1. **Clean History**: Back button takes users to logical previous pages
2. **Filter UX**: Multiple filter changes don't pollute browser history
3. **Search Experience**: Typing in search doesn't create dozens of history entries
4. **Intentional Actions**: Use push (default) for deliberate navigation actions
5. **Tab Navigation**: Switching tabs shouldn't add to history stack
6. **Performance**: Fewer history entries means less memory usage
7. **User Expectations**: History behaves like traditional websites
