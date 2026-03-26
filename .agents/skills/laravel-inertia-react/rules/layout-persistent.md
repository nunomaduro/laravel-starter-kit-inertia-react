---
section: layouts
priority: critical
description: Implement persistent layouts that maintain state across navigation
keywords: [layout, persistent, state, performance, remount, nested-layouts]
---

# Persistent Layouts

Persistent layouts maintain state between page visits. Without them, layouts remount on every navigation, losing state like scroll position, form inputs in navigation, or audio/video playback.

## Incorrect

```tsx
// ❌ Layout wrapping in page - remounts on every navigation
import AppLayout from '@/Layouts/AppLayout'

export default function Dashboard() {
  return (
    <AppLayout>
      <h1>Dashboard</h1>
    </AppLayout>
  )
}

// ❌ Layout in _app.jsx - still remounts
function App({ Component, pageProps }) {
  return (
    <AppLayout>
      <Component {...pageProps} />
    </AppLayout>
  )
}
```

**Problem:** Layout remounts on every page change, losing any state.

## Correct

### Using layout Property

```tsx
// resources/js/Layouts/AppLayout.tsx
import { Link, usePage } from '@inertiajs/react'
import { ReactNode } from 'react'

interface Props {
  children: ReactNode
}

export default function AppLayout({ children }: Props) {
  const { auth } = usePage().props as any

  return (
    <div className="min-h-screen bg-gray-100">
      <nav className="bg-white shadow-sm">
        <div className="max-w-7xl mx-auto px-4 py-3">
          <div className="flex justify-between items-center">
            <div className="flex space-x-4">
              <Link href="/" className="font-bold">
                Logo
              </Link>
              <Link href="/dashboard">Dashboard</Link>
              <Link href="/posts">Posts</Link>
            </div>
            <span>{auth.user?.name}</span>
          </div>
        </div>
      </nav>

      <main className="max-w-7xl mx-auto py-6 px-4">
        {children}
      </main>
    </div>
  )
}
```

```tsx
// resources/js/Pages/Dashboard.tsx
import AppLayout from '@/Layouts/AppLayout'
import { ReactNode } from 'react'

export default function Dashboard() {
  return (
    <div>
      <h1 className="text-2xl font-bold">Dashboard</h1>
      {/* Page content */}
    </div>
  )
}

// Assign persistent layout
Dashboard.layout = (page: ReactNode) => <AppLayout>{page}</AppLayout>
```

### TypeScript Declaration

```tsx
// resources/js/types/inertia.d.ts
import { ReactNode } from 'react'

declare module '@inertiajs/react' {
  interface PageComponent {
    layout?: (page: ReactNode) => ReactNode
  }
}
```

### Default Layout in app.tsx

```tsx
// resources/js/app.tsx
import { createInertiaApp } from '@inertiajs/react'
import { createRoot } from 'react-dom/client'
import AppLayout from './Layouts/AppLayout'

createInertiaApp({
  resolve: (name) => {
    const pages = import.meta.glob('./Pages/**/*.tsx', { eager: true })
    const page = pages[`./Pages/${name}.tsx`] as any

    // Set default layout if page doesn't have one
    page.default.layout = page.default.layout || ((page: ReactNode) =>
      <AppLayout>{page}</AppLayout>
    )

    return page
  },
  setup({ el, App, props }) {
    createRoot(el!).render(<App {...props} />)
  },
})
```

### Nested Layouts

```tsx
// resources/js/Layouts/SettingsLayout.tsx
import { Link } from '@inertiajs/react'
import { ReactNode } from 'react'

interface Props {
  children: ReactNode
}

export default function SettingsLayout({ children }: Props) {
  return (
    <div className="flex">
      <aside className="w-64 border-r p-4">
        <nav className="space-y-2">
          <Link href="/settings/profile">Profile</Link>
          <Link href="/settings/password">Password</Link>
          <Link href="/settings/notifications">Notifications</Link>
        </nav>
      </aside>
      <main className="flex-1 p-6">{children}</main>
    </div>
  )
}
```

```tsx
// resources/js/Pages/Settings/Profile.tsx
import AppLayout from '@/Layouts/AppLayout'
import SettingsLayout from '@/Layouts/SettingsLayout'
import { ReactNode } from 'react'

export default function Profile() {
  return (
    <div>
      <h2>Profile Settings</h2>
      {/* Content */}
    </div>
  )
}

// Nested persistent layouts
Profile.layout = (page: ReactNode) => (
  <AppLayout>
    <SettingsLayout>{page}</SettingsLayout>
  </AppLayout>
)
```

### Conditional Layouts

```tsx
// resources/js/Pages/Login.tsx
import GuestLayout from '@/Layouts/GuestLayout'
import { ReactNode } from 'react'

export default function Login() {
  return (
    <div>
      <h1>Login</h1>
      {/* Login form */}
    </div>
  )
}

// Different layout for auth pages
Login.layout = (page: ReactNode) => <GuestLayout>{page}</GuestLayout>
```

### Layout Without Persistence

```tsx
// When you DON'T want persistence (rare)
export default function SpecialPage() {
  return <div>No layout</div>
}

// Explicitly no layout
SpecialPage.layout = (page: ReactNode) => page
```

## Benefits

- State preserved between page navigations
- Audio/video continues playing
- Form inputs in navigation preserved
- Scroll position in sidebars maintained
- Better perceived performance
