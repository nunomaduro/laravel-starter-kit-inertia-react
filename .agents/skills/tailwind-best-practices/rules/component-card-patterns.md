---
id: component-card-patterns
title: Card Patterns
priority: HIGH
category: Component Patterns
---

# Card Patterns

Create flexible, consistent card components for displaying grouped content with proper structure and styling.

## Bad Example

```html
<!-- Inconsistent card structure -->
<div class="bg-white p-4 shadow">
  <img src="image.jpg">
  <h3>Title</h3>
  <p>Content</p>
</div>

<!-- Missing proper spacing and overflow handling -->
<div class="border p-2">
  <img src="wide-image.jpg" class="w-full">
  <div>
    <h3 class="text-lg">Long title that might overflow the container</h3>
    <p>Description text</p>
  </div>
</div>

<!-- No interactive states for clickable cards -->
<div onclick="navigate()" class="bg-white shadow p-4">
  Click me (no visual feedback)
</div>
```

## Good Example

```html
<!-- Basic card with proper structure -->
<article class="
  bg-white dark:bg-gray-800
  rounded-xl shadow-sm
  border border-gray-200 dark:border-gray-700
  overflow-hidden
">
  <div class="aspect-video">
    <img class="w-full h-full object-cover" src="image.jpg" alt="Description">
  </div>
  <div class="p-6">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
      Card Title
    </h3>
    <p class="mt-2 text-gray-600 dark:text-gray-300">
      Card description with supporting text.
    </p>
  </div>
</article>

<!-- Interactive card with hover state -->
<a href="/article" class="
  block
  bg-white dark:bg-gray-800
  rounded-xl shadow-sm
  border border-gray-200 dark:border-gray-700
  overflow-hidden
  transition-all duration-200
  hover:shadow-md hover:border-gray-300 dark:hover:border-gray-600
  focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2
">
  <div class="aspect-[3/2]">
    <img class="w-full h-full object-cover" src="image.jpg" alt="">
  </div>
  <div class="p-6">
    <span class="text-xs font-medium text-primary-600 dark:text-primary-400 uppercase tracking-wide">
      Category
    </span>
    <h3 class="mt-2 text-lg font-semibold text-gray-900 dark:text-white line-clamp-2">
      Article Title That Might Be Long
    </h3>
    <p class="mt-2 text-gray-600 dark:text-gray-300 line-clamp-3">
      Description text that will be truncated after three lines...
    </p>
  </div>
</a>

<!-- Horizontal card -->
<article class="
  flex flex-col sm:flex-row
  bg-white dark:bg-gray-800
  rounded-xl shadow-sm
  border border-gray-200 dark:border-gray-700
  overflow-hidden
">
  <div class="sm:w-48 sm:flex-shrink-0">
    <img class="w-full h-48 sm:h-full object-cover" src="image.jpg" alt="">
  </div>
  <div class="p-6 flex flex-col justify-between">
    <div>
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Title</h3>
      <p class="mt-2 text-gray-600 dark:text-gray-300">Description</p>
    </div>
    <div class="mt-4">
      <button class="text-primary-600 dark:text-primary-400 font-medium">
        Read more
      </button>
    </div>
  </div>
</article>
```

## Why

1. **Consistent structure**: All cards follow the same pattern for predictable layouts.

2. **Proper overflow**: Images and text are contained within card boundaries.

3. **Accessibility**: Semantic HTML and proper focus states for interactive cards.

4. **Dark mode ready**: Colors adapt to both light and dark themes.

5. **Responsive design**: Cards adapt from mobile to desktop layouts.

## Card Variants

### Simple Card
```html
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
  <h3 class="font-semibold text-gray-900 dark:text-white">Simple Card</h3>
  <p class="mt-2 text-gray-600 dark:text-gray-300">Content goes here.</p>
</div>
```

### Card with Header
```html
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
  <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
    <h3 class="font-semibold text-gray-900 dark:text-white">Card Header</h3>
  </div>
  <div class="p-6">
    <p class="text-gray-600 dark:text-gray-300">Card body content.</p>
  </div>
</div>
```

### Card with Footer
```html
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
  <div class="p-6">
    <h3 class="font-semibold text-gray-900 dark:text-white">Card Title</h3>
    <p class="mt-2 text-gray-600 dark:text-gray-300">Card content.</p>
  </div>
  <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
    <button class="text-primary-600 dark:text-primary-400 font-medium">Action</button>
  </div>
</div>
```

### Profile Card
```html
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 text-center">
  <img class="w-24 h-24 rounded-full mx-auto ring-4 ring-gray-100 dark:ring-gray-700" src="avatar.jpg" alt="">
  <h3 class="mt-4 font-semibold text-gray-900 dark:text-white">John Doe</h3>
  <p class="text-sm text-gray-500 dark:text-gray-400">Software Engineer</p>
  <div class="mt-4 flex justify-center gap-3">
    <button class="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm">Follow</button>
    <button class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm">Message</button>
  </div>
</div>
```

### Stats Card
```html
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
  <div class="flex items-center justify-between">
    <div>
      <p class="text-sm text-gray-500 dark:text-gray-400">Total Revenue</p>
      <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">$45,231</p>
    </div>
    <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-full">
      <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
      </svg>
    </div>
  </div>
  <p class="mt-2 text-sm text-green-600 dark:text-green-400">
    <span class="font-medium">+12.5%</span> from last month
  </p>
</div>
```

## Card Grid Layout

```html
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
  <!-- Cards automatically fill grid -->
</div>

<!-- Or auto-fit for flexible columns -->
<div class="grid grid-cols-[repeat(auto-fit,minmax(280px,1fr))] gap-6">
  <!-- Cards adapt to available space -->
</div>
```
