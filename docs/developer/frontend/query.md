# TanStack Query

The app uses **@tanstack/react-query** for client-side caching and background updates of server data.

## Setup

- **Provider**: `QueryProvider` in `resources/js/providers/query-provider.tsx` wraps the app in `app.tsx`. It creates a single `QueryClient` with a default `staleTime` of 60 seconds.
- **Devtools**: `@tanstack/react-query-devtools` is rendered only when `import.meta.env.DEV` is true (button position: bottom-left).

## Usage

Use `useQuery` for GET endpoints that should be cached and refetched (e.g. autocomplete or search APIs). Prefer Inertia and server-driven data for page-level data; use React Query for auxiliary or frequently updated API data.

Example:

```ts
import { useQuery } from '@tanstack/react-query';

const { data, isLoading, error } = useQuery({
  queryKey: ['users', 'search', term],
  queryFn: () => fetch(`/api/v1/users/search?q=${term}`).then(r => r.json()),
  enabled: term.length >= 2,
});
```
