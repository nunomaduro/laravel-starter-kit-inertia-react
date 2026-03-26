---
title: Generic Table Component
impact: MEDIUM
impactDescription: "type-safe column accessors prevent runtime data access errors"
tags: generics, table, columns, type-safe
---

## Generic Table Component

**Impact: MEDIUM (type-safe column accessors prevent runtime data access errors)**

A generic table component uses `keyof T` for column accessors, ensuring that column definitions can only reference properties that actually exist on the row data type.

## Incorrect

```tsx
// ❌ Bad
interface Column {
  key: string;
  header: string;
  render?: (value: any, row: any) => React.ReactNode;
}

interface TableProps {
  data: any[];
  columns: Column[];
}

function Table({ data, columns }: TableProps) {
  return (
    <table>
      <thead>
        <tr>
          {columns.map((col) => (
            <th key={col.key}>{col.header}</th>
          ))}
        </tr>
      </thead>
      <tbody>
        {data.map((row, i) => (
          <tr key={i}>
            {columns.map((col) => (
              <td key={col.key}>
                {col.render ? col.render(row[col.key], row) : row[col.key]}
              </td>
            ))}
          </tr>
        ))}
      </tbody>
    </table>
  );
}

// Typo in key — not caught until runtime
<Table
  data={users}
  columns={[
    { key: "nmae", header: "Name" },  // Typo: should be "name"
    { key: "email", header: "Email" },
  ]}
/>
```

**Problems:**
- `key: string` accepts any string, including typos and non-existent properties
- `any[]` data means row properties have no autocomplete
- `render` callback receives `any`, so mistakes in the render function go uncaught
- No relationship between column keys and the actual data shape

## Correct

```tsx
// ✅ Good
interface Column<T> {
  key: keyof T;
  header: string;
  render?: (value: T[keyof T], row: T) => React.ReactNode;
}

interface TableProps<T> {
  data: T[];
  columns: Column<T>[];
  keyExtractor: (row: T) => string | number;
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
        {data.map((row) => (
          <tr key={keyExtractor(row)}>
            {columns.map((col) => (
              <td key={String(col.key)}>
                {col.render
                  ? col.render(row[col.key], row)
                  : String(row[col.key] ?? "")}
              </td>
            ))}
          </tr>
        ))}
      </tbody>
    </table>
  );
}

// Usage
interface User {
  id: number;
  name: string;
  email: string;
  role: "admin" | "user";
}

const userColumns: Column<User>[] = [
  { key: "name", header: "Name" },
  { key: "email", header: "Email" },
  {
    key: "role",
    header: "Role",
    render: (value, row) => (
      // row is typed as User, value is User[keyof User]
      <span className={row.role === "admin" ? "font-bold" : ""}>
        {String(value)}
      </span>
    ),
  },
];

function UserTable({ users }: { users: User[] }) {
  return (
    <Table
      data={users}
      columns={userColumns}
      keyExtractor={(user) => user.id}
    />
  );
}

// Compile error — "nmae" is not keyof User
// const badColumn: Column<User> = { key: "nmae", header: "Name" };
```

**Benefits:**
- `key: keyof T` only accepts property names that exist on the data type
- Typos in column keys are caught at compile time, not runtime
- `render` callback receives the typed row, enabling safe property access
- Adding or removing fields from the data type immediately flags broken columns

Reference: [React TypeScript Cheatsheet — Generic Components](https://react-typescript-cheatsheet.netlify.app/docs/advanced/patterns_by_usecase#generic-components)
