---
title: Custom Hooks Typing
impact: CRITICAL
impactDescription: "ensures clear hook contracts with proper return types and parameters"
tags: hook, custom, return-type, generic
---

## Custom Hooks Typing

**Impact: CRITICAL (ensures clear hook contracts with proper return types and parameters)**

Creating properly typed custom hooks with clear return types and parameters. Explicit typing makes hook contracts clear to consumers and catches errors early.

## Incorrect

```tsx
// ❌ Bad
// No return type - callers don't know what to expect
function useUser(id) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    setLoading(true);
    fetchUser(id).then(setUser).finally(() => setLoading(false));
  }, [id]);

  return { user, loading };
}

// Using 'any' for state
function useLocalStorage(key: string, initialValue: any) {
  const [value, setValue] = useState(initialValue);
  return [value, setValue];
}

// Inconsistent return type (object vs tuple)
function useToggle(initial: boolean) {
  const [value, setValue] = useState(initial);
  const toggle = () => setValue(v => !v);
  return { value, toggle, setValue };
}

// Missing cleanup and error handling
function useEventListener(event: string, handler: any) {
  useEffect(() => {
    window.addEventListener(event, handler);
  }, [event, handler]);
}
```

**Problems:**
- Missing parameter types result in implicit `any`
- No return type annotation makes the hook contract unclear
- Using `any` for state removes all type safety
- Missing effect cleanup causes memory leaks
- Missing error handling in async hooks leads to uncaught errors

## Correct

```tsx
// ✅ Good
import { useState, useEffect, useCallback, useRef, useMemo } from 'react';

// Hook with clear parameter and return types
interface User {
  id: string;
  name: string;
  email: string;
}

interface UseUserResult {
  user: User | null;
  loading: boolean;
  error: Error | null;
  refetch: () => Promise<void>;
}

function useUser(userId: string | null): UseUserResult {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<Error | null>(null);

  const fetchUserData = useCallback(async () => {
    if (!userId) {
      setUser(null);
      return;
    }

    setLoading(true);
    setError(null);

    try {
      const response = await fetch(`/api/users/${userId}`);
      if (!response.ok) {
        throw new Error('Failed to fetch user');
      }
      const data: User = await response.json();
      setUser(data);
    } catch (err) {
      setError(err instanceof Error ? err : new Error('Unknown error'));
      setUser(null);
    } finally {
      setLoading(false);
    }
  }, [userId]);

  useEffect(() => {
    fetchUserData();
  }, [fetchUserData]);

  return { user, loading, error, refetch: fetchUserData };
}

// Generic hook with type parameter
function useLocalStorage<T>(
  key: string,
  initialValue: T
): [T, (value: T | ((prev: T) => T)) => void, () => void] {
  const [storedValue, setStoredValue] = useState<T>(() => {
    try {
      const item = localStorage.getItem(key);
      return item ? (JSON.parse(item) as T) : initialValue;
    } catch {
      return initialValue;
    }
  });

  const setValue = useCallback(
    (value: T | ((prev: T) => T)) => {
      setStoredValue((prev) => {
        const valueToStore = value instanceof Function ? value(prev) : value;
        localStorage.setItem(key, JSON.stringify(valueToStore));
        return valueToStore;
      });
    },
    [key]
  );

  const removeValue = useCallback(() => {
    localStorage.removeItem(key);
    setStoredValue(initialValue);
  }, [key, initialValue]);

  return [storedValue, setValue, removeValue];
}

// Usage with explicit type
const [theme, setTheme] = useLocalStorage<'light' | 'dark'>('theme', 'light');

// Tuple return type for toggle hook
function useToggle(
  initialValue: boolean = false
): [boolean, () => void, (value: boolean) => void] {
  const [value, setValue] = useState(initialValue);

  const toggle = useCallback(() => {
    setValue((v) => !v);
  }, []);

  const setValueDirectly = useCallback((newValue: boolean) => {
    setValue(newValue);
  }, []);

  return [value, toggle, setValueDirectly];
}

// Event listener hook with proper typing
function useEventListener<K extends keyof WindowEventMap>(
  eventName: K,
  handler: (event: WindowEventMap[K]) => void,
  element: Window | null = window,
  options?: AddEventListenerOptions
): void {
  const savedHandler = useRef(handler);

  useEffect(() => {
    savedHandler.current = handler;
  }, [handler]);

  useEffect(() => {
    if (!element) return;

    const eventListener = (event: WindowEventMap[K]) => {
      savedHandler.current(event);
    };

    element.addEventListener(eventName, eventListener, options);

    return () => {
      element.removeEventListener(eventName, eventListener, options);
    };
  }, [eventName, element, options]);
}

// Async hook with proper state machine
type AsyncState<T> =
  | { status: 'idle'; data: null; error: null }
  | { status: 'loading'; data: null; error: null }
  | { status: 'success'; data: T; error: null }
  | { status: 'error'; data: null; error: Error };

interface UseAsyncResult<T> {
  state: AsyncState<T>;
  execute: () => Promise<T | null>;
  reset: () => void;
}

function useAsync<T>(
  asyncFunction: () => Promise<T>,
  immediate: boolean = true
): UseAsyncResult<T> {
  const [state, setState] = useState<AsyncState<T>>({
    status: 'idle',
    data: null,
    error: null,
  });

  const execute = useCallback(async (): Promise<T | null> => {
    setState({ status: 'loading', data: null, error: null });

    try {
      const data = await asyncFunction();
      setState({ status: 'success', data, error: null });
      return data;
    } catch (error) {
      const err = error instanceof Error ? error : new Error('Unknown error');
      setState({ status: 'error', data: null, error: err });
      return null;
    }
  }, [asyncFunction]);

  const reset = useCallback(() => {
    setState({ status: 'idle', data: null, error: null });
  }, []);

  useEffect(() => {
    if (immediate) {
      execute();
    }
  }, [execute, immediate]);

  return { state, execute, reset };
}

// Hook returning readonly tuple (const assertion)
function useCounter(initialValue: number = 0) {
  const [count, setCount] = useState(initialValue);

  const increment = useCallback(() => setCount((c) => c + 1), []);
  const decrement = useCallback(() => setCount((c) => c - 1), []);
  const reset = useCallback(() => setCount(initialValue), [initialValue]);

  return [count, { increment, decrement, reset }] as const;
}

// Usage - tuple is readonly [number, { increment, decrement, reset }]
const [count, { increment, decrement, reset }] = useCounter(0);
```

**Benefits:**
- Explicit return types make hook contracts clear to consumers
- Generic parameters enable type-safe reuse across different data types
- Tuple returns for simple hooks, objects for complex ones
- Proper cleanup in effects prevents memory leaks
- Error states in async hooks prevent uncaught runtime errors
- Ref patterns store values that should not trigger re-renders

Reference: [React TypeScript Cheatsheet](https://react-typescript-cheatsheet.netlify.app)
