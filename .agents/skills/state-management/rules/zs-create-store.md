---
title: Creating Zustand Stores
impact: CRITICAL
section: Zustand Stores
tags: zustand, stores, state-management
---

# zs-create-store

**Impact: CRITICAL**


**Priority:** CRITICAL
**Category:** Zustand Store Patterns

## Why It Matters

Zustand provides a simple, lightweight way to manage client state. Unlike Redux, there's minimal boilerplate. Understanding the basic patterns enables efficient state management.

## Basic Store

```tsx
import { create } from 'zustand'

// Simple counter store
const useCounterStore = create((set) => ({
  count: 0,
  increment: () => set((state) => ({ count: state.count + 1 })),
  decrement: () => set((state) => ({ count: state.count - 1 })),
  reset: () => set({ count: 0 }),
}))

// Usage
function Counter() {
  const count = useCounterStore((state) => state.count)
  const increment = useCounterStore((state) => state.increment)

  return (
    <div>
      <span>{count}</span>
      <button onClick={increment}>+</button>
    </div>
  )
}
```

## TypeScript Store

```tsx
import { create } from 'zustand'

// Define state and actions interface
interface CounterState {
  count: number
}

interface CounterActions {
  increment: () => void
  decrement: () => void
  reset: () => void
  setCount: (count: number) => void
}

type CounterStore = CounterState & CounterActions

// Create typed store
const useCounterStore = create<CounterStore>((set) => ({
  // State
  count: 0,

  // Actions
  increment: () => set((state) => ({ count: state.count + 1 })),
  decrement: () => set((state) => ({ count: state.count - 1 })),
  reset: () => set({ count: 0 }),
  setCount: (count) => set({ count }),
}))
```

## Complex Store Example

```tsx
import { create } from 'zustand'

interface Todo {
  id: string
  text: string
  completed: boolean
}

interface TodoState {
  todos: Todo[]
  filter: 'all' | 'active' | 'completed'
}

interface TodoActions {
  addTodo: (text: string) => void
  toggleTodo: (id: string) => void
  removeTodo: (id: string) => void
  setFilter: (filter: TodoState['filter']) => void
  clearCompleted: () => void
}

type TodoStore = TodoState & TodoActions

export const useTodoStore = create<TodoStore>((set) => ({
  // State
  todos: [],
  filter: 'all',

  // Actions
  addTodo: (text) =>
    set((state) => ({
      todos: [
        ...state.todos,
        { id: crypto.randomUUID(), text, completed: false },
      ],
    })),

  toggleTodo: (id) =>
    set((state) => ({
      todos: state.todos.map((todo) =>
        todo.id === id ? { ...todo, completed: !todo.completed } : todo
      ),
    })),

  removeTodo: (id) =>
    set((state) => ({
      todos: state.todos.filter((todo) => todo.id !== id),
    })),

  setFilter: (filter) => set({ filter }),

  clearCompleted: () =>
    set((state) => ({
      todos: state.todos.filter((todo) => !todo.completed),
    })),
}))
```

## Using `get` for Actions

```tsx
const useStore = create<Store>((set, get) => ({
  items: [],
  selectedId: null,

  // Use get() to access current state
  selectNext: () => {
    const { items, selectedId } = get()
    const currentIndex = items.findIndex((i) => i.id === selectedId)
    const nextIndex = (currentIndex + 1) % items.length
    set({ selectedId: items[nextIndex]?.id })
  },

  // Async action with get()
  fetchAndSelect: async (id) => {
    const item = await fetchItem(id)
    const { items } = get()
    set({
      items: [...items, item],
      selectedId: item.id,
    })
  },
}))
```

## Selectors for Performance

```tsx
const useTodoStore = create<TodoStore>((set) => ({
  todos: [],
  filter: 'all',
  // ...actions
}))

// ❌ Bad - re-renders on ANY state change
function BadTodoList() {
  const store = useTodoStore()  // Subscribes to entire store
  return <div>{store.todos.length}</div>
}

// ✅ Good - only re-renders when todos change
function GoodTodoList() {
  const todos = useTodoStore((state) => state.todos)
  return <div>{todos.length}</div>
}

// ✅ Computed selector
function FilteredTodoList() {
  const filteredTodos = useTodoStore((state) => {
    switch (state.filter) {
      case 'active':
        return state.todos.filter((t) => !t.completed)
      case 'completed':
        return state.todos.filter((t) => t.completed)
      default:
        return state.todos
    }
  })

  return <ul>{filteredTodos.map((t) => <li key={t.id}>{t.text}</li>)}</ul>
}
```

## Multiple Selectors

```tsx
// Select multiple values efficiently
function TodoStats() {
  const total = useTodoStore((state) => state.todos.length)
  const completed = useTodoStore(
    (state) => state.todos.filter((t) => t.completed).length
  )

  return (
    <div>
      {completed} of {total} completed
    </div>
  )
}

// Or use useShallow for shallow comparison of objects (Zustand v5+)
import { useShallow } from 'zustand/shallow'

function TodoStats() {
  const { total, completed } = useTodoStore(
    useShallow((state) => ({
      total: state.todos.length,
      completed: state.todos.filter((t) => t.completed).length,
    }))
  )

  return <div>{completed} of {total}</div>
}
```

## Actions Outside Components

```tsx
// Access store outside React
const { increment, getState } = useCounterStore

// Call action
increment()

// Get current state
const count = useCounterStore.getState().count

// Subscribe to changes
const unsubscribe = useCounterStore.subscribe(
  (state) => console.log('Count:', state.count)
)
```

## Store Organization

```tsx
// stores/index.ts - Export all stores
export { useAuthStore } from './authStore'
export { useTodoStore } from './todoStore'
export { useUIStore } from './uiStore'

// stores/authStore.ts
export const useAuthStore = create<AuthStore>((set) => ({
  // ...
}))

// stores/uiStore.ts
export const useUIStore = create<UIStore>((set) => ({
  // ...
}))
```

## Impact

- Minimal boilerplate
- TypeScript first
- Fine-grained re-renders with selectors
- Easy to test and debug
