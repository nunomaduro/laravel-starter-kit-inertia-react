---
section: page-components
priority: high
description: Reload only specific props with only/except options for better performance
keywords: [partial, reload, props, performance, only, except]
---

# Page Partial Reloads

Use partial reloads to refresh only specific props without reloading the entire page, improving performance and user experience.

## Bad Example

```tsx
// Anti-pattern: Full page reload for updating single data
import { router } from '@inertiajs/react';

export default function Dashboard({ stats, notifications, recentActivity }) {
  const refreshAll = () => {
    // This reloads ALL props, even unchanged ones
    router.reload();
  };

  return (
    <div>
      <button onClick={refreshAll}>Refresh Notifications</button>
      <NotificationList notifications={notifications} />
      <StatsGrid stats={stats} />
      <ActivityFeed activities={recentActivity} />
    </div>
  );
}

// Anti-pattern: Making separate API calls
const refreshNotifications = async () => {
  const response = await fetch('/api/notifications');
  const data = await response.json();
  setNotifications(data); // Mixing Inertia with manual state
};
```

## Good Example

```tsx
// resources/js/Pages/Dashboard.tsx
import { router } from '@inertiajs/react';
import { useState } from 'react';

interface DashboardProps {
  stats: Stat[];
  notifications: Notification[];
  recentActivity: Activity[];
}

export default function Dashboard({
  stats,
  notifications,
  recentActivity
}: DashboardProps) {
  const [isRefreshing, setIsRefreshing] = useState(false);

  // Reload only notifications
  const refreshNotifications = () => {
    router.reload({
      only: ['notifications'],
      onStart: () => setIsRefreshing(true),
      onFinish: () => setIsRefreshing(false),
    });
  };

  // Reload multiple specific props
  const refreshDashboardData = () => {
    router.reload({
      only: ['stats', 'recentActivity'],
      preserveScroll: true,
    });
  };

  // Reload everything except heavy data
  const refreshWithoutActivity = () => {
    router.reload({
      except: ['recentActivity'],
    });
  };

  return (
    <div>
      <div className="flex gap-4">
        <button
          onClick={refreshNotifications}
          disabled={isRefreshing}
        >
          {isRefreshing ? 'Refreshing...' : 'Refresh Notifications'}
        </button>
        <button onClick={refreshDashboardData}>
          Refresh Stats
        </button>
      </div>

      <NotificationList
        notifications={notifications}
        isLoading={isRefreshing}
      />
      <StatsGrid stats={stats} />
      <ActivityFeed activities={recentActivity} />
    </div>
  );
}

// Laravel Controller with lazy props for optimization
// app/Http/Controllers/DashboardController.php
/*
use Inertia\Inertia;

public function index()
{
    return Inertia::render('Dashboard', [
        'stats' => fn () => $this->getStats(),
        'notifications' => fn () => auth()->user()->unreadNotifications,
        'recentActivity' => Inertia::lazy(fn () => $this->getRecentActivity()),
    ]);
}
*/

// Polling with partial reloads
import { useEffect } from 'react';

function NotificationBell({ count }: { count: number }) {
  useEffect(() => {
    const interval = setInterval(() => {
      router.reload({ only: ['notificationCount'] });
    }, 30000); // Refresh every 30 seconds

    return () => clearInterval(interval);
  }, []);

  return <span className="badge">{count}</span>;
}
```

## Why

Partial reloads are essential for building efficient Inertia applications:

1. **Performance**: Only transfer and process the data that actually changed
2. **Bandwidth**: Reduce network payload, especially important for mobile users
3. **Server Load**: Laravel only evaluates the requested props when using closures
4. **UX Continuity**: Other parts of the page remain stable during updates
5. **Real-time Features**: Enable efficient polling without full page refreshes
6. **Lazy Loading**: Combine with Inertia::lazy() for on-demand data loading
