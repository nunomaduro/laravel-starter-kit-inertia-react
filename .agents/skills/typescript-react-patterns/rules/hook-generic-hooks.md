---
title: Generic Hooks Typing
impact: CRITICAL
impactDescription: "enables flexible, reusable hooks without losing type safety"
tags: hook, generic, type-parameter, constraint
---

## Generic Hooks Typing

**Impact: CRITICAL (enables flexible, reusable hooks without losing type safety)**

Creating flexible, reusable hooks with TypeScript generics. Well-designed generics preserve type information throughout the hook without requiring explicit type arguments.

## Incorrect

```tsx
// ❌ Bad
// Using 'any' instead of generics
function useFetch(url: string): { data: any; loading: boolean } {
  const [data, setData] = useState<any>(null);
  // ...
  return { data, loading };
}

// Not constraining generic types when needed
function useList<T>(initial: T[]) {
  const [items, setItems] = useState(initial);

  const add = (item: T) => setItems([...items, item]);
  const remove = (item: T) => setItems(items.filter(i => i === item)); // Needs ID

  return { items, add, remove };
}

// Overly complex generics that are hard to understand
function useStore<
  T extends Record<string, unknown>,
  K extends keyof T,
  V extends T[K],
  A extends { type: string; payload: V }
>(initial: T): [T, (action: A) => void] {
  // Too many type parameters
}
```

**Problems:**
- Using `any` removes all type information from hook return values
- Unconstrained generics allow operations that require specific shapes (like `id`)
- Too many type parameters make hooks difficult to use and understand

## Correct

