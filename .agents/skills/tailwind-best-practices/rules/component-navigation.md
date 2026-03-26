---
id: component-navigation
title: Navigation Components
priority: HIGH
category: Component Patterns
---

# Navigation Components

Build accessible, responsive navigation patterns that work across all device sizes.

## Bad Example

```html
<!-- Inaccessible navigation -->
<div class="flex gap-4">
  <div onclick="navigate('/')">Home</div>
  <div onclick="navigate('/about')">About</div>
</div>

<!-- No mobile consideration -->
<nav class="flex gap-6">
  <a href="/">Home</a>
  <a href="/products">Products</a>
  <a href="/services">Services</a>
  <a href="/about">About</a>
  <a href="/contact">Contact</a>
  <!-- Links overflow on mobile -->
</nav>

<!-- Missing active states -->
<nav>
  <a href="/" class="text-gray-600">Home</a>
  <a href="/about" class="text-gray-600">About (currently active but looks same)</a>
</nav>
```

## Good Example

```html
<!-- Responsive navbar with mobile menu -->
<nav class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800">
  <div class="container mx-auto px-4">
    <div class="flex items-center justify-between h-16">
      <!-- Logo -->
      <a href="/" class="text-xl font-bold text-gray-900 dark:text-white">
        Logo
      </a>

      <!-- Desktop navigation -->
      <div class="hidden md:flex items-center gap-1">
        <a href="/" class="px-4 py-2 rounded-lg text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-800 font-medium">
          Home
        </a>
        <a href="/products" class="px-4 py-2 rounded-lg text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
          Products
        </a>
        <a href="/about" class="px-4 py-2 rounded-lg text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
          About
        </a>
        <a href="/contact" class="px-4 py-2 rounded-lg text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
          Contact
        </a>
      </div>

      <!-- Mobile menu button -->
      <button
        class="md:hidden p-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800"
        aria-label="Toggle menu"
        aria-expanded="false"
      >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
    </div>

    <!-- Mobile navigation -->
    <div class="md:hidden py-4 border-t border-gray-200 dark:border-gray-800">
      <div class="flex flex-col gap-1">
        <a href="/" class="px-4 py-2 rounded-lg text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-800 font-medium">
          Home
        </a>
        <a href="/products" class="px-4 py-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
          Products
        </a>
        <a href="/about" class="px-4 py-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
          About
        </a>
        <a href="/contact" class="px-4 py-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
          Contact
        </a>
      </div>
    </div>
  </div>
</nav>
```

## Why

1. **Accessibility**: Semantic HTML with proper ARIA attributes and keyboard navigation.

2. **Responsive design**: Works on mobile with hamburger menu, desktop with full links.

3. **Active states**: Clear indication of current page/section.

4. **Dark mode**: Adapts to both light and dark themes.

5. **Touch-friendly**: Adequate tap targets on mobile devices.

## Sidebar Navigation

```html
<aside class="w-64 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 min-h-screen">
  <div class="p-4">
    <h2 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">
      Main Menu
    </h2>
    <nav class="space-y-1">
      <a href="/dashboard" class="
        flex items-center gap-3 px-3 py-2 rounded-lg
        text-white bg-primary-600
        font-medium
      ">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
        </svg>
        Dashboard
      </a>
      <a href="/analytics" class="
        flex items-center gap-3 px-3 py-2 rounded-lg
        text-gray-600 dark:text-gray-300
        hover:bg-gray-100 dark:hover:bg-gray-800
        transition-colors
      ">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        Analytics
      </a>
      <a href="/settings" class="
        flex items-center gap-3 px-3 py-2 rounded-lg
        text-gray-600 dark:text-gray-300
        hover:bg-gray-100 dark:hover:bg-gray-800
        transition-colors
      ">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        Settings
      </a>
    </nav>
  </div>
</aside>
```

## Breadcrumb Navigation

```html
<nav aria-label="Breadcrumb" class="text-sm">
  <ol class="flex items-center gap-2">
    <li>
      <a href="/" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
        Home
      </a>
    </li>
    <li class="text-gray-400 dark:text-gray-600">/</li>
    <li>
      <a href="/products" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
        Products
      </a>
    </li>
    <li class="text-gray-400 dark:text-gray-600">/</li>
    <li>
      <span class="text-gray-900 dark:text-white font-medium" aria-current="page">
        Product Name
      </span>
    </li>
  </ol>
</nav>
```

## Tab Navigation

```html
<div class="border-b border-gray-200 dark:border-gray-700">
  <nav class="flex gap-1" aria-label="Tabs">
    <button class="
      px-4 py-2 -mb-px
      text-primary-600 dark:text-primary-400
      border-b-2 border-primary-600 dark:border-primary-400
      font-medium
    " aria-current="page">
      Overview
    </button>
    <button class="
      px-4 py-2 -mb-px
      text-gray-500 dark:text-gray-400
      border-b-2 border-transparent
      hover:text-gray-700 dark:hover:text-gray-300
      hover:border-gray-300 dark:hover:border-gray-600
      transition-colors
    ">
      Details
    </button>
    <button class="
      px-4 py-2 -mb-px
      text-gray-500 dark:text-gray-400
      border-b-2 border-transparent
      hover:text-gray-700 dark:hover:text-gray-300
      hover:border-gray-300 dark:hover:border-gray-600
      transition-colors
    ">
      Reviews
    </button>
  </nav>
</div>
```

## Pagination

```html
<nav aria-label="Pagination" class="flex items-center justify-center gap-1">
  <a href="?page=1" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
    </svg>
  </a>
  <a href="?page=1" class="px-3 py-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">1</a>
  <a href="?page=2" class="px-3 py-2 rounded-lg text-white bg-primary-600">2</a>
  <a href="?page=3" class="px-3 py-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">3</a>
  <span class="px-3 py-2 text-gray-400">...</span>
  <a href="?page=10" class="px-3 py-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">10</a>
  <a href="?page=3" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
  </a>
</nav>
```
