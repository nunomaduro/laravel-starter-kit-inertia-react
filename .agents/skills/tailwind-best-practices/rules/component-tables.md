---
id: component-tables
title: Table Components
priority: HIGH
category: Component Patterns
---

# Table Components

Create responsive, accessible tables with proper styling for data presentation.

## Bad Example

```html
<!-- Unstyled table that breaks on mobile -->
<table>
  <thead>
    <tr>
      <th>Name</th>
      <th>Email</th>
      <th>Role</th>
      <th>Status</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>John Doe</td>
      <td>john@example.com</td>
      <td>Admin</td>
      <td>Active</td>
      <td><button>Edit</button></td>
    </tr>
  </tbody>
</table>

<!-- No consideration for long content -->
<table class="w-full">
  <tr>
    <td>Very long text that will overflow and break the layout on small screens</td>
  </tr>
</table>
```

## Good Example

```html
<!-- Responsive table with proper styling -->
<div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
  <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
    <thead class="bg-gray-50 dark:bg-gray-800">
      <tr>
        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
          Name
        </th>
        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
          Email
        </th>
        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
          Role
        </th>
        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
          Status
        </th>
        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
          Actions
        </th>
      </tr>
    </thead>
    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
      <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
        <td class="px-6 py-4 whitespace-nowrap">
          <div class="flex items-center gap-3">
            <img class="h-10 w-10 rounded-full" src="avatar.jpg" alt="">
            <div>
              <div class="text-sm font-medium text-gray-900 dark:text-white">John Doe</div>
              <div class="text-sm text-gray-500 dark:text-gray-400">john.doe</div>
            </div>
          </div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <span class="text-sm text-gray-900 dark:text-white">john@example.com</span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <span class="text-sm text-gray-900 dark:text-white">Administrator</span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">
            Active
          </span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-right">
          <button class="text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-300 text-sm font-medium">
            Edit
          </button>
        </td>
      </tr>
    </tbody>
  </table>
</div>
```

## Why

1. **Responsive**: Horizontal scroll on small screens preserves data integrity.

2. **Accessibility**: Proper `scope` attributes and semantic structure.

3. **Readability**: Consistent spacing, typography, and visual hierarchy.

4. **Dark mode**: Tables work in both light and dark themes.

5. **Interactive**: Hover states and action buttons are clearly styled.

## Table with Sorting

```html
<table class="min-w-full">
  <thead>
    <tr>
      <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
        <button class="group inline-flex items-center gap-1">
          Name
          <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
          </svg>
        </button>
      </th>
      <!-- Sorted column -->
      <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900 dark:text-white uppercase tracking-wider">
        <button class="inline-flex items-center gap-1">
          Date
          <svg class="w-4 h-4 text-gray-900 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
          </svg>
        </button>
      </th>
    </tr>
  </thead>
</table>
```

## Table with Selection

```html
<table class="min-w-full">
  <thead>
    <tr>
      <th class="w-12 px-6 py-3">
        <input
          type="checkbox"
          class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
          aria-label="Select all"
        >
      </th>
      <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Name</th>
      <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
    </tr>
  </thead>
  <tbody>
    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
      <td class="px-6 py-4">
        <input
          type="checkbox"
          class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
        >
      </td>
      <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">John Doe</td>
      <td class="px-6 py-4">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
          Active
        </span>
      </td>
    </tr>
    <!-- Selected row -->
    <tr class="bg-primary-50 dark:bg-primary-900/20">
      <td class="px-6 py-4">
        <input type="checkbox" checked class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
      </td>
      <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">Jane Smith</td>
      <td class="px-6 py-4">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
          Pending
        </span>
      </td>
    </tr>
  </tbody>
</table>
```

## Mobile-Friendly Card Table

```html
<!-- Transforms to cards on mobile -->
<div class="hidden md:block overflow-x-auto">
  <table class="min-w-full">
    <!-- Standard table for desktop -->
  </table>
</div>

<!-- Card layout for mobile -->
<div class="md:hidden space-y-4">
  <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
    <div class="flex items-center justify-between mb-3">
      <div class="flex items-center gap-3">
        <img class="w-10 h-10 rounded-full" src="avatar.jpg" alt="">
        <div>
          <div class="font-medium text-gray-900 dark:text-white">John Doe</div>
          <div class="text-sm text-gray-500">john@example.com</div>
        </div>
      </div>
      <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
    </div>
    <dl class="grid grid-cols-2 gap-2 text-sm">
      <div>
        <dt class="text-gray-500 dark:text-gray-400">Role</dt>
        <dd class="text-gray-900 dark:text-white">Administrator</dd>
      </div>
      <div>
        <dt class="text-gray-500 dark:text-gray-400">Joined</dt>
        <dd class="text-gray-900 dark:text-white">Jan 15, 2024</dd>
      </div>
    </dl>
  </div>
</div>
```

## Striped Table

```html
<tbody class="bg-white dark:bg-gray-900">
  <tr class="even:bg-gray-50 dark:even:bg-gray-800">
    <td class="px-6 py-4">Row 1</td>
  </tr>
  <tr class="even:bg-gray-50 dark:even:bg-gray-800">
    <td class="px-6 py-4">Row 2</td>
  </tr>
  <tr class="even:bg-gray-50 dark:even:bg-gray-800">
    <td class="px-6 py-4">Row 3</td>
  </tr>
</tbody>
```

## Empty State

```html
<tbody>
  <tr>
    <td colspan="5" class="px-6 py-12 text-center">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No data found</h3>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new item.</p>
    </td>
  </tr>
</tbody>
```
