---
title: Callback Ref Pattern
impact: HIGH
impactDescription: "eliminates extra render cycles for DOM measurement"
tags: refs, callback-ref, DOM-measurement
---

## Callback Ref Pattern

**Impact: HIGH (eliminates extra render cycles for DOM measurement)**

Callback refs fire the moment a DOM node is attached or detached. This avoids the extra render cycle caused by `useRef` + `useEffect` when you need to measure or interact with a DOM element immediately.

## Incorrect

```tsx
// ❌ Bad
function MeasuredBox() {
  const boxRef = useRef<HTMLDivElement>(null);
  const [height, setHeight] = useState(0);

  // Extra render cycle: component renders → ref attaches → effect runs → state updates → re-render
  useEffect(() => {
    if (boxRef.current) {
      setHeight(boxRef.current.getBoundingClientRect().height);
    }
  }, []);

  return (
    <div>
      <div ref={boxRef}>Content that needs measuring</div>
      <p>Height: {height}px</p>
    </div>
  );
}
```

**Problems:**
- `useEffect` runs after paint, causing a visible flash when height updates
- Requires an extra render cycle: mount, then measure, then re-render with measurement
- Does not react to the element being conditionally shown/hidden
- If the element mounts later (e.g., after data loads), the effect won't re-run

## Correct

```tsx
// ✅ Good
function MeasuredBox() {
  const [height, setHeight] = useState(0);

  // Callback ref — fires immediately when the DOM node attaches
  const measuredRef = useCallback((node: HTMLDivElement | null) => {
    if (node !== null) {
      setHeight(node.getBoundingClientRect().height);
    }
  }, []);

  return (
    <div>
      <div ref={measuredRef}>Content that needs measuring</div>
      <p>Height: {height}px</p>
    </div>
  );
}

// Callback ref for conditional elements
function Tooltip({ show, text }: { show: boolean; text: string }) {
  const [position, setPosition] = useState({ top: 0, left: 0 });

  const tooltipRef = useCallback((node: HTMLDivElement | null) => {
    if (node) {
      const rect = node.getBoundingClientRect();
      setPosition({
        top: rect.top - rect.height - 8,
        left: rect.left + rect.width / 2,
      });
    }
  }, []);

  if (!show) return null;

  // Callback ref fires each time this element mounts
  return (
    <div ref={tooltipRef} style={{ position: "fixed", ...position }}>
      {text}
    </div>
  );
}

// Callback ref with cleanup (React 19+)
function ResizeObserverBox() {
  const [width, setWidth] = useState(0);

  const observerRef = useCallback((node: HTMLDivElement | null) => {
    if (node) {
      const observer = new ResizeObserver(([entry]) => {
        setWidth(entry.contentRect.width);
      });
      observer.observe(node);

      // Return cleanup function (React 19+)
      return () => observer.disconnect();
    }
  }, []);

  return (
    <div ref={observerRef}>
      <p>Width: {width}px</p>
    </div>
  );
}
```

**Benefits:**
- No extra render cycle — measurement happens the instant the node attaches
- Automatically fires when conditionally rendered elements mount or unmount
- Works with elements that appear after async data loads
- Cleanup function support (React 19+) enables attaching observers cleanly

Reference: [React TypeScript Cheatsheet — useRef](https://react-typescript-cheatsheet.netlify.app/docs/basic/getting-started/hooks#useref)
