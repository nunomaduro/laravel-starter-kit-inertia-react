---
title: Discriminated Unions for State
impact: LOW
impactDescription: "makes impossible states unrepresentable at the type level"
tags: utility-types, discriminated-unions, state-machine, exhaustive-check
---

## Discriminated Unions for State

**Impact: LOW (makes impossible states unrepresentable at the type level)**

Instead of combining booleans and nullable fields (where impossible combinations exist), use a discriminated union where each state variant carries only the data it needs. A `never` check in the default case ensures exhaustiveness at compile time.

## Incorrect

```tsx
// ❌ Bad
interface RequestState<T> {
  isLoading: boolean;
  error: string | null;
  data: T | null;
}

function UserProfile() {
  const [state, setState] = useState<RequestState<User>>({
    isLoading: false,
    error: null,
    data: null,
  });

  // Impossible states are representable:
  // { isLoading: true, error: "fail", data: someUser } — loading AND error AND data?
  // { isLoading: false, error: null, data: null } — not loading, no error, no data?

  if (state.isLoading) {
    return <p>Loading...</p>;
  }

  if (state.error) {
    return <p>Error: {state.error}</p>;
  }

  // TypeScript still thinks data could be null here
  return <div>{state.data?.name}</div>;
}
```

**Problems:**
- `isLoading: true` + `data: someUser` is representable but makes no sense
- `isLoading: true` + `error: "fail"` is representable but contradictory
- After checking `isLoading` and `error`, TypeScript still can't narrow `data` to non-null
- Every consumer must add defensive null checks even in states where data is guaranteed

## Correct

```tsx
// ✅ Good

// Each state has only the properties it needs
type AsyncState<T> =
  | { status: "idle" }
  | { status: "loading" }
  | { status: "error"; error: Error }
  | { status: "success"; data: T };

// Exhaustive check helper — compile error if a case is missed
function assertNever(value: never): never {
  throw new Error(`Unexpected value: ${value}`);
}

interface User {
  id: string;
  name: string;
  email: string;
}

function UserProfile() {
  const [state, setState] = useState<AsyncState<User>>({ status: "idle" });

  const fetchUser = async (id: string) => {
    setState({ status: "loading" });

    try {
      const response = await fetch(`/api/users/${id}`);
      if (!response.ok) throw new Error("Failed to fetch");
      const data: User = await response.json();
      setState({ status: "success", data }); // data is required here
    } catch (e) {
      setState({ status: "error", error: e instanceof Error ? e : new Error(String(e)) });
    }
  };

  // Render with exhaustive switch
  switch (state.status) {
    case "idle":
      return <button onClick={() => fetchUser("1")}>Load User</button>;

    case "loading":
      return <p>Loading...</p>;

    case "error":
      // state.error is Error — guaranteed by the union
      return <p>Error: {state.error.message}</p>;

    case "success":
      // state.data is User — guaranteed, no null check needed
      return (
        <div>
          <h1>{state.data.name}</h1>
          <p>{state.data.email}</p>
        </div>
      );

    default:
      // Compile error if a new status is added but not handled
      return assertNever(state);
  }
}

// Discriminated unions for component variants
type NotificationProps =
  | { type: "success"; message: string }
  | { type: "error"; message: string; retry: () => void }
  | { type: "warning"; message: string; dismissable: boolean };

function Notification(props: NotificationProps) {
  switch (props.type) {
    case "success":
      return <div className="success">{props.message}</div>;

    case "error":
      return (
        <div className="error">
          {props.message}
          <button onClick={props.retry}>Retry</button>
        </div>
      );

    case "warning":
      return (
        <div className="warning">
          {props.message}
          {props.dismissable && <button>Dismiss</button>}
        </div>
      );

    default:
      return assertNever(props);
  }
}

// Usage — TypeScript enforces the correct shape for each type
<Notification type="success" message="Saved!" />
<Notification type="error" message="Failed" retry={() => refetch()} />
<Notification type="warning" message="Disk almost full" dismissable />

// Compile errors:
// <Notification type="error" message="Failed" />  // Missing 'retry'
// <Notification type="success" message="OK" retry={() => {}} />  // 'retry' doesn't exist on success
```

**Benefits:**
- Impossible states (loading + data, error + success) cannot be represented
- TypeScript narrows the type inside each `case` branch, so `data` and `error` are non-null where guaranteed
- `assertNever` in the default case causes a compile error when a new variant is added but not handled
- Component variant props are enforced: `error` requires `retry`, `success` does not accept it

Reference: [React TypeScript Cheatsheet — Discriminated Unions](https://react-typescript-cheatsheet.netlify.app/docs/basic/troubleshooting/types#discriminated-unions)
