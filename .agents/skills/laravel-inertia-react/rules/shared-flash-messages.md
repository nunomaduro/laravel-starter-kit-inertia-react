---
section: shared-data
priority: high
description: Display Laravel session flash messages in React components
keywords: [flash, messages, feedback, session, notifications, toast]
---

# Shared Flash Messages

Laravel's session flash messages need to be passed to Inertia and displayed in React. This enables consistent feedback to users after form submissions and actions.

## Laravel Setup

```php
// app/Http/Middleware/HandleInertiaRequests.php
<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
            ],
        ]);
    }
}
```

```php
// In controller
public function store(StorePostRequest $request): RedirectResponse
{
    Post::create($request->validated());

    return redirect()
        ->route('posts.index')
        ->with('success', 'Post created successfully!');
}

public function destroy(Post $post): RedirectResponse
{
    $post->delete();

    return redirect()
        ->route('posts.index')
        ->with('success', 'Post deleted successfully!');
}
```

## React Implementation

### TypeScript Types

```tsx
// resources/js/types/index.ts
export interface PageProps {
  auth: {
    user: {
      id: number
      name: string
      email: string
    } | null
  }
  flash: {
    success?: string
    error?: string
    warning?: string
    info?: string
  }
}
```

### Flash Message Component

```tsx
// resources/js/Components/FlashMessages.tsx
import { usePage } from '@inertiajs/react'
import { useEffect, useState } from 'react'
import { PageProps } from '@/types'

export default function FlashMessages() {
  const { flash } = usePage<PageProps>().props
  const [messages, setMessages] = useState(flash)

  useEffect(() => {
    setMessages(flash)

    // Auto-dismiss after 5 seconds
    if (flash.success || flash.error || flash.warning || flash.info) {
      const timer = setTimeout(() => {
        setMessages({})
      }, 5000)
      return () => clearTimeout(timer)
    }
  }, [flash])

  if (!messages.success && !messages.error && !messages.warning && !messages.info) {
    return null
  }

  return (
    <div className="fixed top-4 right-4 z-50 space-y-2">
      {messages.success && (
        <div className="flex items-center gap-2 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
          <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
          </svg>
          <span>{messages.success}</span>
          <button onClick={() => setMessages({})}>×</button>
        </div>
      )}

      {messages.error && (
        <div className="flex items-center gap-2 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
          <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
          </svg>
          <span>{messages.error}</span>
          <button onClick={() => setMessages({})}>×</button>
        </div>
      )}

      {messages.warning && (
        <div className="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
          {messages.warning}
        </div>
      )}

      {messages.info && (
        <div className="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded">
          {messages.info}
        </div>
      )}
    </div>
  )
}
```

### Add to Layout

```tsx
// resources/js/Layouts/AppLayout.tsx
import FlashMessages from '@/Components/FlashMessages'
import { ReactNode } from 'react'

export default function AppLayout({ children }: { children: ReactNode }) {
  return (
    <div>
      <FlashMessages />
      <nav>{/* ... */}</nav>
      <main>{children}</main>
    </div>
  )
}
```

### With Animation (using Tailwind)

```tsx
import { usePage } from '@inertiajs/react'
import { useEffect, useState } from 'react'
import { Transition } from '@headlessui/react'

export default function FlashMessages() {
  const { flash } = usePage().props as { flash: { success?: string } }
  const [show, setShow] = useState(false)

  useEffect(() => {
    if (flash.success) {
      setShow(true)
      const timer = setTimeout(() => setShow(false), 3000)
      return () => clearTimeout(timer)
    }
  }, [flash])

  return (
    <Transition
      show={show}
      enter="transition ease-out duration-300"
      enterFrom="transform translate-x-full opacity-0"
      enterTo="transform translate-x-0 opacity-100"
      leave="transition ease-in duration-200"
      leaveFrom="transform translate-x-0 opacity-100"
      leaveTo="transform translate-x-full opacity-0"
    >
      <div className="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
        {flash.success}
      </div>
    </Transition>
  )
}
```

### Using Toast Library

```tsx
// With react-hot-toast
import { usePage } from '@inertiajs/react'
import { useEffect } from 'react'
import toast, { Toaster } from 'react-hot-toast'

export default function FlashProvider({ children }) {
  const { flash } = usePage().props as { flash: any }

  useEffect(() => {
    if (flash.success) toast.success(flash.success)
    if (flash.error) toast.error(flash.error)
  }, [flash])

  return (
    <>
      <Toaster position="top-right" />
      {children}
    </>
  )
}
```

## Benefits

- Consistent user feedback
- Works with Laravel's standard flash system
- Auto-dismisses after timeout
- Type-safe with TypeScript
