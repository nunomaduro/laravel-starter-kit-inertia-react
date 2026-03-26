---
title: Interaction to Next Paint Optimization
impact: CRITICAL
impactDescription: "Must be under 200ms (replaced FID in March 2024)"
tags: core-web-vitals, inp, interactivity
---

## Interaction to Next Paint Optimization

**Impact: CRITICAL (Must be under 200ms (replaced FID in March 2024))**

INP measures the latency of every click, tap, and keyboard interaction throughout a page visit, reporting the worst interaction. Since March 2024, INP replaced First Input Delay as a Core Web Vital ranking signal, making responsive interactions essential for SEO.

## Incorrect

```tsx
// ❌ Bad: synchronous heavy computation blocks the main thread on click
export default function ProductFilter({ products }: { products: Product[] }) {
  const handleFilter = (category: string) => {
    // Long-running synchronous task blocks UI for 500ms+
    const filtered = products.filter((product) => {
      // Expensive computation per item
      const score = calculateRelevanceScore(product, category);
      const normalized = normalizeAcrossDataset(score, products);
      return normalized > 0.5;
    });

    // DOM update only happens after entire computation finishes
    setFilteredProducts(filtered);
    updateURL(category);
    trackAnalytics("filter", category);
  };

  return (
    <button onClick={() => handleFilter("electronics")}>
      Filter Electronics
    </button>
  );
}
```

**Problems:**
- The entire filtering, normalization, and DOM update runs synchronously, blocking the main thread
- The browser cannot paint the next frame until the handler completes, causing visible lag
- Analytics and URL updates further extend the blocking time after the critical render

## Correct

```tsx
// ✅ Good: break work into chunks and yield to the main thread
export default function ProductFilter({ products }: { products: Product[] }) {
  const [isPending, startTransition] = useTransition();

  const handleFilter = async (category: string) => {
    // Immediately update UI to show pending state
    startTransition(() => {
      setCategory(category);
    });

    // Offload heavy computation to a Web Worker
    const filtered = await new Promise<Product[]>((resolve) => {
      filterWorker.onmessage = (e) => resolve(e.data);
      filterWorker.postMessage({ products, category });
    });

    startTransition(() => {
      setFilteredProducts(filtered);
    });

    // Defer non-critical work
    requestIdleCallback(() => {
      updateURL(category);
      trackAnalytics("filter", category);
    });
  };

  return (
    <button
      onClick={() => handleFilter("electronics")}
      disabled={isPending}
    >
      {isPending ? "Filtering..." : "Filter Electronics"}
    </button>
  );
}
```

```ts
// ✅ Web Worker: filter-worker.ts — runs off the main thread
self.onmessage = (event: MessageEvent) => {
  const { products, category } = event.data;

  const filtered = products.filter((product: Product) => {
    const score = calculateRelevanceScore(product, category);
    const normalized = normalizeAcrossDataset(score, products);
    return normalized > 0.5;
  });

  self.postMessage(filtered);
};
```

```ts
// ✅ Alternative: yield to main thread using scheduler.yield()
async function processInChunks<T>(
  items: T[],
  callback: (item: T) => boolean,
  chunkSize = 100
): Promise<T[]> {
  const results: T[] = [];

  for (let i = 0; i < items.length; i += chunkSize) {
    const chunk = items.slice(i, i + chunkSize);
    results.push(...chunk.filter(callback));

    // Yield to the main thread between chunks
    if ("scheduler" in globalThis) {
      await (globalThis as any).scheduler.yield();
    } else {
      await new Promise((resolve) => setTimeout(resolve, 0));
    }
  }

  return results;
}
```

**Benefits:**
- `useTransition` provides immediate visual feedback while deferring the expensive re-render
- Web Workers move heavy computation off the main thread entirely, keeping INP near zero
- `requestIdleCallback` defers analytics and URL updates until the browser is idle
- Chunked processing with yielding prevents any single task from blocking the main thread beyond 50ms

Reference: [Optimize Interaction to Next Paint](https://web.dev/articles/optimize-inp)
