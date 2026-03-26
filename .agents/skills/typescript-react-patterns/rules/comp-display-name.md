---
title: Display Name Pattern
impact: CRITICAL
impactDescription: "improves debugging and DevTools component identification"
tags: component, displayName, debugging, HOC
---

## Display Name Pattern

**Impact: CRITICAL (improves debugging and DevTools component identification)**

Setting displayName for better debugging and DevTools integration. Components without displayName show as "Anonymous" in React DevTools, making debugging difficult.

## Incorrect

```tsx
// ❌ Bad
// Anonymous arrow function - shows as "Anonymous" in DevTools
export const Button = ({ children }: { children: React.ReactNode }) => {
  return <button>{children}</button>;
};

// HOC without displayName - hard to identify wrapped component
function withLogger<P extends object>(Component: React.ComponentType<P>) {
  return (props: P) => {
    console.log('Rendering:', props);
    return <Component {...props} />;
  };
}

// Memo without displayName
const ExpensiveList = React.memo(({ items }: { items: string[] }) => {
  return (
    <ul>
      {items.map((item) => (
        <li key={item}>{item}</li>
      ))}
    </ul>
  );
});

// ForwardRef without displayName
const Input = React.forwardRef<HTMLInputElement, React.InputHTMLAttributes<HTMLInputElement>>(
  (props, ref) => <input ref={ref} {...props} />
);

// Context without displayName
const ThemeContext = React.createContext<'light' | 'dark'>('light');
```

**Problems:**
- Anonymous components appear as "Anonymous" in React DevTools
- HOCs without displayName hide the wrapped component identity
- Error stack traces show meaningless component names
- React Profiler cannot label components for performance analysis

## Correct

```tsx
// ✅ Good
import React, { forwardRef, memo, createContext } from 'react';

// Named function export - automatically has displayName
export function Button({ children }: { children: React.ReactNode }): React.ReactElement {
  return <button>{children}</button>;
}

// Arrow function with explicit displayName
export const IconButton = ({ icon, label }: { icon: React.ReactNode; label: string }): React.ReactElement => {
  return (
    <button aria-label={label}>
      {icon}
    </button>
  );
};
IconButton.displayName = 'IconButton';

// HOC with proper displayName
function withLogger<P extends object>(
  WrappedComponent: React.ComponentType<P>
) {
  const displayName = WrappedComponent.displayName ?? WrappedComponent.name ?? 'Component';

  function WithLogger(props: P) {
    console.log(`Rendering ${displayName}:`, props);
    return <WrappedComponent {...props} />;
  }

  WithLogger.displayName = `withLogger(${displayName})`;

  return WithLogger;
}

const LoggedButton = withLogger(Button);

// Memo with displayName
interface ListProps {
  items: string[];
  onItemClick?: (item: string) => void;
}

const ExpensiveList = memo<ListProps>(({ items, onItemClick }) => {
  return (
    <ul>
      {items.map((item) => (
        <li key={item} onClick={() => onItemClick?.(item)}>
          {item}
        </li>
      ))}
    </ul>
  );
});
ExpensiveList.displayName = 'ExpensiveList';

// ForwardRef with displayName
interface InputProps extends Omit<React.InputHTMLAttributes<HTMLInputElement>, 'size'> {
  size?: 'sm' | 'md' | 'lg';
  error?: string;
}

const Input = forwardRef<HTMLInputElement, InputProps>(
  ({ size = 'md', error, className, ...rest }, ref) => {
    return (
      <div className="input-wrapper">
        <input
          ref={ref}
          className={`input input-${size} ${error ? 'input-error' : ''} ${className ?? ''}`}
          aria-invalid={!!error}
          {...rest}
        />
        {error && <span className="error">{error}</span>}
      </div>
    );
  }
);
Input.displayName = 'Input';

// Context with displayName
interface ThemeContextValue {
  theme: 'light' | 'dark';
  toggleTheme: () => void;
}

const ThemeContext = createContext<ThemeContextValue | null>(null);
ThemeContext.displayName = 'ThemeContext';

// Helper function for creating displayName
function getDisplayName<P>(Component: React.ComponentType<P>): string {
  return Component.displayName ?? Component.name ?? 'Component';
}
```

**Benefits:**
- Components appear with readable names in React DevTools
- Stack traces show meaningful component names
- Wrapped components show their hierarchy (e.g., "withLogger(Button)")
- React Profiler shows component names for performance analysis
- Component names appear in test output and snapshots

Reference: [React TypeScript Cheatsheet](https://react-typescript-cheatsheet.netlify.app)
