---
title: useReducer Typing
impact: CRITICAL
impactDescription: "enables exhaustive action handling and type-safe state transitions"
tags: hook, useReducer, discriminated-union, action
---

## useReducer Typing

**Impact: CRITICAL (enables exhaustive action handling and type-safe state transitions)**

Properly typing the useReducer hook with actions, state, and discriminated unions. Discriminated union action types enable exhaustive switch statements and catch missing cases at compile time.

## Incorrect

```tsx
// ❌ Bad
// Untyped reducer - no type safety
const reducer = (state, action) => {
  switch (action.type) {
    case 'increment':
      return { count: state.count + 1 };
    default:
      return state;
  }
};

// Action type as string - no autocomplete or validation
interface Action {
  type: string;
  payload?: any;
}

// Missing exhaustiveness checking
function badReducer(state: State, action: Action): State {
  switch (action.type) {
    case 'add':
      return { ...state, items: [...state.items, action.payload] };
    // Forgot to handle 'remove' action - no error!
  }
  return state;
}
```

**Problems:**
- Untyped reducers have implicit `any` for state and action
- String-typed action types provide no autocomplete or validation
- Without exhaustiveness checking, missing action cases are silently ignored
- `any` payload types allow invalid data to pass through

## Correct

```tsx
// ✅ Good
import { useReducer, Reducer } from 'react';

// Define state interface
interface CounterState {
  count: number;
  step: number;
}

// Define action types using discriminated union
type CounterAction =
  | { type: 'increment' }
  | { type: 'decrement' }
  | { type: 'reset' }
  | { type: 'setStep'; payload: number }
  | { type: 'incrementBy'; payload: number };

const initialState: CounterState = {
  count: 0,
  step: 1,
};

// Typed reducer with exhaustiveness checking
function counterReducer(state: CounterState, action: CounterAction): CounterState {
  switch (action.type) {
    case 'increment':
      return { ...state, count: state.count + state.step };
    case 'decrement':
      return { ...state, count: state.count - state.step };
    case 'reset':
      return initialState;
    case 'setStep':
      return { ...state, step: action.payload };
    case 'incrementBy':
      return { ...state, count: state.count + action.payload };
    default:
      // Exhaustiveness check - will error if a case is missing
      const _exhaustive: never = action;
      return state;
  }
}

// Usage in component
function Counter() {
  const [state, dispatch] = useReducer(counterReducer, initialState);

  return (
    <div>
      <p>Count: {state.count}</p>
      <button onClick={() => dispatch({ type: 'increment' })}>+</button>
      <button onClick={() => dispatch({ type: 'decrement' })}>-</button>
      <button onClick={() => dispatch({ type: 'reset' })}>Reset</button>
      <button onClick={() => dispatch({ type: 'incrementBy', payload: 10 })}>+10</button>
    </div>
  );
}

// Complex state with nested objects
interface TodoState {
  todos: Todo[];
  filter: 'all' | 'active' | 'completed';
  isLoading: boolean;
  error: string | null;
}

interface Todo {
  id: string;
  text: string;
  completed: boolean;
}

type TodoAction =
  | { type: 'ADD_TODO'; payload: { text: string } }
  | { type: 'TOGGLE_TODO'; payload: { id: string } }
  | { type: 'DELETE_TODO'; payload: { id: string } }
  | { type: 'SET_FILTER'; payload: TodoState['filter'] }
  | { type: 'FETCH_START' }
  | { type: 'FETCH_SUCCESS'; payload: Todo[] }
  | { type: 'FETCH_ERROR'; payload: string };

function todoReducer(state: TodoState, action: TodoAction): TodoState {
  switch (action.type) {
    case 'ADD_TODO':
      return {
        ...state,
        todos: [
          ...state.todos,
          {
            id: crypto.randomUUID(),
            text: action.payload.text,
            completed: false,
          },
        ],
      };

    case 'TOGGLE_TODO':
      return {
        ...state,
        todos: state.todos.map((todo) =>
          todo.id === action.payload.id
            ? { ...todo, completed: !todo.completed }
            : todo
        ),
      };

    case 'DELETE_TODO':
      return {
        ...state,
        todos: state.todos.filter((todo) => todo.id !== action.payload.id),
      };

    case 'SET_FILTER':
      return { ...state, filter: action.payload };

    case 'FETCH_START':
      return { ...state, isLoading: true, error: null };

    case 'FETCH_SUCCESS':
      return { ...state, isLoading: false, todos: action.payload };

    case 'FETCH_ERROR':
      return { ...state, isLoading: false, error: action.payload };

    default:
      const _exhaustive: never = action;
      return state;
  }
}

// Lazy initialization with typed init function
interface FormState {
  values: Record<string, string>;
  touched: Record<string, boolean>;
  errors: Record<string, string>;
}

type FormAction =
  | { type: 'SET_VALUE'; field: string; value: string }
  | { type: 'SET_TOUCHED'; field: string }
  | { type: 'SET_ERROR'; field: string; error: string }
  | { type: 'RESET' };

function createInitialState(fields: string[]): FormState {
  return {
    values: Object.fromEntries(fields.map((f) => [f, ''])),
    touched: Object.fromEntries(fields.map((f) => [f, false])),
    errors: {},
  };
}

function formReducer(state: FormState, action: FormAction): FormState {
  switch (action.type) {
    case 'SET_VALUE':
      return {
        ...state,
        values: { ...state.values, [action.field]: action.value },
      };
    case 'SET_TOUCHED':
      return {
        ...state,
        touched: { ...state.touched, [action.field]: true },
      };
    case 'SET_ERROR':
      return {
        ...state,
        errors: { ...state.errors, [action.field]: action.error },
      };
    case 'RESET':
      return createInitialState(Object.keys(state.values));
    default:
      const _exhaustive: never = action;
      return state;
  }
}

// Usage with lazy init
function Form({ fields }: { fields: string[] }) {
  const [state, dispatch] = useReducer(formReducer, fields, createInitialState);

  // dispatch is fully typed
  dispatch({ type: 'SET_VALUE', field: 'email', value: 'test@example.com' });

  return <form>{/* form fields */}</form>;
}

// Action creators for better DX
const todoActions = {
  addTodo: (text: string): TodoAction => ({ type: 'ADD_TODO', payload: { text } }),
  toggleTodo: (id: string): TodoAction => ({ type: 'TOGGLE_TODO', payload: { id } }),
  deleteTodo: (id: string): TodoAction => ({ type: 'DELETE_TODO', payload: { id } }),
  setFilter: (filter: TodoState['filter']): TodoAction => ({ type: 'SET_FILTER', payload: filter }),
};

// Usage with action creators
dispatch(todoActions.addTodo('Buy groceries'));
dispatch(todoActions.toggleTodo('123'));
```

**Benefits:**
- Discriminated unions enable exhaustive switch statements
- Compile-time errors when action cases are missing
- Each action has its own strongly-typed payload
- Type system helps ensure proper immutable state updates
- Lazy initialization third argument properly types the init function
- Action creator factory functions provide better developer experience

Reference: [React TypeScript Cheatsheet](https://react-typescript-cheatsheet.netlify.app)
