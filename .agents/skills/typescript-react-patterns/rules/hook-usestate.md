---
title: useState Hook Typing
impact: CRITICAL
impactDescription: "prevents type errors when initial values do not represent all possible states"
tags: hook, useState, state, union-type
---

## useState Hook Typing

**Impact: CRITICAL (prevents type errors when initial values do not represent all possible states)**

useState infers types from initial values. When the initial value does not represent all possible states (like `null` for async data), explicit typing prevents runtime errors.

## Incorrect

```tsx
// ❌ Bad
// Inferred as null, can't set to User
const [user, setUser] = useState(null)
setUser({ id: 1, name: 'John' })  // Error!

// Inferred as never[] - can't add typed items
const [items, setItems] = useState([])
setItems([{ id: 1 }])  // Error!

// Inferred as string, can't set undefined
const [search, setSearch] = useState('')
setSearch(undefined)  // Error if you need undefined state
```

**Problems:**
- `useState(null)` infers type as `null` only, rejecting object values
- `useState([])` infers type as `never[]`, rejecting any array items
- Simple type inference cannot represent union states like `string | undefined`

## Correct

```tsx
// ✅ Good
// Nullable State
interface User {
  id: number
  name: string
  email: string
}

// Explicit union type for nullable state
const [user, setUser] = useState<User | null>(null)

// Now both work:
setUser({ id: 1, name: 'John', email: 'john@example.com' })
setUser(null)

// Access with null check
if (user) {
  console.log(user.name)  // TypeScript knows user is User here
}

// Array State
interface Todo {
  id: number
  text: string
  done: boolean
}

const [todos, setTodos] = useState<Todo[]>([])

// Add item
setTodos(prev => [...prev, { id: Date.now(), text: 'New', done: false }])

// Update item
setTodos(prev =>
  prev.map(todo =>
    todo.id === id ? { ...todo, done: !todo.done } : todo
  )
)

// Remove item
setTodos(prev => prev.filter(todo => todo.id !== id))

// Object State
interface FormData {
  name: string
  email: string
  age: number
}

const [form, setForm] = useState<FormData>({
  name: '',
  email: '',
  age: 0,
})

// Update single field
setForm(prev => ({ ...prev, name: 'John' }))

// Partial updates helper
const updateForm = <K extends keyof FormData>(
  field: K,
  value: FormData[K]
) => {
  setForm(prev => ({ ...prev, [field]: value }))
}

updateForm('name', 'John')
updateForm('age', 25)

// Union State (Discriminated union for state machines)
type RequestState<T> =
  | { status: 'idle' }
  | { status: 'loading' }
  | { status: 'success'; data: T }
  | { status: 'error'; error: Error }

const [state, setState] = useState<RequestState<User>>({ status: 'idle' })

// Usage
switch (state.status) {
  case 'idle':
    return <button onClick={fetch}>Load</button>
  case 'loading':
    return <Spinner />
  case 'success':
    return <UserCard user={state.data} />  // data is typed!
  case 'error':
    return <Error message={state.error.message} />
}

// Lazy Initialization
const [lazyState, setLazyState] = useState<ComplexState>(() => {
  return computeInitialState()
})

const [theme, setTheme] = useState<'light' | 'dark'>(() => {
  const saved = localStorage.getItem('theme')
  return (saved as 'light' | 'dark') || 'light'
})

// Undefined vs Null
// Use undefined for "not yet set"
const [selectedId, setSelectedId] = useState<number | undefined>(undefined)

// Use null for "explicitly empty"
// (same pattern as the nullable state example above)
const [userData, setUserData] = useState<User | null>(null)
```

**Benefits:**
- Explicit type annotations allow state to hold values beyond the initial type
- Discriminated unions enable exhaustive state machine patterns
- Lazy initialization runs expensive computations only once
- Clear conventions for `null` vs `undefined` improve code readability
- Generic type parameters work seamlessly with complex state shapes

Reference: [React TypeScript Cheatsheet](https://react-typescript-cheatsheet.netlify.app)
