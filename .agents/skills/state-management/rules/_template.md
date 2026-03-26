---
title: Rule Title Here
impact: MEDIUM
section: Query Fundamentals
tags: react-query, zustand, state-management
---

# Rule Title Here

**Impact: MEDIUM (optional impact description)**

Brief explanation of the rule and why it matters. Focus on the problem it solves and the value it provides for state management.

## Bad Example

```tsx
// Anti-pattern: Description of what's wrong
const { data } = useQuery({
  queryKey: ['data'],
  queryFn: fetchData,
  // Issues: explains the problems
})

// Another bad pattern example
const mutation = useMutation({
  mutationFn: updateData,
  // Missing important configuration
})
```

## Good Example

```tsx
// Correct pattern: Description of what's right
const { data } = useQuery({
  queryKey: queryKeys.data.list(filters),
  queryFn: () => fetchData(filters),
  staleTime: 5 * 60 * 1000,
  gcTime: 30 * 60 * 1000,
})

// Another good pattern example
const mutation = useMutation({
  mutationFn: updateData,
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: ['data'] })
  },
  onError: (error) => {
    console.error('Update failed:', error)
  },
})

// With custom hook for reusability
export function useDataQuery(filters?: DataFilters) {
  return useQuery({
    queryKey: queryKeys.data.list(filters ?? {}),
    queryFn: () => fetchData(filters),
    staleTime: 5 * 60 * 1000,
  })
}

// Zustand store example
interface DataStore {
  items: Data[]
  addItem: (item: Data) => void
  removeItem: (id: string) => void
}

export const useDataStore = create<DataStore>((set) => ({
  items: [],
  addItem: (item) => set((state) => ({ items: [...state.items, item] })),
  removeItem: (id) => set((state) => ({
    items: state.items.filter((i) => i.id !== id)
  })),
}))
```

## Why

1. **Performance**: Explain the performance benefits with specific metrics or scenarios.

2. **User Experience**: Describe how this improves the user's experience.

3. **Maintainability**: Explain how this makes code easier to maintain and understand.

4. **Type Safety**: If applicable, describe TypeScript benefits.

5. **Consistency**: Explain how this promotes consistent patterns across the codebase.

6. **Error Handling**: Describe how this improves error handling and recovery.

When to use this pattern:
- Specific scenario 1
- Specific scenario 2
- Specific scenario 3

When NOT to use:
- Anti-scenario 1
- Anti-scenario 2

Reference: [Link to relevant documentation](https://tanstack.com/query/latest)
