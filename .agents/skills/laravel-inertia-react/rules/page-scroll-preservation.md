---
section: page-components
priority: high
description: Control scroll behavior during navigation with preserveScroll option
keywords: [scroll, navigation, pagination, ux, preserveScroll]
---

# Page Scroll Preservation

Control scroll behavior during Inertia navigation to maintain user context and provide smooth transitions.

## Bad Example

```tsx
// Anti-pattern: Manual scroll management with useEffect
import { useEffect } from 'react';

export default function ProductList({ products }) {
  useEffect(() => {
    window.scrollTo(0, 0);
  }, []);

  return (
    <div>
      {products.map(product => (
        <ProductCard key={product.id} product={product} />
      ))}
    </div>
  );
}

// Anti-pattern: Using Link without considering scroll behavior
import { Link } from '@inertiajs/react';

function Pagination({ links }) {
  return (
    <div>
      {links.map(link => (
        <Link key={link.label} href={link.url}>
          {link.label}
        </Link>
      ))}
    </div>
  );
}
```

## Good Example

```tsx
// Preserve scroll position when filtering/sorting
import { Link, router } from '@inertiajs/react';

interface ProductListProps {
  products: Product[];
  filters: Filters;
}

export default function ProductList({ products, filters }: ProductListProps) {
  const handleSort = (sortBy: string) => {
    router.get(
      route('products.index'),
      { ...filters, sort: sortBy },
      { preserveScroll: true }
    );
  };

  return (
    <div>
      <select onChange={(e) => handleSort(e.target.value)}>
        <option value="name">Name</option>
        <option value="price">Price</option>
      </select>

      {products.map(product => (
        <ProductCard key={product.id} product={product} />
      ))}

      {/* Preserve scroll for pagination */}
      <Pagination preserveScroll />
    </div>
  );
}

// Pagination component with scroll preservation
function Pagination({ links, preserveScroll = false }) {
  return (
    <div className="flex gap-2">
      {links.map((link, index) => (
        <Link
          key={index}
          href={link.url ?? '#'}
          preserveScroll={preserveScroll}
          className={link.active ? 'font-bold' : ''}
          dangerouslySetInnerHTML={{ __html: link.label }}
        />
      ))}
    </div>
  );
}

// Reset scroll to top for new page visits
import { Link } from '@inertiajs/react';

function Navigation() {
  return (
    <nav>
      {/* Default behavior: scroll resets to top */}
      <Link href={route('dashboard')}>Dashboard</Link>

      {/* Explicit scroll reset */}
      <Link href={route('products.index')} preserveScroll={false}>
        Products
      </Link>
    </nav>
  );
}

// Scroll to specific element after navigation
import { router } from '@inertiajs/react';

function jumpToSection(sectionId: string) {
  router.visit(route('page.show'), {
    onSuccess: () => {
      document.getElementById(sectionId)?.scrollIntoView({
        behavior: 'smooth'
      });
    },
  });
}
```

## Why

Proper scroll management improves user experience significantly:

1. **Context Preservation**: Users don't lose their place when filtering or sorting data
2. **Pagination UX**: Scroll preservation during pagination keeps users oriented in long lists
3. **Form Interactions**: Preserving scroll after form submissions feels more natural
4. **Navigation Clarity**: Scrolling to top on new pages signals a fresh context
5. **Deep Linking**: Proper scroll handling supports linking to specific page sections
6. **Performance Perception**: Smooth scroll behavior makes the app feel more responsive
