---
title: Generic Select/Dropdown Component
impact: MEDIUM
impactDescription: "keeps value and onChange type-safe across any data type"
tags: generics, select, dropdown, type-inference
---

## Generic Select/Dropdown Component

**Impact: MEDIUM (keeps value and onChange type-safe across any data type)**

A generic select component lets the value type be inferred from the items array, so `onChange` always receives the correct type instead of forcing everything through `string`.

## Incorrect

```tsx
// ❌ Bad
interface SelectProps {
  items: { label: string; value: string }[];
  value: string;
  onChange: (value: string) => void;
}

function Select({ items, value, onChange }: SelectProps) {
  return (
    <select value={value} onChange={(e) => onChange(e.target.value)}>
      {items.map((item) => (
        <option key={item.value} value={item.value}>
          {item.label}
        </option>
      ))}
    </select>
  );
}

// Everything forced to string — loses type information
const statusOptions = [
  { label: "Active", value: "active" },
  { label: "Inactive", value: "inactive" },
];

// onChange receives string, but we wanted "active" | "inactive"
<Select items={statusOptions} value={status} onChange={(val) => setStatus(val)} />
```

**Problems:**
- `value: string` forces all select values to be strings, even when the domain type is narrower
- `onChange` returns `string`, requiring manual casting at every call site
- No connection between the items' value type and the onChange callback type
- Cannot use numeric or object values without converting to/from strings

## Correct

```tsx
// ✅ Good
interface SelectOption<T> {
  label: string;
  value: T;
}

interface SelectProps<T> {
  items: SelectOption<T>[];
  value: T;
  onChange: (value: T) => void;
  placeholder?: string;
}

function Select<T extends string | number>({
  items,
  value,
  onChange,
  placeholder,
}: SelectProps<T>) {
  return (
    <select
      value={String(value)}
      onChange={(e) => {
        const selected = items.find((item) => String(item.value) === e.target.value);
        if (selected) {
          onChange(selected.value); // Returns T, not string
        }
      }}
    >
      {placeholder && (
        <option value="" disabled>
          {placeholder}
        </option>
      )}
      {items.map((item) => (
        <option key={String(item.value)} value={String(item.value)}>
          {item.label}
        </option>
      ))}
    </select>
  );
}

// Usage — T is inferred from items

// String literal union
type Status = "active" | "inactive" | "pending";

const statusOptions: SelectOption<Status>[] = [
  { label: "Active", value: "active" },
  { label: "Inactive", value: "inactive" },
  { label: "Pending", value: "pending" },
];

function StatusFilter() {
  const [status, setStatus] = useState<Status>("active");

  // onChange receives Status, not string
  return <Select items={statusOptions} value={status} onChange={setStatus} />;
}

// Numeric values
const priorityOptions: SelectOption<number>[] = [
  { label: "Low", value: 1 },
  { label: "Medium", value: 2 },
  { label: "High", value: 3 },
];

function PriorityPicker() {
  const [priority, setPriority] = useState(1);

  // onChange receives number, not string
  return <Select items={priorityOptions} value={priority} onChange={setPriority} />;
}
```

**Benefits:**
- `T` is inferred from the items array, so `onChange` automatically returns the correct type
- String literal unions (`"active" | "inactive"`) are preserved through the component
- Numeric values work without string conversion at call sites
- Type mismatch between `items`, `value`, and `onChange` is caught at compile time

Reference: [React TypeScript Cheatsheet — Generic Components](https://react-typescript-cheatsheet.netlify.app/docs/advanced/patterns_by_usecase#generic-components)
