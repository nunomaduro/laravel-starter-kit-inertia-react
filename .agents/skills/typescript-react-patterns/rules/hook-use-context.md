---
title: useContext Typing
impact: CRITICAL
impactDescription: "prevents silent failures from undefined context access"
tags: hook, useContext, context, provider
---

## useContext Typing

**Impact: CRITICAL (prevents silent failures from undefined context access)**

Properly typing Context and useContext for type-safe global state. Using null defaults with custom guard hooks prevents silent failures when context is used outside its provider.

## Incorrect

```tsx
// ❌ Bad
// Untyped context - no type safety
const AppContext = React.createContext(undefined);

// Using 'any' for context value
const ThemeContext = React.createContext<any>({ theme: 'light' });

// Default value that doesn't match actual usage
interface User {
  id: string;
  name: string;
}

const UserContext = React.createContext<User>({
  id: '',
  name: '',
}); // Fake default encourages using context outside provider

// Not handling missing provider
const AuthContext = React.createContext<AuthContextValue | undefined>(undefined);

function useAuth() {
  const context = React.useContext(AuthContext);
  return context; // Could be undefined, but callers don't know
}
```

**Problems:**
- Untyped context defaults to `any` with no type checking
- Using `any` removes autocomplete and error detection
- Fake default values mask missing providers and create misleading behavior
- Returning potentially undefined context without a guard leaves callers unprotected

## Correct

```tsx
// ✅ Good
import React, { createContext, useContext, useState, useCallback, useMemo } from 'react';

// Pattern 1: Context with null default and custom hook
interface User {
  id: string;
  name: string;
  email: string;
}

interface AuthContextValue {
  user: User | null;
  isAuthenticated: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => void;
}

const AuthContext = createContext<AuthContextValue | null>(null);
AuthContext.displayName = 'AuthContext';

function useAuth(): AuthContextValue {
  const context = useContext(AuthContext);

  if (context === null) {
    throw new Error('useAuth must be used within an AuthProvider');
  }

  return context;
}

// assume: const api = createApiClient()
function AuthProvider({ children }: { children: React.ReactNode }): React.ReactElement {
  const [user, setUser] = useState<User | null>(null);

  const login = useCallback(async (email: string, password: string) => {
    const response = await api.login(email, password);
    setUser(response.user);
  }, []);

  const logout = useCallback(() => {
    setUser(null);
  }, []);

  const value = useMemo<AuthContextValue>(
    () => ({
      user,
      isAuthenticated: user !== null,
      login,
      logout,
    }),
    [user, login, logout]
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

// Usage in component
function UserProfile(): React.ReactElement {
  const { user, logout } = useAuth(); // Guaranteed to be non-null

  if (!user) {
    return <div>Please log in</div>;
  }

  return (
    <div>
      <h1>{user.name}</h1>
      <p>{user.email}</p>
      <button onClick={logout}>Logout</button>
    </div>
  );
}

// Pattern 2: Multiple contexts for separation of concerns
interface ThemeContextValue {
  theme: 'light' | 'dark';
  colors: {
    primary: string;
    background: string;
    text: string;
  };
}

interface ThemeActionsContextValue {
  setTheme: (theme: 'light' | 'dark') => void;
  toggleTheme: () => void;
}

const ThemeContext = createContext<ThemeContextValue | null>(null);
const ThemeActionsContext = createContext<ThemeActionsContextValue | null>(null);

function useTheme(): ThemeContextValue {
  const context = useContext(ThemeContext);
  if (!context) {
    throw new Error('useTheme must be used within ThemeProvider');
  }
  return context;
}

function useThemeActions(): ThemeActionsContextValue {
  const context = useContext(ThemeActionsContext);
  if (!context) {
    throw new Error('useThemeActions must be used within ThemeProvider');
  }
  return context;
}

// Pattern 3: Generic context factory
function createSafeContext<T>(displayName: string) {
  const Context = createContext<T | null>(null);
  Context.displayName = displayName;

  function useContextSafe(): T {
    const context = useContext(Context);
    if (context === null) {
      throw new Error(`use${displayName} must be used within ${displayName}Provider`);
    }
    return context;
  }

  return [Context.Provider, useContextSafe] as const;
}

// Usage of factory
interface AppNotification { id: string; message: string; type: 'info' | 'error' | 'success' }

interface NotificationContextValue {
  notifications: AppNotification[];
  addNotification: (notification: Omit<AppNotification, 'id'>) => void;
  removeNotification: (id: string) => void;
}

const [NotificationProvider, useNotifications] = createSafeContext<NotificationContextValue>('Notification');

// Pattern 4: Context with reducer
interface CartItem {
  id: string;
  name: string;
  price: number;
  quantity: number;
}

type CartAction =
  | { type: 'ADD_ITEM'; payload: Omit<CartItem, 'quantity'> }
  | { type: 'REMOVE_ITEM'; payload: string }
  | { type: 'UPDATE_QUANTITY'; payload: { id: string; quantity: number } }
  | { type: 'CLEAR' };

interface CartState {
  items: CartItem[];
  total: number;
}

interface CartContextValue {
  state: CartState;
  dispatch: React.Dispatch<CartAction>;
  itemCount: number;
  addItem: (item: Omit<CartItem, 'quantity'>) => void;
  removeItem: (id: string) => void;
}

const CartContext = createContext<CartContextValue | null>(null);

function useCart(): CartContextValue {
  const context = useContext(CartContext);
  if (!context) {
    throw new Error('useCart must be used within CartProvider');
  }
  return context;
}
```

**Benefits:**
- Null default with error boundary prevents silent failures
- Custom hooks encapsulate context access and error handling
- Separated contexts split state and actions to prevent unnecessary re-renders
- Factory pattern reduces boilerplate for creating typed contexts
- Memoized provider values prevent unnecessary re-renders
- DisplayName improves debugging in React DevTools

Reference: [React TypeScript Cheatsheet](https://react-typescript-cheatsheet.netlify.app)
