---
id: component-btn-variants
title: Button Variants
priority: HIGH
category: Component Patterns
---

# Button Variants

Create consistent, reusable button components with proper variants for different contexts and states.

## Bad Example

```html
<!-- Inconsistent button styles across the app -->
<button class="bg-blue-500 text-white px-4 py-2">Submit</button>
<button class="bg-blue-600 text-white px-3 py-1 rounded">Save</button>
<button class="bg-indigo-500 text-white px-6 py-3 rounded-lg">Continue</button>

<!-- Missing interactive states -->
<button class="bg-red-500 text-white px-4 py-2">
  No hover, focus, or disabled states
</button>

<!-- Accessibility issues -->
<div class="bg-green-500 text-white px-4 py-2 cursor-pointer" onclick="submit()">
  Using div instead of button
</div>
```

## Good Example

```html
<!-- Primary button with all states -->
<button class="
  inline-flex items-center justify-center gap-2
  px-4 py-2 rounded-lg
  bg-primary-600 text-white font-medium
  hover:bg-primary-700
  focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2
  active:bg-primary-800
  disabled:opacity-50 disabled:cursor-not-allowed
  transition-colors duration-150
">
  Primary Action
</button>

<!-- Secondary button -->
<button class="
  inline-flex items-center justify-center gap-2
  px-4 py-2 rounded-lg
  bg-gray-100 text-gray-900 font-medium
  hover:bg-gray-200
  focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2
  active:bg-gray-300
  disabled:opacity-50 disabled:cursor-not-allowed
  dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700
  transition-colors duration-150
">
  Secondary Action
</button>

<!-- Outline button -->
<button class="
  inline-flex items-center justify-center gap-2
  px-4 py-2 rounded-lg
  border-2 border-primary-600 text-primary-600 font-medium
  hover:bg-primary-50
  focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2
  active:bg-primary-100
  disabled:opacity-50 disabled:cursor-not-allowed
  dark:border-primary-400 dark:text-primary-400 dark:hover:bg-primary-950
  transition-colors duration-150
">
  Outline Action
</button>

<!-- Ghost button -->
<button class="
  inline-flex items-center justify-center gap-2
  px-4 py-2 rounded-lg
  text-gray-600 font-medium
  hover:bg-gray-100 hover:text-gray-900
  focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2
  active:bg-gray-200
  disabled:opacity-50 disabled:cursor-not-allowed
  dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100
  transition-colors duration-150
">
  Ghost Action
</button>

<!-- Destructive button -->
<button class="
  inline-flex items-center justify-center gap-2
  px-4 py-2 rounded-lg
  bg-red-600 text-white font-medium
  hover:bg-red-700
  focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2
  active:bg-red-800
  disabled:opacity-50 disabled:cursor-not-allowed
  transition-colors duration-150
">
  Delete
</button>
```

## Why

1. **Consistency**: All buttons look and behave the same way across the application.

2. **Accessibility**: Proper focus states, disabled states, and semantic HTML.

3. **User feedback**: Hover, active, and focus states communicate interactivity.

4. **Dark mode support**: Buttons work in both light and dark themes.

5. **Flexibility**: Size and variant options cover all use cases.

## Size Variants

```html
<!-- Extra small -->
<button class="px-2.5 py-1.5 text-xs rounded">XS</button>

<!-- Small -->
<button class="px-3 py-2 text-sm rounded-md">Small</button>

<!-- Medium (default) -->
<button class="px-4 py-2 text-sm rounded-lg">Medium</button>

<!-- Large -->
<button class="px-6 py-3 text-base rounded-lg">Large</button>

<!-- Extra large -->
<button class="px-8 py-4 text-lg rounded-xl">XL</button>
```

## Icon Buttons

```html
<!-- Icon only button -->
<button class="
  p-2 rounded-lg
  text-gray-500 hover:text-gray-700 hover:bg-gray-100
  focus:outline-none focus:ring-2 focus:ring-gray-500
  dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-800
" aria-label="Close">
  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
  </svg>
</button>

<!-- Button with icon -->
<button class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg">
  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
  </svg>
  Add Item
</button>
```

## Loading State

```html
<button class="
  inline-flex items-center justify-center gap-2
  px-4 py-2 rounded-lg
  bg-primary-600 text-white
  disabled:opacity-75 disabled:cursor-wait
" disabled>
  <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
  </svg>
  Loading...
</button>
```

## Button Group

```html
<div class="inline-flex rounded-lg shadow-sm">
  <button class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-l-lg hover:bg-gray-100">
    Left
  </button>
  <button class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border-t border-b border-gray-200 hover:bg-gray-100">
    Middle
  </button>
  <button class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-r-lg hover:bg-gray-100">
    Right
  </button>
</div>
```

## Using @apply for Components

```css
/* In your CSS file */
@layer components {
  .btn {
    @apply inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg font-medium transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed;
  }

  .btn-primary {
    @apply bg-primary-600 text-white hover:bg-primary-700 active:bg-primary-800 focus:ring-primary-500;
  }

  .btn-secondary {
    @apply bg-gray-100 text-gray-900 hover:bg-gray-200 active:bg-gray-300 focus:ring-gray-500;
  }
}
```
