# Frontend utilities (TanStack Pacer, Ranger, Virtual)

The app includes TanStack utilities for rate-limiting, ranges, and virtualization. Use them where appropriate; Inertia and server-driven flows remain the primary patterns.

## TanStack Pacer (`@tanstack/pacer`)

Use for debouncing, throttling, and rate-limiting:

- **Debouncer**: For search inputs or filters, use the `debouncer` export so that API or filter updates run after the user stops typing.
- **Throttler**: For scroll or resize handlers where you want at most one run per interval.

Example (debounce a search term):

```ts
import { createDebouncer } from '@tanstack/pacer/debouncer';

const debouncer = createDebouncer({ delay: 300 });
const debouncedSearch = debouncer.enqueue((term: string) => {
  // run search or set state
});
// Call debouncedSearch(term) on input change.
```

## TanStack Ranger (`@tanstack/ranger`)

Use for range selection (e.g. min–max price, date range). The app’s slider UI can be backed by Ranger for controlled range state. See the shadcn slider component and Ranger docs for a combined example.

## TanStack Virtual (`@tanstack/react-virtual`)

Use `useVirtualizer` for long lists (e.g. 100+ items) to only render visible rows and keep scrolling performant. Suitable for changelog or help article lists, or client-side virtualized table rows when not using server-side pagination.

Example:

```ts
import { useVirtualizer } from '@tanstack/react-virtual';
// Use with a scroll container ref and row count; render only virtualizer.getVirtualItems().
```

## DataTable search

The DataTable and command palette use their built-in filtering; for custom search inputs that hit the server, wire Pacer’s debouncer as above.
