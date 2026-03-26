---
title: Configure HMR for Optimal Development
impact: HIGH
impactDescription: "Fast, reliable hot updates"
tags: dev, hmr, hot-reload, development, vite
---

## Configure HMR for Optimal Development

**Impact: HIGH (Fast, reliable hot updates)**

Configure Vite's Hot Module Replacement (HMR) for optimal development experience with fast, reliable updates.

## Incorrect

```tsx
// ❌ Bad: No HMR configuration
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  // HMR works with defaults but may have issues in certain environments
});
```

```tsx
// ❌ Bad: Module-level mutable state breaks HMR
let userCache = {};

export function UserProvider({ children }) {
  const [user, setUser] = useState(() => {
    return userCache.current || null;
  });

  useEffect(() => {
    userCache.current = user;
  }, [user]);

  return (
    <UserContext.Provider value={{ user, setUser }}>
      {children}
    </UserContext.Provider>
  );
}
// HMR causes state loss and unexpected behavior
```

**Problems:**
- Default HMR config fails in Docker, WSL, and network drive environments
- Module-level mutable state persists across HMR updates, causing bugs
- Missing watch configuration leads to undetected file changes
- No error overlay makes debugging harder during development

## Correct

```tsx
// ✅ Good: Properly configured HMR
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [
    react({
      fastRefresh: true,
    }),
  ],
  server: {
    hmr: {
      overlay: true,
      protocol: 'ws',
    },
    watch: {
      usePolling: process.env.USE_POLLING === 'true',
      ignored: ['**/node_modules/**', '**/dist/**'],
    },
  },
});
```

```tsx
// ✅ Good: Docker/WSL optimized HMR
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  server: {
    host: '0.0.0.0',
    hmr: {
      host: 'localhost',
      clientPort: 5173,
    },
    watch: {
      usePolling: true,
      interval: 1000,
    },
  },
});
```

```tsx
// ✅ Good: HMR-compatible state management with Zustand
import { create } from 'zustand';

interface UserState {
  user: User | null;
  setUser: (user: User | null) => void;
}

export const useUserStore = create<UserState>((set) => ({
  user: null,
  setUser: (user) => set({ user }),
}));
// HMR will preserve store state automatically
```

```tsx
// ✅ Good: Custom HMR handling for special cases
import axios from 'axios';

export const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  timeout: 10000,
});

if (import.meta.hot) {
  import.meta.hot.dispose(() => {
    apiClient.interceptors.request.clear();
    apiClient.interceptors.response.clear();
  });
}

apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});
```

```tsx
// ✅ Good: HMR-compatible context with explicit accept
import { createContext, useContext, useState, useCallback } from 'react';

const ThemeContext = createContext<ThemeContextValue | null>(null);

export function ThemeProvider({ children }: { children: React.ReactNode }) {
  const [theme, setTheme] = useState<'light' | 'dark'>('light');

  const toggleTheme = useCallback(() => {
    setTheme((prev) => (prev === 'light' ? 'dark' : 'light'));
  }, []);

  return (
    <ThemeContext.Provider value={{ theme, toggleTheme }}>
      {children}
    </ThemeContext.Provider>
  );
}

if (import.meta.hot) {
  import.meta.hot.accept();
}
```

**Benefits:**
- Changes reflect in the browser in milliseconds, not seconds
- React Fast Refresh maintains component state during updates
- Clear error overlays help quickly identify and fix issues
- Docker/WSL configuration ensures HMR works in containerized environments
- Custom `import.meta.hot` handling prevents memory leaks during long sessions

| Issue | Cause | Solution |
|-------|-------|----------|
| Full page reload | Export not a component | Check default exports |
| State lost | Module-level state | Use state management library |
| Changes not detected | File system events | Enable polling |
| Connection errors | Port/protocol mismatch | Configure hmr.clientPort |
| Slow updates | Large dep chain | Optimize with optimizeDeps |

Reference: [Vite HMR API](https://vitejs.dev/guide/api-hmr.html) | [Vite Server Options](https://vitejs.dev/config/server-options.html)
