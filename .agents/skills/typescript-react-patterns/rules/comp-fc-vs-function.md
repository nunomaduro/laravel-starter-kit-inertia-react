---
title: FC vs Function Declaration
impact: CRITICAL
impactDescription: "ensures consistent and future-proof component typing"
tags: component, FC, function, declaration
---

## FC vs Function Declaration

**Impact: CRITICAL (ensures consistent and future-proof component typing)**

Choosing between `React.FC` and regular function declarations for component typing. Regular function declarations are preferred over `React.FC` for clarity and generic support.

## Incorrect

```tsx
// ❌ Bad
import React, { FC } from 'react';

interface ButtonProps {
  label: string;
  onClick: () => void;
}

// FC used to include children implicitly (React 17 and earlier)
// In React 18+, FC no longer includes children automatically
const Button: FC<ButtonProps> = ({ label, onClick }) => {
  return <button onClick={onClick}>{label}</button>;
};

// FC makes it harder to use generics
const List: FC<{ items: string[] }> = ({ items }) => (
  <ul>
    {items.map((item) => (
      <li key={item}>{item}</li>
    ))}
  </ul>
);
```

**Problems:**
- `React.FC` had implicit children in React 17, causing confusion across versions
- `React.FC` makes generic components awkward to type
- `React.FC` had issues with `defaultProps` typing
- Inconsistent community conventions between React 17 and 18

## Correct

```tsx
// ✅ Good
// Using regular function declarations with explicit return types
interface ButtonProps {
  label: string;
  onClick: () => void;
}

function Button({ label, onClick }: ButtonProps): React.ReactElement {
  return <button onClick={onClick}>{label}</button>;
}

// Arrow function with explicit typing
const Card = ({ title, children }: {
  title: string;
  children: React.ReactNode;
}): React.ReactElement => {
  return (
    <div className="card">
      <h2>{title}</h2>
      {children}
    </div>
  );
};

// Generic components are cleaner without FC
interface ListProps<T> {
  items: T[];
  renderItem: (item: T) => React.ReactNode;
}

function List<T>({ items, renderItem }: ListProps<T>): React.ReactElement {
  return <ul>{items.map(renderItem)}</ul>;
}

// Named function export for better debugging
interface User { id: string; name: string; email: string }

export function UserProfile({ user }: { user: User }): React.ReactElement {
  return <div>{user.name}</div>;
}
```

**Benefits:**
- Explicit over implicit: regular functions require explicit children prop declaration
- Generic support: regular functions work better with TypeScript generics
- Consistency: avoids confusion about React 17 vs 18 FC behavior
- Better debugging: named function declarations appear with their names in DevTools
- Industry trend: the React and TypeScript communities have moved away from FC

Reference: [React TypeScript Cheatsheet](https://react-typescript-cheatsheet.netlify.app)
