---
title: Use Dynamic Imports for Heavy Components
impact: CRITICAL
impactDescription: "30-50% reduction in initial bundle"
tags: split, dynamic-imports, lazy-loading, code-splitting, react
---

## Use Dynamic Imports for Heavy Components

**Impact: CRITICAL (30-50% reduction in initial bundle)**

Heavy components like charts, editors, and complex forms should not be loaded until needed. Dynamic imports allow loading code on-demand, reducing initial bundle size.

## Incorrect

```typescript
// ❌ Bad: All heavy libraries loaded upfront
import { Chart } from 'chart.js'
import ReactQuill from 'react-quill'
import { PDFViewer } from '@react-pdf/renderer'
import MonacoEditor from '@monaco-editor/react'

function Dashboard() {
  const [showChart, setShowChart] = useState(false)

  return (
    <div>
      <button onClick={() => setShowChart(true)}>Show Chart</button>
      {showChart && <Chart data={data} />}
    </div>
  )
}
```

**Problems:**
- Chart.js, React Quill, PDF renderer, and Monaco are all loaded even if never used
- Initial bundle bloated with hundreds of KBs of library code
- Slower Time to Interactive for all users regardless of feature usage
- Heavy parsing blocks the main thread on mobile devices

## Correct

```typescript
// ✅ Good: Lazy load heavy components
import { lazy, Suspense, useState } from 'react'

const Chart = lazy(() => import('./components/Chart'))
const Editor = lazy(() => import('./components/Editor'))
const PDFViewer = lazy(() => import('./components/PDFViewer'))

function Dashboard() {
  const [showChart, setShowChart] = useState(false)
  const [showEditor, setShowEditor] = useState(false)

  return (
    <div>
      <button onClick={() => setShowChart(true)}>Show Chart</button>
      <button onClick={() => setShowEditor(true)}>Show Editor</button>

      {showChart && (
        <Suspense fallback={<ChartSkeleton />}>
          <Chart data={data} />
        </Suspense>
      )}

      {showEditor && (
        <Suspense fallback={<EditorSkeleton />}>
          <Editor />
        </Suspense>
      )}
    </div>
  )
}
```

```typescript
// ✅ Good: Conditional dynamic import for libraries
async function exportToPDF() {
  const { PDFDocument } = await import('pdf-lib')
  const pdfDoc = await PDFDocument.create()
  // ... generate PDF
}

function ExportButton() {
  const [loading, setLoading] = useState(false)

  const handleExport = async () => {
    setLoading(true)
    await exportToPDF()
    setLoading(false)
  }

  return (
    <button onClick={handleExport} disabled={loading}>
      {loading ? 'Generating...' : 'Export PDF'}
    </button>
  )
}
```

```typescript
// ✅ Good: Preload on interaction intent
const HeavyModal = lazy(() => import('./HeavyModal'))

function ModalTrigger() {
  const [isOpen, setIsOpen] = useState(false)

  const preload = () => {
    import('./HeavyModal')
  }

  return (
    <>
      <button
        onClick={() => setIsOpen(true)}
        onMouseEnter={preload}
        onFocus={preload}
      >
        Open Settings
      </button>

      {isOpen && (
        <Suspense fallback={<ModalSkeleton />}>
          <HeavyModal onClose={() => setIsOpen(false)} />
        </Suspense>
      )}
    </>
  )
}
```

```typescript
// ✅ Good: Feature flag based loading
function App({ user }) {
  const AdminPanel = user.isAdmin
    ? lazy(() => import('./AdminPanel'))
    : null

  return (
    <div>
      <MainContent />
      {AdminPanel && (
        <Suspense fallback={<Loading />}>
          <AdminPanel />
        </Suspense>
      )}
    </div>
  )
}
```

**Benefits:**
- Initial bundle can be 50%+ smaller by deferring heavy libraries
- Faster Time to Interactive since only critical code is parsed upfront
- Better user experience on slow connections and mobile devices
- Preloading on hover makes subsequent loads feel instant
- Feature-flag loading avoids shipping admin code to regular users

Libraries that should typically be dynamically imported:
- Chart libraries (Chart.js, Recharts, D3)
- Rich text editors (React Quill, TipTap, Slate)
- Code editors (Monaco, CodeMirror)
- PDF libraries (react-pdf, pdf-lib)
- Date pickers with locales
- Map libraries (Mapbox, Google Maps)
- Markdown renderers

Reference: [Vite Dynamic Import](https://vitejs.dev/guide/features.html#dynamic-import) | [React lazy](https://react.dev/reference/react/lazy)
