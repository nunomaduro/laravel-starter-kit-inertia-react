# State Management (React Query + Zustand)

**Version 1.1.0** | TanStack Query v5 | Zustand v5 | March 2026

Patterns for server state (TanStack Query v5) and client state (Zustand v5).

## Overview

This skill provides guidance for:
- Data fetching with TanStack Query v5
- Caching and invalidation strategies
- Mutations and optimistic updates
- Client state with Zustand v5
- Combining both libraries

## v5 Compatibility Notes

### TanStack Query v5
- All APIs use single object argument: `useQuery({ queryKey, queryFn, ...options })`
- `cacheTime` renamed to `gcTime`
- `keepPreviousData` removed → use `placeholderData: keepPreviousData` or identity function
- `isPreviousData` removed → use `isPlaceholderData`
- `onSuccess`, `onError`, `onSettled` callbacks removed from `useQuery` (still valid on `useMutation`)
- `suspense: true` on `useQuery` removed → use `useSuspenseQuery`

### Zustand v5
- `shallow` as 2nd argument to store hook removed → use `useShallow` from `zustand/shallow`
- Selectors returning new object/array references may cause infinite loops — wrap with `useShallow`

## Security: Persist Middleware

> **Never persist auth tokens, passwords, or secrets to localStorage/sessionStorage.**
> These are accessible to any JavaScript on the page — an XSS attack fully exposes them.
> Use `partialize` to persist only non-sensitive UI state. Manage auth tokens via HttpOnly cookies server-side.

## Categories

### 1. React Query Basics (Critical)
Setup, useQuery, query keys, and error handling.

### 2. Zustand Store Patterns (Critical)
Store creation, TypeScript, selectors, and secure persistence.

### 3. Caching & Invalidation (High)
Stale time, gc time, invalidation, and prefetching.

### 4. Mutations & Updates (High)
useMutation, callbacks, and cache updates.

### 5. Optimistic Updates (Medium)
Optimistic UI updates with rollback.

### 6. DevTools & Debugging (Medium)
Development tools for both libraries.

## Server State vs Client State

| Server State (React Query) | Client State (Zustand) |
|---------------------------|----------------------|
| Data from APIs | UI state |
| Cached remotely | Local only |
| May be stale | Always current |
| Needs refetching | No fetching |
| Examples: users, posts | Examples: modals, themes |

## Quick Start

```tsx
// React Query v5: Server state
const { data, isLoading } = useQuery({
  queryKey: ['posts'],
  queryFn: fetchPosts,
})

// Zustand v5: Client state
const useUIStore = create((set) => ({
  isModalOpen: false,
  openModal: () => set({ isModalOpen: true }),
  closeModal: () => set({ isModalOpen: false }),
}))

// Zustand v5: Shallow selector (prevents infinite loops)
import { useShallow } from 'zustand/shallow'
const { count, text } = useStore(useShallow((s) => ({ count: s.count, text: s.text })))
```

## Usage

This skill triggers automatically when:
- Fetching data from APIs
- Managing application state
- Implementing caching
- Handling mutations

## References

- [TanStack Query v5 Docs](https://tanstack.com/query/latest)
- [TanStack Query v5 Migration Guide](https://tanstack.com/query/latest/docs/react/guides/migrating-to-v5)
- [Zustand Docs](https://docs.pmnd.rs/zustand/getting-started/introduction)
- [Zustand v5 Migration Guide](https://github.com/pmndrs/zustand/blob/main/docs/reference/migrations/migrating-to-v5.md)
- [Zustand Persist Middleware](https://docs.pmnd.rs/zustand/integrations/persisting-store-data)
