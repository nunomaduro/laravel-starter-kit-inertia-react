---
title: Generic Constraints with extends
impact: MEDIUM
impactDescription: "prevents invalid types from being passed to generic components and functions"
tags: generics, constraints, extends, keyof
---

## Generic Constraints with extends

**Impact: MEDIUM (prevents invalid types from being passed to generic components and functions)**

Unconstrained generics accept anything, including types that lack the properties your component needs. Use `extends` to constrain what `T` can be, and `keyof` to constrain keys to valid property names.

## Incorrect

```tsx
// ❌ Bad
// Unconstrained — accepts string, number, null, anything
interface ListProps<T> {
  items: T[];
  renderItem: (item: T) => React.ReactNode;
}

function List<T>({ items, renderItem }: ListProps<T>) {
  return (
    <ul>
      {items.map((item, i) => (
        // Cannot use item.id as key — T might not have 'id'
        <li key={i}>{renderItem(item)}</li>
      ))}
    </ul>
  );
}

// Unconstrained key lookup — no guarantee key exists on obj
function getProperty<T>(obj: T, key: string): unknown {
  return (obj as any)[key]; // Forced to cast to any
}
```

**Problems:**
- `T` could be a primitive, so accessing `item.id` would crash
- `key: string` accepts any string, not just valid property names of `T`
- Forced to use `any` casts to access properties, defeating TypeScript
- No compile-time feedback when passing invalid types

## Correct

```tsx
// ✅ Good

// Constrain T to objects with an id
interface HasId {
  id: string | number;
}

function List<T extends HasId>({
  items,
  renderItem,
}: {
  items: T[];
  renderItem: (item: T) => React.ReactNode;
}) {
  return (
    <ul>
      {items.map((item) => (
        // Safe — T is guaranteed to have .id
        <li key={item.id}>{renderItem(item)}</li>
      ))}
    </ul>
  );
}

// Constrain T to record-like objects
function ObjectTable<T extends Record<string, unknown>>({
  data,
  keys,
}: {
  data: T[];
  keys: (keyof T)[];
}) {
  return (
    <table>
      <thead>
        <tr>
          {keys.map((key) => (
            <th key={String(key)}>{String(key)}</th>
          ))}
        </tr>
      </thead>
      <tbody>
        {data.map((row, i) => (
          <tr key={i}>
            {keys.map((key) => (
              <td key={String(key)}>{String(row[key] ?? "")}</td>
            ))}
          </tr>
        ))}
      </tbody>
    </table>
  );
}

// Constrain key to keyof T — type-safe property access
function getProperty<T, K extends keyof T>(obj: T, key: K): T[K] {
  return obj[key]; // No cast needed — return type is T[K]
}

interface User {
  id: number;
  name: string;
  email: string;
}

const user: User = { id: 1, name: "Alice", email: "alice@example.com" };
const name = getProperty(user, "name"); // type is string
const id = getProperty(user, "id"); // type is number
// getProperty(user, "age"); // Compile error — "age" is not keyof User

// Multiple constraints
function merge<T extends HasId, U extends Partial<T>>(base: T, updates: U): T {
  return { ...base, ...updates };
}

// Constrained component props
interface SortableListProps<T extends HasId & { sortOrder: number }> {
  items: T[];
  onReorder: (items: T[]) => void;
}

function SortableList<T extends HasId & { sortOrder: number }>({
  items,
  onReorder,
}: SortableListProps<T>) {
  const sorted = [...items].sort((a, b) => a.sortOrder - b.sortOrder);

  return (
    <ul>
      {sorted.map((item) => (
        <li key={item.id}>Item {item.id} (order: {item.sortOrder})</li>
      ))}
    </ul>
  );
}
```

**Benefits:**
- `T extends HasId` guarantees `.id` exists, enabling safe key extraction
- `K extends keyof T` restricts keys to valid property names with typed return values
- `T extends Record<string, unknown>` ensures T is an object, not a primitive
- Multiple constraints (`T extends A & B`) combine requirements cleanly

Reference: [React TypeScript Cheatsheet — Generic Components](https://react-typescript-cheatsheet.netlify.app/docs/advanced/patterns_by_usecase#generic-components)
