---
title: useRef for DOM Elements
impact: HIGH
impactDescription: "prevents null access crashes and enables precise DOM typing"
tags: refs, useRef, DOM, HTMLElement
---

## useRef for DOM Elements

**Impact: HIGH (prevents null access crashes and enables precise DOM typing)**

When using `useRef` for DOM elements, pass `null` as the initial value and use the most specific HTML element type. This returns a `RefObject` with a read-only `.current` that is `null` until React attaches the element.

## Incorrect

```tsx
// ❌ Bad
function BadForm() {
  // Missing type parameter — .current is undefined, not null
  const inputRef = useRef();

  // Too generic — loses element-specific properties like .value, .focus()
  const anotherRef = useRef<HTMLElement>(null);

  // Missing null initial value — returns MutableRefObject, not RefObject
  const divRef = useRef<HTMLDivElement>();

  const handleClick = () => {
    // No null check — crashes if element isn't mounted
    console.log(inputRef.current.value);
    inputRef.current.focus();
  };

  return (
    <div ref={divRef}>
      <input ref={inputRef} />
      <button onClick={handleClick}>Focus</button>
    </div>
  );
}
```

**Problems:**
- `useRef()` without a type defaults to `undefined`, not `null` — won't match React's ref assignment
- `HTMLElement` is too generic and lacks element-specific properties like `value` on inputs
- Missing `null` initial value creates a `MutableRefObject` instead of `RefObject`
- No null check before accessing `.current` causes runtime crashes

## Correct

```tsx
// ✅ Good
function SearchForm() {
  // Specific element types — pass null to get RefObject
  const inputRef = useRef<HTMLInputElement>(null);
  const formRef = useRef<HTMLFormElement>(null);
  const canvasRef = useRef<HTMLCanvasElement>(null);

  useEffect(() => {
    // Always check for null before accessing .current
    if (inputRef.current) {
      inputRef.current.focus(); // .focus() is available on HTMLInputElement
    }
  }, []);

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    if (inputRef.current) {
      console.log(inputRef.current.value); // .value is typed
      inputRef.current.select(); // .select() is typed
    }
  };

  const drawCanvas = () => {
    if (canvasRef.current) {
      // .getContext() is available because we used HTMLCanvasElement
      const ctx = canvasRef.current.getContext("2d");
      if (ctx) {
        ctx.fillRect(0, 0, 100, 100);
      }
    }
  };

  return (
    <form ref={formRef} onSubmit={handleSubmit}>
      <input ref={inputRef} type="search" />
      <canvas ref={canvasRef} />
      <button type="submit">Search</button>
    </form>
  );
}

// Using a ref for a video element
function VideoPlayer({ src }: { src: string }) {
  const videoRef = useRef<HTMLVideoElement>(null);

  const play = () => videoRef.current?.play();
  const pause = () => videoRef.current?.pause();

  return (
    <div>
      <video ref={videoRef} src={src} />
      <button onClick={play}>Play</button>
      <button onClick={pause}>Pause</button>
    </div>
  );
}
```

**Benefits:**
- `useRef<HTMLInputElement>(null)` returns `RefObject<HTMLInputElement>` with read-only `.current`
- Element-specific methods and properties (`value`, `focus`, `select`, `getContext`) are fully typed
- Null checks prevent runtime crashes when the element isn't mounted
- Optional chaining (`ref.current?.method()`) provides concise null-safe access

Reference: [React TypeScript Cheatsheet — useRef](https://react-typescript-cheatsheet.netlify.app/docs/basic/getting-started/hooks#useref)
