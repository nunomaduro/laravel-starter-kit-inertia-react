---
title: Generic List Component
impact: MEDIUM
impactDescription: "enables reusable, type-safe list rendering for any data type"
tags: generic, list, component, type-inference
---

## Generic List Component

**Impact: MEDIUM (enables reusable, type-safe list rendering for any data type)**

Generic components allow building reusable, type-safe components that work with any data type. The type flows through from the data to the render function, providing full type safety.

## Incorrect

```tsx
// ❌ Bad
// Using any - no type safety
interface ListProps {
  items: any[]
  renderItem: (item: any) => React.ReactNode
}

function List({ items, renderItem }: ListProps) {
  return <ul>{items.map(renderItem)}</ul>
}

// No autocomplete, no type checking
<List
  items={users}
  renderItem={(item) => <li>{item.nmae}</li>}  // Typo not caught!
/>
```

**Problems:**
- Using `any` removes all type checking on list items
- Typos in property names are not caught at compile time
- No autocomplete for item properties in the render callback

## Correct

```tsx
// ✅ Good
// Basic Generic List
interface ListProps<T> {
  items: T[]
  renderItem: (item: T, index: number) => React.ReactNode
  keyExtractor: (item: T) => string | number
}

function List<T>({ items, renderItem, keyExtractor }: ListProps<T>) {
  return (
    <ul>
      {items.map((item, index) => (
        <li key={keyExtractor(item)}>
          {renderItem(item, index)}
        </li>
      ))}
    </ul>
  )
}

// Usage - T is inferred from items
interface User {
  id: number
  name: string
  email: string
}

const users: User[] = [
  { id: 1, name: 'John', email: 'john@example.com' }
]

<List
  items={users}
  renderItem={(user) => (
    // user is typed as User
    <span>{user.name} - {user.email}</span>
  )}
  keyExtractor={(user) => user.id}
/>

// With Constraints
interface HasId {
  id: string | number
}

interface ConstrainedListProps<T extends HasId> {
  items: T[]
  renderItem: (item: T) => React.ReactNode
}

function ConstrainedList<T extends HasId>({ items, renderItem }: ConstrainedListProps<T>) {
  return (
    <ul>
      {items.map((item) => (
        <li key={item.id}>
          {renderItem(item)}
        </li>
      ))}
    </ul>
  )
}

// Generic Grid Component
interface GridProps<T> {
  items: T[]
  columns: number
  renderItem: (item: T) => React.ReactNode
  keyExtractor: (item: T) => string | number
  emptyState?: React.ReactNode
}

function Grid<T>({
  items,
  columns,
  renderItem,
  keyExtractor,
  emptyState = <p>No items</p>,
}: GridProps<T>) {
  if (items.length === 0) {
    return <>{emptyState}</>
  }

  return (
    <div
      className="grid gap-4"
      style={{ gridTemplateColumns: `repeat(${columns}, 1fr)` }}
    >
      {items.map((item) => (
        <div key={keyExtractor(item)}>
          {renderItem(item)}
        </div>
      ))}
    </div>
  )
}

// Generic Table Component
interface Column<T> {
  key: keyof T | string
  header: string
  render?: (item: T) => React.ReactNode
}

interface TableProps<T> {
  data: T[]
  columns: Column<T>[]
  keyExtractor: (item: T) => string | number
}

function Table<T>({ data, columns, keyExtractor }: TableProps<T>) {
  return (
    <table>
      <thead>
        <tr>
          {columns.map((col) => (
            <th key={String(col.key)}>{col.header}</th>
          ))}
        </tr>
      </thead>
      <tbody>
        {data.map((item) => (
          <tr key={keyExtractor(item)}>
            {columns.map((col) => (
              <td key={String(col.key)}>
                {col.render
                  ? col.render(item)
                  : String(item[col.key as keyof T] ?? '')}
              </td>
            ))}
          </tr>
        ))}
      </tbody>
    </table>
  )
}

// Alternative syntax: arrow function generic
// const ListArrow = <T,>({ items, renderItem }: ListProps<T>) => (
//   // Note the comma after T - needed in TSX files
//   <ul>{items.map(renderItem)}</ul>
// )

// Alternative syntax: with constraint
// const ListConstrained = <T extends HasId>({ items }: { items: T[] }) => (
//   <ul>{items.map(item => <li key={item.id} />)}</ul>
// )
```

**Benefits:**
- Full type inference from items to render callbacks
- Typos in property names are caught at compile time
- IDE autocomplete works for item properties
- Generic constraints can enforce minimum type requirements
- Reusable across any data type without losing type safety

Reference: [React TypeScript Cheatsheet](https://react-typescript-cheatsheet.netlify.app)
