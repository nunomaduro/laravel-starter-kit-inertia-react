---
title: useRef Hook Typing
impact: CRITICAL
impactDescription: "prevents null reference errors and unnecessary null checks"
tags: hook, useRef, ref, DOM, mutable
---

## useRef Hook Typing

**Impact: CRITICAL (prevents null reference errors and unnecessary null checks)**

useRef has two distinct use cases with different typing: DOM element refs (nullable) and mutable value storage (non-nullable). Using the wrong pattern causes type errors or requires unnecessary null checks.

## Incorrect

```tsx
// ❌ Bad
// Missing element type
const inputRef = useRef(null)
inputRef.current.focus()  // Error: possibly null

// Wrong initial value for DOM ref
const inputRef = useRef<HTMLInputElement>()  // undefined, not null
<input ref={inputRef} />  // Type error

// Treating mutable ref as nullable
const countRef = useRef<number>(0)
if (countRef.current !== null) {  // Unnecessary check
  countRef.current++
}
```

**Problems:**
- Missing element type generic means `current` has no useful properties
- Using `undefined` instead of `null` for DOM refs causes type incompatibility
- Unnecessary null checks on mutable refs add noise and reduce readability

## Correct

```tsx
// ✅ Good
// DOM Element Refs - pass null, type the element
// const inputRef = useRef<HTMLInputElement>(null)
// const buttonRef = useRef<HTMLButtonElement>(null)
// const divRef = useRef<HTMLDivElement>(null)

function Form() {
  const inputRef = useRef<HTMLInputElement>(null)

  const focusInput = () => {
    // Optional chaining because ref might not be attached yet
    inputRef.current?.focus()
  }

  useEffect(() => {
    inputRef.current?.focus()
  }, [])

  return <input ref={inputRef} />
}

// Common DOM Element Types (standalone declarations for reference)
// const inputRef = useRef<HTMLInputElement>(null)
// const textareaRef = useRef<HTMLTextAreaElement>(null)
// const selectRef = useRef<HTMLSelectElement>(null)
// const formRef = useRef<HTMLFormElement>(null)
// const divRef = useRef<HTMLDivElement>(null)
// const videoRef = useRef<HTMLVideoElement>(null)
// const canvasRef = useRef<HTMLCanvasElement>(null)
// const svgRef = useRef<SVGSVGElement>(null)

// Mutable Value Refs - pass actual initial value
function Timer() {
  const intervalRef = useRef<number | undefined>(undefined)
  const countRef = useRef(0)  // Inferred as MutableRefObject<number>

  useEffect(() => {
    intervalRef.current = window.setInterval(() => {
      countRef.current++  // No null check needed
    }, 1000)

    return () => {
      clearInterval(intervalRef.current)
    }
  }, [])
}

// Storing Previous Value
function usePrevious<T>(value: T): T | undefined {
  const ref = useRef<T | undefined>(undefined)

  useEffect(() => {
    ref.current = value
  }, [value])

  return ref.current
}

function Counter({ count }: { count: number }) {
  const prevCount = usePrevious(count)

  return (
    <p>
      Current: {count}, Previous: {prevCount ?? 'none'}
    </p>
  )
}

// Storing Callbacks
function useEventCallback<T extends (...args: never[]) => unknown>(fn: T): T {
  const ref = useRef<T>(fn)

  useEffect(() => {
    ref.current = fn
  }, [fn])

  return useCallback(
    ((...args) => ref.current(...args)) as T,
    []
  )
}

// Multiple Refs (Callback Refs)
interface Item { id: string; name: string }

function List({ items }: { items: Item[] }) {
  const itemRefs = useRef<Map<string, HTMLLIElement>>(new Map())

  const setRef = (id: string) => (el: HTMLLIElement | null) => {
    if (el) {
      itemRefs.current.set(id, el)
    } else {
      itemRefs.current.delete(id)
    }
  }

  const scrollToItem = (id: string) => {
    itemRefs.current.get(id)?.scrollIntoView()
  }

  return (
    <ul>
      {items.map(item => (
        <li key={item.id} ref={setRef(item.id)}>
          {item.name}
        </li>
      ))}
    </ul>
  )
}
```

**Benefits:**
- DOM refs with `null` initial value create `RefObject<T>` with nullable current
- Mutable refs with actual initial value create `MutableRefObject<T>` with non-nullable current
- Optional chaining on DOM refs handles the "not yet attached" state cleanly
- Callback refs enable dynamic ref management for lists of elements
- Ref-stored callbacks avoid stale closure issues without triggering re-renders

Reference: [React TypeScript Cheatsheet](https://react-typescript-cheatsheet.netlify.app)
