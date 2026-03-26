---
title: useCallback Typing
impact: CRITICAL
impactDescription: "ensures memoized callbacks have correct parameter and return types"
tags: hook, useCallback, memoization, performance
---

## useCallback Typing

**Impact: CRITICAL (ensures memoized callbacks have correct parameter and return types)**

Properly typing useCallback for memoized function references. Explicit parameter types enable autocomplete and catch errors, while proper dependencies prevent stale closures.

## Incorrect

```tsx
// ❌ Bad
// Missing dependency array type inference
const handleClick = useCallback((id) => {
  console.log(id); // id is implicitly 'any'
}, []);

// Incorrect return type inference
const fetchData = useCallback(async () => {
  const data = await api.getData();
  return data; // Return type not enforced
});

// Dependencies not aligned with closure usage
const [count, setCount] = useState(0);
const increment = useCallback(() => {
  setCount(count + 1); // Stale closure - count not in deps
}, []); // Missing count dependency

// Using 'any' for event parameter
const handleChange = useCallback((e: any) => {
  setValue(e.target.value);
}, []);
```

**Problems:**
- Missing parameter types result in implicit `any` with no autocomplete
- Missing or incorrect dependencies cause stale closure bugs
- Using `any` for event parameters removes all type checking
- Return types are not enforced without explicit annotation

## Correct

```tsx
// ✅ Good
import { useCallback, useState } from 'react';

// Basic callback with explicit parameter types
const handleClick = useCallback((id: string) => {
  console.log('Clicked:', id);
}, []);

// Callback with event typing
const handleInputChange = useCallback(
  (event: React.ChangeEvent<HTMLInputElement>) => {
    const value = event.target.value;
    console.log('Input changed:', value);
  },
  []
);

// Callback with return type
interface CartItem { id: string; name: string; price: number; quantity: number }

const calculateTotal = useCallback(
  (items: CartItem[]): number => {
    return items.reduce((sum, item) => sum + item.price * item.quantity, 0);
  },
  []
);

// Async callback with proper typing
interface User {
  id: string;
  name: string;
  email: string;
}

const fetchUser = useCallback(
  async (userId: string): Promise<User> => {
    const response = await fetch(`/api/users/${userId}`);
    if (!response.ok) {
      throw new Error('Failed to fetch user');
    }
    return response.json();
  },
  []
);

// Callback using state with proper dependencies
function Counter() {
  const [count, setCount] = useState(0);
  const [step, setStep] = useState(1);

  // Use functional update to avoid stale closure
  const increment = useCallback(() => {
    setCount((prevCount) => prevCount + step);
  }, [step]); // Only depends on step, count uses functional update

  // When you need the current value in the callback
  const logAndIncrement = useCallback(() => {
    console.log('Current count:', count);
    setCount((prevCount) => prevCount + 1);
  }, [count]); // count needed for console.log

  return (
    <div>
      <p>Count: {count}</p>
      <button onClick={increment}>+{step}</button>
    </div>
  );
}

// Callback passed to child with proper typing
interface Item { id: string; name: string }

interface ItemListProps {
  items: Item[];
  onItemSelect: (item: Item) => void;
  onItemDelete: (id: string) => Promise<void>;
}

function ItemContainer() {
  const [items, setItems] = useState<Item[]>([]);

  const handleSelect = useCallback((item: Item) => {
    console.log('Selected:', item.name);
  }, []);

  const handleDelete = useCallback(async (id: string): Promise<void> => {
    // assume api is injected or imported
    await api.deleteItem(id);
    setItems((prev) => prev.filter((item) => item.id !== id));
  }, []);

  return (
    <ItemList
      items={items}
      onItemSelect={handleSelect}
      onItemDelete={handleDelete}
    />
  );
}

// Callback with multiple parameters
interface FormData {
  name: string;
  email: string;
  message: string;
}

type FormField = keyof FormData;

function FormComponent() {
  const [formData, setFormData] = useState<Record<string, string>>({});

  const handleFieldChange = useCallback(
    (field: FormField, value: string) => {
      setFormData((prev) => ({ ...prev, [field]: value }));
    },
    []
  );
}

// Memoized callback for optimized child renders
const MemoizedChild = React.memo<{ onClick: () => void }>(({ onClick }) => {
  console.log('Child rendered');
  return <button onClick={onClick}>Click me</button>;
});

function Parent() {
  const [count, setCount] = useState(0);

  // Without useCallback, MemoizedChild would re-render on every Parent render
  const handleClick = useCallback(() => {
    setCount((c) => c + 1);
  }, []);

  return (
    <div>
      <p>Count: {count}</p>
      <MemoizedChild onClick={handleClick} />
    </div>
  );
}
```

**Benefits:**
- Explicit parameter types enable autocomplete and catch errors
- Declared return types ensure callbacks return expected values
- Functional updates avoid stale closures without adding state to deps
- Proper React event types for form and DOM events
- Stable references prevent unnecessary child re-renders with React.memo

Reference: [React TypeScript Cheatsheet](https://react-typescript-cheatsheet.netlify.app)
