---
id: resp-mobile-first
title: Mobile-First Responsive Design
priority: CRITICAL
category: Responsive Design
---

# Mobile-First Responsive Design

## Why It Matters

Tailwind is mobile-first by default. Unprefixed utilities apply to all screen sizes, and prefixed utilities (sm:, md:, lg:) apply at that breakpoint and up. Understanding this prevents confusion and produces cleaner code.

## Breakpoints

| Prefix | Min-Width | CSS |
|--------|-----------|-----|
| (none) | 0px | Default (mobile) |
| sm: | 640px | @media (min-width: 640px) |
| md: | 768px | @media (min-width: 768px) |
| lg: | 1024px | @media (min-width: 1024px) |
| xl: | 1280px | @media (min-width: 1280px) |
| 2xl: | 1536px | @media (min-width: 1536px) |

## Incorrect

```tsx
// ❌ Desktop-first thinking (confusing)
<div className="w-1/4 lg:w-1/3 md:w-1/2 sm:w-full">
  // Hard to understand, order matters differently than expected
</div>

// ❌ Overriding mobile styles at every breakpoint
<div className="hidden sm:block md:block lg:block xl:block">
  // Redundant - sm:block already applies to all larger screens
</div>

// ❌ Not starting with mobile
<div className="md:flex md:items-center">
  // What happens on mobile? (default: display: block)
</div>
```

## Correct

```tsx
// ✅ Mobile-first: base styles first, then add breakpoints
<div className="
  w-full           // Mobile: full width
  sm:w-1/2         // Small+: half width
  md:w-1/3         // Medium+: third width
  lg:w-1/4         // Large+: quarter width
">
  Content
</div>

// ✅ Clear progression
<div className="
  flex flex-col    // Mobile: stack vertically
  md:flex-row      // Medium+: row layout
">
  <div>Item 1</div>
  <div>Item 2</div>
</div>

// ✅ Only specify changes at breakpoints
<div className="block sm:hidden">
  Mobile only
</div>
<div className="hidden sm:block">
  Tablet and up
</div>
```

## Common Patterns

### Responsive Grid

```tsx
// Cards: 1 col → 2 col → 3 col → 4 col
<div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
  {items.map(item => <Card key={item.id} />)}
</div>
```

### Responsive Typography

```tsx
<h1 className="
  text-2xl        // Mobile
  md:text-3xl     // Tablet
  lg:text-4xl     // Desktop
  xl:text-5xl     // Large desktop
">
  Heading
</h1>
```

### Responsive Spacing

```tsx
<section className="
  px-4 py-8       // Mobile: smaller padding
  md:px-8 md:py-12  // Tablet: medium padding
  lg:px-16 lg:py-16 // Desktop: larger padding
">
  Content
</section>
```

### Responsive Layout

```tsx
// Stack on mobile, side-by-side on larger screens
<div className="flex flex-col lg:flex-row gap-8">
  <aside className="w-full lg:w-64 lg:flex-shrink-0">
    Sidebar
  </aside>
  <main className="flex-1">
    Main content
  </main>
</div>
```

### Responsive Hide/Show

```tsx
// Mobile navigation (hamburger menu)
<nav className="md:hidden">
  <button>Menu</button>
</nav>

// Desktop navigation
<nav className="hidden md:flex space-x-4">
  <a href="/">Home</a>
  <a href="/about">About</a>
</nav>
```

## Thinking Process

1. **Start with mobile design** - What should this look like on a phone?
2. **Add tablet changes** - At md:, what needs to change?
3. **Add desktop changes** - At lg:, what needs to change?
4. **Only add what changes** - Don't repeat styles that stay the same

## Impact

- Cleaner, more maintainable code
- Smaller CSS output
- Better mental model for responsive design
- Easier to debug responsive issues
