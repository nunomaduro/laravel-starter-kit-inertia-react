---
section: navigation
priority: critical
description: Use router for programmatic navigation and form submissions
keywords: [router, programmatic, navigation, visit, get, post, put, delete]
---

# Programmatic Navigation

Use Inertia's router for programmatic navigation, redirects, and form submissions outside of Link components.

## Bad Example

```tsx
// Anti-pattern: Using window.location
const handleClick = () => {
  window.location.href = '/dashboard';
};

// Anti-pattern: Using browser history directly
const goBack = () => {
  window.history.back();
};

// Anti-pattern: Using fetch for navigation
const handleSubmit = async () => {
  const response = await fetch('/api/users', {
    method: 'POST',
    body: JSON.stringify(data),
  });
  if (response.ok) {
    window.location.href = '/users';
  }
};
```

## Good Example

```tsx
// resources/js/Pages/Users/Create.tsx
import { router } from '@inertiajs/react';
import { FormEventHandler } from 'react';

export default function CreateUser() {
  const handleCancel = () => {
    // Simple navigation
    router.visit(route('users.index'));
  };

  const handleCreate = (userData: UserData) => {
    // POST request with callbacks
    router.post(route('users.store'), userData, {
      onSuccess: () => {
        // Redirect happens automatically from Laravel
        // But you can do additional client-side logic here
      },
      onError: (errors) => {
        console.error('Validation failed:', errors);
      },
    });
  };

  return (
    <div>
      <UserForm onSubmit={handleCreate} />
      <button onClick={handleCancel}>Cancel</button>
    </div>
  );
}

// Advanced router usage examples
import { router } from '@inertiajs/react';

// GET request with query parameters
function searchUsers(query: string) {
  router.get(route('users.index'), { search: query }, {
    preserveState: true,
    preserveScroll: true,
  });
}

// POST request
function createPost(data: PostData) {
  router.post(route('posts.store'), data, {
    onSuccess: (page) => {
      console.log('Post created!', page.props.flash);
    },
  });
}

// PUT request for updates
function updateUser(userId: number, data: UserData) {
  router.put(route('users.update', userId), data, {
    preserveScroll: true,
  });
}

// PATCH for partial updates
function togglePublished(postId: number) {
  router.patch(route('posts.toggle-published', postId));
}

// DELETE request
function deleteUser(userId: number) {
  if (confirm('Are you sure you want to delete this user?')) {
    router.delete(route('users.destroy', userId), {
      onSuccess: () => {
        // User deleted, page will be re-rendered
      },
    });
  }
}

// Reload current page
function refreshData() {
  router.reload();
}

// Partial reload - only fetch specific props
function refreshNotifications() {
  router.reload({ only: ['notifications'] });
}

// Navigate with all options
function advancedNavigation() {
  router.visit(route('dashboard'), {
    method: 'get',
    data: { tab: 'analytics' },
    replace: true,                    // Replace history entry
    preserveState: true,              // Keep component state
    preserveScroll: true,             // Keep scroll position
    only: ['stats'],                  // Partial reload
    headers: { 'X-Custom': 'value' }, // Custom headers
    onBefore: (visit) => {
      // Return false to cancel
      return confirm('Navigate away?');
    },
    onStart: (visit) => {
      console.log('Navigation started');
    },
    onProgress: (progress) => {
      console.log(`${progress.percentage}% loaded`);
    },
    onSuccess: (page) => {
      console.log('Navigation successful', page);
    },
    onError: (errors) => {
      console.error('Errors:', errors);
    },
    onCancel: () => {
      console.log('Navigation cancelled');
    },
    onFinish: () => {
      console.log('Navigation finished (success or error)');
    },
  });
}

// Using router in event handlers
function ProductActions({ product }: { product: Product }) {
  const handleDuplicate = () => {
    router.post(route('products.duplicate', product.id), {}, {
      onSuccess: () => {
        // Will redirect to new product edit page
      },
    });
  };

  const handleArchive = () => {
    router.patch(
      route('products.archive', product.id),
      {},
      {
        preserveScroll: true,
        onSuccess: () => {
          // Product archived, list will update
        },
      }
    );
  };

  const handleExport = () => {
    // For file downloads, use regular window.location
    window.location.href = route('products.export', product.id);
  };

  return (
    <div className="flex gap-2">
      <button onClick={handleDuplicate}>Duplicate</button>
      <button onClick={handleArchive}>Archive</button>
      <button onClick={handleExport}>Export PDF</button>
    </div>
  );
}

// Conditional navigation based on form state
function FormWithNavigation() {
  const [hasChanges, setHasChanges] = useState(false);

  const navigateAway = (url: string) => {
    if (hasChanges) {
      if (confirm('You have unsaved changes. Continue?')) {
        router.visit(url);
      }
    } else {
      router.visit(url);
    }
  };

  return (
    <div>
      <button onClick={() => navigateAway(route('home'))}>
        Go Home
      </button>
    </div>
  );
}
```

## Why

Programmatic navigation with Inertia's router provides:

1. **Consistent Behavior**: Same SPA navigation as Link components
2. **Full Control**: Access to all visit options and lifecycle callbacks
3. **HTTP Methods**: Support for GET, POST, PUT, PATCH, DELETE
4. **Event Handling**: Navigate from button clicks, form submissions, etc.
5. **Conditional Logic**: Navigate based on validation or user confirmation
6. **Progress Tracking**: Same loading indicator as Link navigation
7. **State Management**: Preserve scroll, state, and partial reload support
8. **Error Handling**: Proper callback structure for success and error states
