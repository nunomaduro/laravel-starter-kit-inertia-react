---
title: Typed Context Provider Pattern
impact: MEDIUM
impactDescription: "eliminates null checks at every useContext call site"
tags: context, provider, createContext, useContext
---

## Typed Context Provider Pattern

**Impact: MEDIUM (eliminates null checks at every useContext call site)**

The recommended pattern is `createContext<Type | null>(null)` paired with a custom hook that throws if the context is used outside the provider. This keeps the default value honest (no dummy objects) while giving consumers a non-null type.

## Incorrect

```tsx
// ❌ Bad
// Fake default value — misleading and breaks if used outside provider
const AuthContext = createContext<AuthState>({
  user: null,
  token: "",
  login: () => {},      // Dummy no-op — silently does nothing
  logout: () => {},     // Dummy no-op
  isAuthenticated: false,
});

// Every consumer must handle potential undefined anyway
function Profile() {
  const { user } = useContext(AuthContext);
  // user could still be null, but TypeScript thinks the context always exists
  return <div>{user?.name}</div>;
}
```

**Problems:**
- Fake default values silently do nothing when the provider is missing
- No-op functions like `login: () => {}` mask bugs — the app appears to work but does nothing
- No error when a component accidentally renders outside the provider tree
- Consumers get a false sense of type safety from the dummy default

## Correct

```tsx
// ✅ Good

// 1. Define the context type
interface AuthContextType {
  user: User | null;
  token: string;
  isAuthenticated: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => void;
}

interface User {
  id: string;
  name: string;
  email: string;
}

// 2. Create context with null default — honest about having no value
const AuthContext = createContext<AuthContextType | null>(null);

// 3. Custom hook with null check and descriptive error
function useAuth(): AuthContextType {
  const context = useContext(AuthContext);
  if (context === null) {
    throw new Error("useAuth must be used within an AuthProvider");
  }
  return context; // Return type is AuthContextType, not AuthContextType | null
}

// 4. Provider component encapsulates state logic
function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [token, setToken] = useState("");

  const login = async (email: string, password: string) => {
    const response = await fetch("/api/login", {
      method: "POST",
      body: JSON.stringify({ email, password }),
    });
    const data = await response.json();
    setUser(data.user);
    setToken(data.token);
  };

  const logout = () => {
    setUser(null);
    setToken("");
  };

  const value: AuthContextType = {
    user,
    token,
    isAuthenticated: user !== null,
    login,
    logout,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

// 5. Consumers use the custom hook — no null checks needed
function Profile() {
  const { user, isAuthenticated, logout } = useAuth();
  // user is User | null (from the interface), but the context itself is guaranteed

  if (!isAuthenticated || !user) {
    return <p>Please log in.</p>;
  }

  return (
    <div>
      <h1>{user.name}</h1>
      <p>{user.email}</p>
      <button onClick={logout}>Logout</button>
    </div>
  );
}

// 6. App wraps the tree with the provider
function App() {
  return (
    <AuthProvider>
      <Profile />
    </AuthProvider>
  );
}
```

**Benefits:**
- `createContext<T | null>(null)` avoids fake defaults that mask missing providers
- Custom `useAuth` hook throws a clear error if used outside `AuthProvider`
- Consumers get `AuthContextType` (not `AuthContextType | null`), removing redundant null checks
- Provider encapsulates all state logic in one place

Reference: [React TypeScript Cheatsheet — Context](https://react-typescript-cheatsheet.netlify.app/docs/basic/getting-started/context)
