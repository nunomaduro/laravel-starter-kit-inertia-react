---
section: page-components
priority: critical
description: Standard structure for Inertia page components with TypeScript typing and layout assignment
keywords: [inertia, page, component, typescript, layout]
---

# Page Component Structure

Inertia page components should follow a consistent structure with proper TypeScript typing, clear separation of concerns, and explicit layout assignment.

## Bad Example

```tsx
// Anti-pattern: Unstructured component without typing
export default function Dashboard(props) {
  return (
    <div>
      <h1>Dashboard</h1>
      <p>Welcome {props.user.name}</p>
      <div>
        {props.stats.map(stat => (
          <div key={stat.id}>{stat.value}</div>
        ))}
      </div>
    </div>
  );
}
```

## Good Example

```tsx
// resources/js/Pages/Dashboard.tsx
import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import StatsGrid from '@/Components/StatsGrid';

interface Stat {
  id: number;
  label: string;
  value: number;
  change: number;
}

interface DashboardProps extends PageProps {
  stats: Stat[];
  recentActivity: Activity[];
}

export default function Dashboard({ auth, stats, recentActivity }: DashboardProps) {
  return (
    <>
      <Head title="Dashboard" />

      <div className="py-12">
        <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
          <h1 className="text-2xl font-semibold text-gray-900">
            Welcome back, {auth.user.name}
          </h1>

          <StatsGrid stats={stats} />

          <RecentActivityList activities={recentActivity} />
        </div>
      </div>
    </>
  );
}

Dashboard.layout = (page: React.ReactNode) => (
  <AuthenticatedLayout children={page} />
);
```

## Why

A well-structured page component provides several benefits:

1. **Type Safety**: TypeScript interfaces catch prop mismatches at compile time rather than runtime
2. **Maintainability**: Clear structure makes it easy to understand what data the page expects
3. **Reusability**: Extracting sub-components keeps pages focused on composition
4. **SEO**: Using the Head component ensures proper meta tags for each page
5. **Layout Consistency**: Explicit layout assignment prevents layout-related bugs
6. **Developer Experience**: Consistent patterns across pages reduce cognitive load
