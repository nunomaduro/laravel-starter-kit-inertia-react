---
title: Strategic Suspense Boundaries for Lazy Loading
impact: CRITICAL
impactDescription: "Progressive loading, better UX"
tags: split, suspense, lazy, react, boundaries
---

## Strategic Suspense Boundaries for Lazy Loading

**Impact: CRITICAL (Progressive loading, better UX)**

Without proper Suspense boundaries, a single lazy component can block the entire UI. Strategic placement of Suspense boundaries allows parts of the UI to load independently.

## Incorrect

```typescript
// ❌ Bad: Single Suspense at root - entire app shows loading state
function App() {
  return (
    <Suspense fallback={<FullPageLoader />}>
      <Header />
      <Sidebar />
      <MainContent />
      <Footer />
    </Suspense>
  )
}
```

**Problems:**
- If any lazy component is loading, the entire app shows the loading state
- No progressive rendering — users see nothing until everything loads
- Poor perceived performance even on fast connections
- No granular control over loading fallbacks per section

## Correct

```typescript
// ✅ Good: Strategic Suspense boundaries per section
function App() {
  return (
    <div className="app-layout">
      {/* Header loads immediately - not lazy */}
      <Header />

      <div className="main-layout">
        {/* Sidebar has its own boundary */}
        <Suspense fallback={<SidebarSkeleton />}>
          <Sidebar />
        </Suspense>

        {/* Main content independent */}
        <Suspense fallback={<ContentSkeleton />}>
          <MainContent />
        </Suspense>
      </div>

      {/* Footer loads immediately */}
      <Footer />
    </div>
  )
}
```

```typescript
// ✅ Good: Nested Suspense for complex UIs
function Dashboard() {
  return (
    <div className="dashboard">
      <h1>Dashboard</h1>

      <div className="dashboard-grid">
        <Suspense fallback={<WidgetSkeleton />}>
          <StatsWidget />
        </Suspense>

        <Suspense fallback={<WidgetSkeleton />}>
          <ChartWidget />
        </Suspense>

        <Suspense fallback={<WidgetSkeleton />}>
          <RecentActivityWidget />
        </Suspense>
      </div>
    </div>
  )
}
```

```typescript
// ✅ Good: Error Boundaries with Suspense
import { ErrorBoundary } from 'react-error-boundary'

function App() {
  return (
    <ErrorBoundary fallback={<ErrorFallback />}>
      <Suspense fallback={<PageLoader />}>
        <Routes>
          <Route path="/" element={<Home />} />
          <Route path="/dashboard" element={<Dashboard />} />
        </Routes>
      </Suspense>
    </ErrorBoundary>
  )
}

function ErrorFallback({ error, resetErrorBoundary }) {
  return (
    <div className="error-container">
      <h2>Something went wrong</h2>
      <pre>{error.message}</pre>
      <button onClick={resetErrorBoundary}>Try again</button>
    </div>
  )
}
```

```typescript
// ✅ Good: Skeleton components match actual content layout
function ContentSkeleton() {
  return (
    <div className="animate-pulse">
      <div className="h-8 bg-gray-200 rounded w-1/4 mb-4" />
      <div className="h-4 bg-gray-200 rounded w-full mb-2" />
      <div className="h-4 bg-gray-200 rounded w-full mb-2" />
      <div className="h-4 bg-gray-200 rounded w-3/4" />
    </div>
  )
}
```

**Benefits:**
- Parts of UI render independently without blocking each other
- Better perceived performance with skeleton loading states
- Graceful degradation on slow networks
- Error boundaries catch loading failures per section, not globally

Reference: [React Suspense](https://react.dev/reference/react/Suspense) | [react-error-boundary](https://github.com/bvaughn/react-error-boundary)