```tsx
// ✅ Good
import { useState, useCallback, useEffect, useRef, useMemo } from 'react';

// Basic generic hook for data fetching
interface FetchState<T> {
  data: T | null;
  loading: boolean;
  error: Error | null;
}

interface UseFetchOptions<T> {
  initialData?: T;
  onSuccess?: (data: T) => void;
  onError?: (error: Error) => void;
  transform?: (raw: unknown) => T;
}

function useFetch<T>(
  url: string | null,
  options: UseFetchOptions<T> = {}
): FetchState<T> & { refetch: () => Promise<void> } {
  const { initialData = null, onSuccess, onError, transform } = options;

  const [state, setState] = useState<FetchState<T>>({
    data: initialData,
    loading: !!url,
    error: null,
  });

  const fetchData = useCallback(async () => {
    if (!url) {
      setState({ data: initialData, loading: false, error: null });
      return;
    }

    setState((prev) => ({ ...prev, loading: true, error: null }));

    try {
      const response = await fetch(url);
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      const raw = await response.json();
      const data = transform ? transform(raw) : (raw as T);

      setState({ data, loading: false, error: null });
      onSuccess?.(data);
    } catch (err) {
      const error = err instanceof Error ? err : new Error('Fetch failed');
      setState({ data: null, loading: false, error });
      onError?.(error);
    }
  }, [url, initialData, transform, onSuccess, onError]);

  useEffect(() => {
    fetchData();
  }, [fetchData]);

  return { ...state, refetch: fetchData };
}

// Usage with type inference
interface User {
  id: string;
  name: string;
  email: string;
}

const { data: user, loading, error } = useFetch<User>('/api/user/1');
// user is User | null

// Generic list management hook with proper constraints
interface Identifiable {
  id: string | number;
}

interface UseListActions<T> {
  add: (item: T) => void;
  remove: (id: T extends Identifiable ? T['id'] : number) => void;
  update: (id: T extends Identifiable ? T['id'] : number, updates: Partial<T>) => void;
  clear: () => void;
  set: (items: T[]) => void;
}

function useList<T extends Identifiable>(
  initialItems: T[] = []
): [T[], UseListActions<T>] {
  const [items, setItems] = useState<T[]>(initialItems);

  const actions: UseListActions<T> = useMemo(
    () => ({
      add: (item: T) => {
        setItems((prev) => [...prev, item]);
      },
      remove: (id) => {
        setItems((prev) => prev.filter((item) => item.id !== id));
      },
      update: (id, updates) => {
        setItems((prev) =>
          prev.map((item) => (item.id === id ? { ...item, ...updates } : item))
        );
      },
      clear: () => {
        setItems([]);
      },
      set: (newItems) => {
        setItems(newItems);
      },
    }),
    []
  );

  return [items, actions];
}

// Usage
interface Todo {
  id: string;
  text: string;
  completed: boolean;
}

const [todos, { add, remove, update }] = useList<Todo>([]);
add({ id: '1', text: 'Learn TypeScript', completed: false });
update('1', { completed: true });
remove('1');

// Generic form hook
type ValidationRule<T> = (value: T) => string | null;

interface UseFormOptions<T extends Record<string, unknown>> {
  initialValues: T;
  validationRules?: Partial<{ [K in keyof T]: ValidationRule<T[K]> }>;
  onSubmit: (values: T) => void | Promise<void>;
}

interface UseFormResult<T extends Record<string, unknown>> {
  values: T;
  errors: Partial<Record<keyof T, string>>;
  touched: Partial<Record<keyof T, boolean>>;
  isSubmitting: boolean;
  isValid: boolean;
  handleChange: <K extends keyof T>(field: K, value: T[K]) => void;
  handleBlur: <K extends keyof T>(field: K) => void;
  handleSubmit: (e?: React.FormEvent) => Promise<void>;
  reset: () => void;
  setFieldValue: <K extends keyof T>(field: K, value: T[K]) => void;
  setFieldError: <K extends keyof T>(field: K, error: string) => void;
}

function useForm<T extends Record<string, unknown>>({
  initialValues,
  validationRules = {},
  onSubmit,
}: UseFormOptions<T>): UseFormResult<T> {
  const [values, setValues] = useState<T>(initialValues);
  const [errors, setErrors] = useState<Partial<Record<keyof T, string>>>({});
  const [touched, setTouched] = useState<Partial<Record<keyof T, boolean>>>({});
  const [isSubmitting, setIsSubmitting] = useState(false);

  const validateField = useCallback(
    <K extends keyof T>(field: K, value: T[K]): string | null => {
      const rule = validationRules[field];
      return rule ? rule(value) : null;
    },
    [validationRules]
  );

  const handleChange = useCallback(
    <K extends keyof T>(field: K, value: T[K]) => {
      setValues((prev) => ({ ...prev, [field]: value }));

      if (touched[field]) {
        const error = validateField(field, value);
        setErrors((prev) => ({ ...prev, [field]: error ?? undefined }));
      }
    },
    [touched, validateField]
  );

  const handleBlur = useCallback(
    <K extends keyof T>(field: K) => {
      setTouched((prev) => ({ ...prev, [field]: true }));

      const error = validateField(field, values[field]);
      setErrors((prev) => ({ ...prev, [field]: error ?? undefined }));
    },
    [values, validateField]
  );

  const validateAll = useCallback((): boolean => {
    const newErrors: Partial<Record<keyof T, string>> = {};
    let isValid = true;

    for (const key of Object.keys(values) as Array<keyof T>) {
      const error = validateField(key, values[key]);
      if (error) {
        newErrors[key] = error;
        isValid = false;
      }
    }

    setErrors(newErrors);
    return isValid;
  }, [values, validateField]);

  const handleSubmit = useCallback(
    async (e?: React.FormEvent) => {
      e?.preventDefault();

      if (!validateAll()) return;

      setIsSubmitting(true);
      try {
        await onSubmit(values);
      } finally {
        setIsSubmitting(false);
      }
    },
    [values, validateAll, onSubmit]
  );

  const reset = useCallback(() => {
    setValues(initialValues);
    setErrors({});
    setTouched({});
  }, [initialValues]);

  const setFieldValue = useCallback(<K extends keyof T>(field: K, value: T[K]) => {
    setValues((prev) => ({ ...prev, [field]: value }));
  }, []);

  const setFieldError = useCallback(<K extends keyof T>(field: K, error: string) => {
    setErrors((prev) => ({ ...prev, [field]: error }));
  }, []);

  const isValid = Object.keys(errors).length === 0;

  return {
    values,
    errors,
    touched,
    isSubmitting,
    isValid,
    handleChange,
    handleBlur,
    handleSubmit,
    reset,
    setFieldValue,
    setFieldError,
  };
}

// Generic selection hook
function useSelection<T extends Identifiable>() {
  const [selectedIds, setSelectedIds] = useState<Set<T['id']>>(new Set());

  const select = useCallback((id: T['id']) => {
    setSelectedIds((prev) => new Set(prev).add(id));
  }, []);

  const deselect = useCallback((id: T['id']) => {
    setSelectedIds((prev) => {
      const next = new Set(prev);
      next.delete(id);
      return next;
    });
  }, []);

  const toggle = useCallback((id: T['id']) => {
    setSelectedIds((prev) => {
      const next = new Set(prev);
      if (next.has(id)) {
        next.delete(id);
      } else {
        next.add(id);
      }
      return next;
    });
  }, []);

  const isSelected = useCallback(
    (id: T['id']) => selectedIds.has(id),
    [selectedIds]
  );

  return {
    selectedIds: Array.from(selectedIds),
    select,
    deselect,
    toggle,
    isSelected,
    selectedCount: selectedIds.size,
  };
}
```

**Benefits:**
- Type safety without `any`: generics preserve type information throughout the hook
- Constraints using `extends` limit generic types to specific shapes
- Well-designed generics often do not need explicit type arguments
- Generic hooks work with any compatible type for maximum reusability
- Clear contracts: generic return types communicate what hooks provide

Reference: [React TypeScript Cheatsheet](https://react-typescript-cheatsheet.netlify.app)
