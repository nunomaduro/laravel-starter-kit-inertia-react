---
title: Context with useReducer
impact: MEDIUM
impactDescription: "discriminated union actions eliminate invalid state transitions"
tags: context, useReducer, discriminated-unions, state-machine
---

## Context with useReducer

**Impact: MEDIUM (discriminated union actions eliminate invalid state transitions)**

When combining Context with `useReducer`, use discriminated union actions so each action type has its own payload shape. This prevents passing the wrong payload to the wrong action.

## Incorrect

```tsx
// ❌ Bad
interface State {
  count: number;
  name: string;
  items: string[];
}

// Loose action type — any action can have any payload
interface Action {
  type: string;
  payload: any;
}

function reducer(state: State, action: Action): State {
  switch (action.type) {
    case "INCREMENT":
      return { ...state, count: state.count + action.payload }; // payload could be anything
    case "SET_NAME":
      return { ...state, name: action.payload }; // no guarantee it's a string
    case "ADD_ITEM":
      return { ...state, items: [...state.items, action.payload] };
    default:
      return state; // Unknown actions silently pass through
  }
}

// Dispatching wrong payload type — not caught
// dispatch({ type: "INCREMENT", payload: "hello" })
// dispatch({ type: "SET_NAME", payload: 42 })
```

**Problems:**
- `type: string` accepts any string, including typos like `"INCRMENT"`
- `payload: any` means wrong payload types are never caught
- `default: return state` silently ignores unknown actions instead of failing
- No connection between action type and its expected payload

## Correct

```tsx
// ✅ Good

// State type
interface CounterState {
  count: number;
  name: string;
  items: string[];
  isLoading: boolean;
}

// Discriminated union — each action defines its own shape
type CounterAction =
  | { type: "INCREMENT" }
  | { type: "DECREMENT" }
  | { type: "INCREMENT_BY"; payload: number }
  | { type: "SET_NAME"; payload: string }
  | { type: "ADD_ITEM"; payload: string }
  | { type: "REMOVE_ITEM"; payload: number }
  | { type: "SET_LOADING"; payload: boolean }
  | { type: "RESET" };

const initialState: CounterState = {
  count: 0,
  name: "",
  items: [],
  isLoading: false,
};

function counterReducer(state: CounterState, action: CounterAction): CounterState {
  switch (action.type) {
    case "INCREMENT":
      return { ...state, count: state.count + 1 };
    case "DECREMENT":
      return { ...state, count: state.count - 1 };
    case "INCREMENT_BY":
      return { ...state, count: state.count + action.payload }; // payload is number
    case "SET_NAME":
      return { ...state, name: action.payload }; // payload is string
    case "ADD_ITEM":
      return { ...state, items: [...state.items, action.payload] }; // payload is string
    case "REMOVE_ITEM":
      return {
        ...state,
        items: state.items.filter((_, i) => i !== action.payload), // payload is number
      };
    case "SET_LOADING":
      return { ...state, isLoading: action.payload }; // payload is boolean
    case "RESET":
      return initialState; // No payload needed
  }
}

// Context setup
interface CounterContextType {
  state: CounterState;
  dispatch: React.Dispatch<CounterAction>;
}

const CounterContext = createContext<CounterContextType | null>(null);

function useCounter(): CounterContextType {
  const context = useContext(CounterContext);
  if (context === null) {
    throw new Error("useCounter must be used within a CounterProvider");
  }
  return context;
}

function CounterProvider({ children }: { children: React.ReactNode }) {
  const [state, dispatch] = useReducer(counterReducer, initialState);

  return (
    <CounterContext.Provider value={{ state, dispatch }}>
      {children}
    </CounterContext.Provider>
  );
}

// Consumer — dispatch is fully typed
function Counter() {
  const { state, dispatch } = useCounter();

  return (
    <div>
      <p>Count: {state.count}</p>
      <button onClick={() => dispatch({ type: "INCREMENT" })}>+1</button>
      <button onClick={() => dispatch({ type: "INCREMENT_BY", payload: 5 })}>+5</button>
      <button onClick={() => dispatch({ type: "RESET" })}>Reset</button>

      {/* Compile error: payload must be number for INCREMENT_BY */}
      {/* dispatch({ type: "INCREMENT_BY", payload: "five" }) */}

      {/* Compile error: INCREMENT doesn't accept payload */}
      {/* dispatch({ type: "INCREMENT", payload: 1 }) */}
    </div>
  );
}
```

**Benefits:**
- Each action type has exactly the payload shape it needs (or no payload at all)
- Wrong payload types are caught at compile time: `{ type: "INCREMENT_BY", payload: "five" }` fails
- Typos in action types are caught: `{ type: "INCRMENT" }` fails
- The reducer switch is exhaustive — no silent fallthrough on unknown actions

Reference: [React TypeScript Cheatsheet — useReducer](https://react-typescript-cheatsheet.netlify.app/docs/basic/getting-started/hooks#usereducer)
