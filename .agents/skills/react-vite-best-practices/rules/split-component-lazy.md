---
title: Lazy Load Non-Critical Components
impact: CRITICAL
impactDescription: "20-40% smaller initial bundle"
tags: split, lazy, components, code-splitting, react
---

## Lazy Load Non-Critical Components

**Impact: CRITICAL (20-40% smaller initial bundle)**

Use React.lazy for component-level code splitting to load non-critical UI components on demand.

## Incorrect

```tsx
// ❌ Bad: All components imported eagerly
import { useState } from 'react';
import Header from './components/Header';
import Sidebar from './components/Sidebar';
import MainContent from './components/MainContent';
import SettingsPanel from './components/SettingsPanel';
import NotificationCenter from './components/NotificationCenter';
import UserProfileModal from './components/UserProfileModal';
import HelpDrawer from './components/HelpDrawer';
import FeedbackForm from './components/FeedbackForm';
import AdvancedFilters from './components/AdvancedFilters';
import ExportDialog from './components/ExportDialog';

function Dashboard() {
  const [showSettings, setShowSettings] = useState(false);
  const [showProfile, setShowProfile] = useState(false);

  return (
    <div>
      <Header />
      <Sidebar />
      <MainContent />
      {showSettings && <SettingsPanel />}
      {showProfile && <UserProfileModal />}
    </div>
  );
}
// All modals, drawers, and dialogs loaded even if never opened
```

**Problems:**
- All modal, drawer, and dialog code is downloaded on initial page load
- Users pay the cost of parsing code they may never use
- Larger initial bundle slows Time to Interactive
- Heavy components block the main thread during parsing on mobile

## Correct

```tsx
// ✅ Good: Component-level lazy loading
import { lazy, Suspense, useState } from 'react';
import Header from './components/Header';
import Sidebar from './components/Sidebar';
import MainContent from './components/MainContent';
import { Skeleton } from './components/ui/Skeleton';

const SettingsPanel = lazy(() => import('./components/SettingsPanel'));
const NotificationCenter = lazy(() => import('./components/NotificationCenter'));
const UserProfileModal = lazy(() => import('./components/UserProfileModal'));
const HelpDrawer = lazy(() => import('./components/HelpDrawer'));
const FeedbackForm = lazy(() => import('./components/FeedbackForm'));
const AdvancedFilters = lazy(() => import('./components/AdvancedFilters'));
const ExportDialog = lazy(() => import('./components/ExportDialog'));

function LazyModal({
  isOpen,
  children
}: {
  isOpen: boolean;
  children: React.ReactNode
}) {
  if (!isOpen) return null;

  return (
    <Suspense fallback={<Skeleton className="modal-skeleton" />}>
      {children}
    </Suspense>
  );
}

function Dashboard() {
  const [showSettings, setShowSettings] = useState(false);
  const [showProfile, setShowProfile] = useState(false);

  return (
    <div>
      <Header
        onSettingsClick={() => setShowSettings(true)}
        onProfileClick={() => setShowProfile(true)}
      />
      <Sidebar />
      <MainContent />

      <LazyModal isOpen={showSettings}>
        <SettingsPanel onClose={() => setShowSettings(false)} />
      </LazyModal>

      <LazyModal isOpen={showProfile}>
        <UserProfileModal onClose={() => setShowProfile(false)} />
      </LazyModal>
    </div>
  );
}
```

```tsx
// ✅ Good: Lazy component with preloading
import { lazy, ComponentType, LazyExoticComponent } from 'react';

interface PreloadableComponent<T extends ComponentType<any>>
  extends LazyExoticComponent<T> {
  preload: () => Promise<{ default: T }>;
}

export function lazyWithPreload<T extends ComponentType<any>>(
  factory: () => Promise<{ default: T }>
): PreloadableComponent<T> {
  const Component = lazy(factory) as PreloadableComponent<T>;
  Component.preload = factory;
  return Component;
}

const SettingsPanel = lazyWithPreload(() => import('./components/SettingsPanel'));

function SettingsButton({ onClick }: { onClick: () => void }) {
  return (
    <button
      onClick={onClick}
      onMouseEnter={() => SettingsPanel.preload()}
      onFocus={() => SettingsPanel.preload()}
    >
      Settings
    </button>
  );
}
```

```tsx
// ✅ Good: Lazy loading below-the-fold content with Intersection Observer
import { lazy, Suspense } from 'react';
import { useInView } from 'react-intersection-observer';

const RelatedProducts = lazy(() => import('./components/RelatedProducts'));
const CustomerReviews = lazy(() => import('./components/CustomerReviews'));

function ProductPage({ productId }: { productId: string }) {
  const { ref: reviewsRef, inView: reviewsInView } = useInView({
    triggerOnce: true,
    rootMargin: '200px',
  });

  return (
    <div>
      <ProductHeader productId={productId} />
      <ProductGallery productId={productId} />

      <section ref={reviewsRef}>
        {reviewsInView && (
          <Suspense fallback={<ReviewsSkeleton />}>
            <CustomerReviews productId={productId} />
          </Suspense>
        )}
      </section>
    </div>
  );
}
```

**Benefits:**
- Modals, drawers, and dialogs only load when actually opened
- Faster First Contentful Paint since critical UI renders immediately
- Below-the-fold content loads as users scroll, not on initial page load
- Preloading on hover eliminates perceived delay when opening components
- Better memory usage since components only occupy memory when rendered

| Component Type | Lazy Load? | Reason |
|---------------|------------|--------|
| Modals/Dialogs | Yes | Only shown on interaction |
| Drawers/Panels | Yes | Hidden by default |
| Below-fold content | Yes | Not in initial viewport |
| Tabs (non-default) | Yes | Hidden until selected |
| Header/Navigation | No | Always visible |
| Above-fold content | No | Critical for FCP |

Reference: [React lazy](https://react.dev/reference/react/lazy) | [react-intersection-observer](https://github.com/thebuilder/react-intersection-observer)
