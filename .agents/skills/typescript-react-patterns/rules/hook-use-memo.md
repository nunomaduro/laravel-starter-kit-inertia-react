---
title: useMemo Typing
impact: CRITICAL
impactDescription: "ensures memoized values have correct types and proper dependencies"
tags: hook, useMemo, memoization, performance
---

## useMemo Typing

**Impact: CRITICAL (ensures memoized values have correct types and proper dependencies)**

Properly typing useMemo for memoized computed values. Explicit type annotations make code more readable, catch errors, and ensure correct dependency arrays.

## Incorrect

```tsx
// ❌ Bad
// Missing type annotation - relies entirely on inference
const expensiveResult = useMemo(() => {
  return someExpensiveCalculation(data);
}, [data]);

// Using 'any' loses type safety
const config = useMemo<any>(() => ({
  theme: 'dark',
  features: ['a', 'b'],
}), []);

// Unnecessary useMemo for simple values
const double = useMemo(() => count * 2, [count]); // Simple math doesn't need memoization

// Missing dependencies causes stale values
const filteredItems = useMemo(() => {
  return items.filter(item => item.category === selectedCategory);
}, []); // Missing items and selectedCategory
```

**Problems:**
- Using `any` removes type safety on the memoized value
- Missing dependencies cause stale cached values
- Simple calculations do not benefit from memoization overhead
- Unclear return types when conditionally returning different shapes

## Correct

```tsx
// ✅ Good
import { useMemo, useState, createContext } from 'react';

// Basic useMemo with explicit type
interface ProcessedData {
  total: number;
  average: number;
  max: number;
  min: number;
}

const statistics = useMemo<ProcessedData>(() => {
  const total = numbers.reduce((sum, n) => sum + n, 0);
  return {
    total,
    average: total / numbers.length,
    max: Math.max(...numbers),
    min: Math.min(...numbers),
  };
}, [numbers]);

// Filtering and sorting with proper typing
interface Product {
  id: string;
  name: string;
  price: number;
  category: string;
  inStock: boolean;
}

type SortField = 'name' | 'price';
type SortDirection = 'asc' | 'desc';

function ProductList({ products }: { products: Product[] }) {
  const [searchTerm, setSearchTerm] = useState('');
  const [category, setCategory] = useState<string | null>(null);
  const [sortField, setSortField] = useState<SortField>('name');
  const [sortDirection, setSortDirection] = useState<SortDirection>('asc');

  const filteredAndSortedProducts = useMemo<Product[]>(() => {
    let result = products;

    if (searchTerm) {
      const term = searchTerm.toLowerCase();
      result = result.filter((p) =>
        p.name.toLowerCase().includes(term)
      );
    }

    if (category) {
      result = result.filter((p) => p.category === category);
    }

    result = [...result].sort((a, b) => {
      const aVal = a[sortField];
      const bVal = b[sortField];
      const comparison = aVal < bVal ? -1 : aVal > bVal ? 1 : 0;
      return sortDirection === 'asc' ? comparison : -comparison;
    });

    return result;
  }, [products, searchTerm, category, sortField, sortDirection]);

  return (
    <ul>
      {filteredAndSortedProducts.map((product) => (
        <li key={product.id}>{product.name} - ${product.price}</li>
      ))}
    </ul>
  );
}

// Memoizing derived state for context
interface ThemeColors {
  primary: string;
  secondary: string;
  background: string;
  text: string;
  border: string;
}

interface ThemeContextValue {
  theme: 'light' | 'dark';
  colors: ThemeColors;
}

const ThemeContext = createContext<ThemeContextValue>(null!);

function ThemeProvider({ children }: { children: React.ReactNode }) {
  const [theme, setTheme] = useState<'light' | 'dark'>('light');

  const colors = useMemo<ThemeColors>(() => {
    if (theme === 'light') {
      return {
        primary: '#007bff',
        secondary: '#6c757d',
        background: '#ffffff',
        text: '#212529',
        border: '#dee2e6',
      };
    }
    return {
      primary: '#6ea8fe',
      secondary: '#adb5bd',
      background: '#212529',
      text: '#ffffff',
      border: '#495057',
    };
  }, [theme]);

  const contextValue = useMemo<ThemeContextValue>(
    () => ({ theme, colors }),
    [theme, colors]
  );

  return (
    <ThemeContext.Provider value={contextValue}>
      {children}
    </ThemeContext.Provider>
  );
}

// Memoizing expensive transformations
interface RawDataPoint {
  timestamp: string;
  value: number;
  metadata: Record<string, unknown>;
}

interface ChartDataPoint {
  x: Date;
  y: number;
  label: string;
}

function DataVisualization({ rawData }: { rawData: RawDataPoint[] }) {
  const chartData = useMemo<ChartDataPoint[]>(() => {
    return rawData.map((point) => ({
      x: new Date(point.timestamp),
      y: point.value,
      label: `Value: ${point.value}`,
    }));
  }, [rawData]);

  const aggregatedData = useMemo(() => {
    const byMonth = new Map<string, number[]>();

    chartData.forEach((point) => {
      const monthKey = `${point.x.getFullYear()}-${point.x.getMonth()}`;
      const existing = byMonth.get(monthKey) ?? [];
      byMonth.set(monthKey, [...existing, point.y]);
    });

    return Array.from(byMonth.entries()).map(([month, values]) => ({
      month,
      average: values.reduce((a, b) => a + b, 0) / values.length,
      count: values.length,
    }));
  }, [chartData]);

  return <Chart data={chartData} aggregated={aggregatedData} />;
}

// Generic memoized selector pattern
interface Order { id: string; total: number }
function useMemoizedSelector<TState, TSelected>(
  state: TState,
  selector: (state: TState) => TSelected
): TSelected {
  return useMemo(() => selector(state), [state, selector]);
}

// Usage
interface User {
  id: string;
  name: string;
  email: string;
  isActive: boolean;
}

interface AppState {
  users: User[];
  products: Product[];
  orders: Order[];
}

function UserList({ state }: { state: AppState }) {
  const activeUsers = useMemoizedSelector(
    state,
    (s) => s.users.filter((u) => u.isActive)
  );

  return <ul>{activeUsers.map((u) => <li key={u.id}>{u.name}</li>)}</ul>;
}
```

**Benefits:**
- Explicit types make code more readable and catch type mismatches
- Correct dependencies ensure memoized values stay up to date
- Only expensive calculations benefit from memoization
- Reference stability prevents unnecessary child re-renders
- Derived state avoids storing redundant data
- Memoized context values prevent provider-triggered re-renders

Reference: [React TypeScript Cheatsheet](https://react-typescript-cheatsheet.netlify.app)
