---
title: Persist Middleware
impact: CRITICAL
section: Zustand Stores
tags: zustand, persistence, localstorage
---

# zs-persist

**Impact: CRITICAL**


**Priority:** CRITICAL
**Category:** Zustand Store Patterns

## Why It Matters

The persist middleware saves Zustand state to storage (localStorage, sessionStorage, etc.), enabling state to survive page refreshes. Useful for user preferences, UI settings, and cart data.

## Security Warning

> **Do NOT persist authentication tokens, passwords, or secrets to localStorage or sessionStorage.**
> These storages are accessible to any JavaScript on the page — an XSS vulnerability fully exposes them.
> Auth tokens should be stored in **HttpOnly cookies** managed server-side.
> If you must track auth state client-side, persist only non-sensitive flags (e.g. `isAuthenticated: boolean`) and re-validate server-side on every request.

## Basic Persistence

```tsx
import { create } from 'zustand'
import { persist } from 'zustand/middleware'

interface SettingsState {
  theme: 'light' | 'dark'
  fontSize: number
  setTheme: (theme: 'light' | 'dark') => void
  setFontSize: (size: number) => void
}

export const useSettingsStore = create<SettingsState>()(
  persist(
    (set) => ({
      theme: 'light',
      fontSize: 16,
      setTheme: (theme) => set({ theme }),
      setFontSize: (fontSize) => set({ fontSize }),
    }),
    {
      name: 'settings-storage', // localStorage key
    }
  )
)
```

## With TypeScript and DevTools

```tsx
import { create } from 'zustand'
import { persist, devtools } from 'zustand/middleware'

interface AuthState {
  user: User | null
  isAuthenticated: boolean
  login: (user: User) => void
  logout: () => void
}

// ✅ Only persist non-sensitive flags — never persist tokens or passwords
export const useAuthStore = create<AuthState>()(
  devtools(
    persist(
      (set) => ({
        user: null,
        isAuthenticated: false,

        login: (user) =>
          set(
            { user, isAuthenticated: true },
            false,
            'auth/login'
          ),

        logout: () =>
          set(
            { user: null, isAuthenticated: false },
            false,
            'auth/logout'
          ),
      }),
      {
        name: 'auth-storage',
        // Only persist the user display info and auth flag, NOT tokens
        partialize: (state) => ({
          user: state.user,
          isAuthenticated: state.isAuthenticated,
        }),
      }
    ),
    { name: 'AuthStore' }
  )
)
// ❌ Tokens must NOT be stored here — use HttpOnly cookies managed server-side
```

## Partial Persistence

```tsx
interface CartState {
  items: CartItem[]
  isOpen: boolean  // Don't persist UI state
  addItem: (item: CartItem) => void
  toggle: () => void
}

export const useCartStore = create<CartState>()(
  persist(
    (set) => ({
      items: [],
      isOpen: false,
      addItem: (item) => set((state) => ({ items: [...state.items, item] })),
      toggle: () => set((state) => ({ isOpen: !state.isOpen })),
    }),
    {
      name: 'cart-storage',
      // Only persist items, not isOpen
      partialize: (state) => ({ items: state.items }),
    }
  )
)
```

## Custom Storage

```tsx
import { persist, createJSONStorage } from 'zustand/middleware'

// SessionStorage instead of localStorage
export const useSessionStore = create<State>()(
  persist(
    (set) => ({
      // ...
    }),
    {
      name: 'session-storage',
      storage: createJSONStorage(() => sessionStorage),
    }
  )
)

// Custom storage (e.g., for React Native AsyncStorage)
import AsyncStorage from '@react-native-async-storage/async-storage'

const customStorage = {
  getItem: async (name: string) => {
    const value = await AsyncStorage.getItem(name)
    return value ? JSON.parse(value) : null
  },
  setItem: async (name: string, value: unknown) => {
    await AsyncStorage.setItem(name, JSON.stringify(value))
  },
  removeItem: async (name: string) => {
    await AsyncStorage.removeItem(name)
  },
}

export const useStore = create<State>()(
  persist(
    (set) => ({
      // ...
    }),
    {
      name: 'my-storage',
      storage: createJSONStorage(() => customStorage),
    }
  )
)
```

## Version Migration

```tsx
interface StateV1 {
  count: number
}

interface StateV2 {
  count: number
  doubleCount: number  // New in V2
}

export const useStore = create<StateV2>()(
  persist(
    (set) => ({
      count: 0,
      doubleCount: 0,
      increment: () =>
        set((state) => ({
          count: state.count + 1,
          doubleCount: (state.count + 1) * 2,
        })),
    }),
    {
      name: 'my-storage',
      version: 2,  // Current version
      migrate: (persistedState: unknown, version: number) => {
        if (version === 1) {
          // Migrate from V1 to V2
          const state = persistedState as StateV1
          return {
            ...state,
            doubleCount: state.count * 2,
          }
        }
        return persistedState as StateV2
      },
    }
  )
)
```

## Hydration Handling

```tsx
// Handle hydration mismatch (SSR)
function useHydration() {
  const [hydrated, setHydrated] = useState(false)

  useEffect(() => {
    // Check if store has rehydrated
    const unsubscribe = useAuthStore.persist.onFinishHydration(() => {
      setHydrated(true)
    })

    // If already hydrated
    if (useAuthStore.persist.hasHydrated()) {
      setHydrated(true)
    }

    return unsubscribe
  }, [])

  return hydrated
}

// Usage
function App() {
  const hydrated = useHydration()

  if (!hydrated) {
    return <LoadingScreen />
  }

  return <MainApp />
}
```

## Skip Hydration

```tsx
// For SSR: Skip hydration to avoid mismatch
export const useStore = create<State>()(
  persist(
    (set) => ({
      // ...
    }),
    {
      name: 'my-storage',
      skipHydration: true,  // Manual hydration
    }
  )
)

// Hydrate manually in useEffect
useEffect(() => {
  useStore.persist.rehydrate()
}, [])
```

## Clear Persisted State

```tsx
// Clear storage and reset state
function clearAllData() {
  useAuthStore.persist.clearStorage()
  useCartStore.persist.clearStorage()
  useSettingsStore.persist.clearStorage()

  // Optionally reset to initial state
  useAuthStore.setState({ user: null, isAuthenticated: false })
}
```

## Benefits

- State survives page refresh
- Works with various storage backends
- Version migration for schema changes
- Partial persistence for selective saving
- SSR compatible with hydration control
