---
title: useImperativeHandle Typing
impact: HIGH
impactDescription: "enables type-safe parent-to-child method calls via refs"
tags: refs, useImperativeHandle, forwardRef, expose-methods
---

## useImperativeHandle Typing

**Impact: HIGH (enables type-safe parent-to-child method calls via refs)**

`useImperativeHandle` lets a child component expose specific methods to a parent via a ref. Define a handle interface for the exposed methods and use `forwardRef` with the handle type to ensure full type safety.

## Incorrect

```tsx
// ❌ Bad
// No handle type — parent has no idea what methods are available
const Timer = forwardRef((props, ref) => {
  const [count, setCount] = useState(0);

  useImperativeHandle(ref, () => ({
    start() { /* ... */ },
    stop() { /* ... */ },
    reset() { /* ... */ },
  }));

  return <div>{count}</div>;
});

// Parent — ref.current is typed as unknown, no autocomplete
function Parent() {
  const timerRef = useRef(null);

  const handleClick = () => {
    // TypeScript doesn't know .start() exists
    timerRef.current?.start();
  };

  return <Timer ref={timerRef} />;
}
```

**Problems:**
- `ref` is untyped — parent gets no autocomplete or type checking on exposed methods
- Typos like `ref.current.stat()` won't be caught at compile time
- No contract between parent and child about what the ref exposes

## Correct

```tsx
// ✅ Good
// 1. Define the handle type — this is the ref's public API
interface TimerHandle {
  start: () => void;
  stop: () => void;
  reset: () => void;
  getCount: () => number;
}

interface TimerProps {
  interval?: number;
}

// 2. Use forwardRef<HandleType, PropsType>
const Timer = forwardRef<TimerHandle, TimerProps>(({ interval = 1000 }, ref) => {
  const [count, setCount] = useState(0);
  const intervalRef = useRef<ReturnType<typeof setInterval> | null>(null);

  // 3. useImperativeHandle exposes only these methods
  useImperativeHandle(ref, () => ({
    start() {
      if (intervalRef.current) return;
      intervalRef.current = setInterval(() => {
        setCount((c) => c + 1);
      }, interval);
    },
    stop() {
      if (intervalRef.current) {
        clearInterval(intervalRef.current);
        intervalRef.current = null;
      }
    },
    reset() {
      setCount(0);
    },
    getCount() {
      return count;
    },
  }), [interval, count]);

  return <div>Count: {count}</div>;
});

Timer.displayName = "Timer";

// 4. Parent uses the handle type for full type safety
function Dashboard() {
  const timerRef = useRef<TimerHandle>(null);

  const handleStart = () => {
    timerRef.current?.start(); // Autocomplete shows start, stop, reset, getCount
  };

  const handleStop = () => {
    timerRef.current?.stop();
  };

  const handleReset = () => {
    timerRef.current?.reset();
  };

  const showCount = () => {
    const count = timerRef.current?.getCount();
    console.log("Current count:", count); // count is number | undefined
  };

  return (
    <div>
      <Timer ref={timerRef} interval={500} />
      <button onClick={handleStart}>Start</button>
      <button onClick={handleStop}>Stop</button>
      <button onClick={handleReset}>Reset</button>
      <button onClick={showCount}>Log Count</button>
    </div>
  );
}
```

**Benefits:**
- `TimerHandle` interface creates a clear contract for the ref's public API
- `forwardRef<TimerHandle, TimerProps>` ensures the ref type flows to the parent
- Parent gets full autocomplete and type checking on `timerRef.current`
- Only explicitly exposed methods are accessible — internal state stays encapsulated

Reference: [React TypeScript Cheatsheet — forwardRef/createRef](https://react-typescript-cheatsheet.netlify.app/docs/basic/getting-started/forward_and_create_ref)
