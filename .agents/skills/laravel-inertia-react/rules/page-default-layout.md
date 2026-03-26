---
section: page-components
priority: critical
description: Assign layouts using static layout property for persistent layouts
keywords: [layout, persistent, page, assignment, property]
---

# Page Default Layout

Assign layouts to pages using the static layout property pattern to enable persistent layouts and avoid unnecessary re-renders.

## Bad Example

```tsx
// Anti-pattern: Wrapping layout inside the component
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Dashboard({ auth }) {
  return (
    <AuthenticatedLayout user={auth.user}>
      <div className="py-12">
        <h1>Dashboard</h1>
      </div>
    </AuthenticatedLayout>
  );
}

// Anti-pattern: Using a wrapper component
function DashboardWithLayout(props) {
  return (
    <AuthenticatedLayout>
      <Dashboard {...props} />
    </AuthenticatedLayout>
  );
}

export default DashboardWithLayout;
```

## Good Example

```tsx
// resources/js/Pages/Dashboard.tsx
import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';

export default function Dashboard({ auth }: PageProps) {
  return (
    <>
      <Head title="Dashboard" />
      <div className="py-12">
        <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
          <h1>Welcome, {auth.user.name}</h1>
        </div>
      </div>
    </>
  );
}

Dashboard.layout = (page: React.ReactNode) => (
  <AuthenticatedLayout children={page} />
);

// For TypeScript, extend the function type
// resources/js/types/index.d.ts
import { ReactNode } from 'react';

declare module '@inertiajs/react' {
  interface PageComponent<P = {}> {
    (props: P): ReactNode;
    layout?: (page: ReactNode) => ReactNode;
  }
}

// Alternative: Typed page component
import { PageComponent } from '@/types';

const Dashboard: PageComponent<PageProps> = ({ auth }) => {
  return (
    <>
      <Head title="Dashboard" />
      <div>Dashboard content</div>
    </>
  );
};

Dashboard.layout = (page) => <AuthenticatedLayout children={page} />;

export default Dashboard;
```

## Why

Using the static layout property pattern is important because:

1. **Persistent Layouts**: Layouts remain mounted between page visits, preserving their state
2. **Performance**: Audio players, video, scroll position, and form state persist across navigation
3. **Fewer Re-renders**: The layout doesn't unmount/remount on every page change
4. **Cleaner Components**: Page components focus on their content, not layout wrapping
5. **Consistency**: All pages follow the same pattern, making the codebase predictable
6. **Flexibility**: Easy to switch layouts or use conditional layouts per page
