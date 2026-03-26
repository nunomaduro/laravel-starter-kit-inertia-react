---
section: forms
priority: critical
description: Use useForm hook for form state management and Laravel validation integration
keywords: [useForm, form, validation, laravel, state]
---

# Form useForm Hook

The useForm hook is Inertia's primary way to handle forms. It provides automatic form state management, error handling, processing state, and seamless integration with Laravel validation.

## Incorrect

```tsx
// ❌ Manual state management
import { useState } from 'react'
import { router } from '@inertiajs/react'

function CreatePost() {
  const [title, setTitle] = useState('')
  const [body, setBody] = useState('')
  const [errors, setErrors] = useState({})
  const [processing, setProcessing] = useState(false)

  const handleSubmit = async (e) => {
    e.preventDefault()
    setProcessing(true)

    router.post('/posts', { title, body }, {
      onError: (errors) => setErrors(errors),
      onFinish: () => setProcessing(false),
    })
  }

  return (
    <form onSubmit={handleSubmit}>
      {/* ... */}
    </form>
  )
}
```

## Correct

```tsx
// ✅ Using useForm hook
import { useForm } from '@inertiajs/react'
import { FormEvent } from 'react'

interface FormData {
  title: string
  body: string
  category_id: string
}

export default function CreatePost() {
  const { data, setData, post, processing, errors, reset, clearErrors } =
    useForm<FormData>({
      title: '',
      body: '',
      category_id: '',
    })

  const handleSubmit = (e: FormEvent) => {
    e.preventDefault()

    post(route('posts.store'), {
      onSuccess: () => {
        reset()  // Clear form on success
      },
    })
  }

  return (
    <form onSubmit={handleSubmit}>
      <div>
        <label htmlFor="title">Title</label>
        <input
          id="title"
          type="text"
          value={data.title}
          onChange={(e) => setData('title', e.target.value)}
          onFocus={() => clearErrors('title')}
        />
        {errors.title && <span className="text-red-500">{errors.title}</span>}
      </div>

      <div>
        <label htmlFor="body">Body</label>
        <textarea
          id="body"
          value={data.body}
          onChange={(e) => setData('body', e.target.value)}
        />
        {errors.body && <span className="text-red-500">{errors.body}</span>}
      </div>

      <button type="submit" disabled={processing}>
        {processing ? 'Creating...' : 'Create Post'}
      </button>
    </form>
  )
}
```

## useForm Methods

```tsx
const {
  data,           // Current form data
  setData,        // Update form data
  post,           // POST request
  put,            // PUT request
  patch,          // PATCH request
  delete: destroy,// DELETE request (renamed to avoid keyword)
  processing,     // Is form submitting?
  errors,         // Validation errors from Laravel
  reset,          // Reset form to initial values
  clearErrors,    // Clear specific or all errors
  isDirty,        // Has form been modified?
  transform,      // Transform data before submit
  setError,       // Set custom error
  recentlySuccessful, // Was last submit successful?
} = useForm({
  // Initial values
})
```

## Setting Data

```tsx
// Single field
setData('title', 'New Title')

// Multiple fields
setData({
  title: 'New Title',
  body: 'New Body',
})

// Using callback (access previous data)
setData((prevData) => ({
  ...prevData,
  title: prevData.title.toUpperCase(),
}))

// Nested data
setData('author.name', 'John')
```

## HTTP Methods

```tsx
// POST - Create
post(route('posts.store'))

// PUT - Full update
put(route('posts.update', post.id))

// PATCH - Partial update
patch(route('posts.update', post.id))

// DELETE
destroy(route('posts.destroy', post.id), {
  onBefore: () => confirm('Are you sure?'),
})
```

## Options

```tsx
post(route('posts.store'), {
  // Preserve state on success
  preserveState: true,

  // Preserve scroll position
  preserveScroll: true,

  // Replace history instead of push
  replace: true,

  // Callbacks
  onBefore: (visit) => {
    // Return false to cancel
  },
  onStart: (visit) => {},
  onProgress: (progress) => {
    // For file uploads
    console.log(progress.percentage)
  },
  onSuccess: (page) => {
    reset()
  },
  onError: (errors) => {
    // Handle errors
  },
  onCancel: () => {},
  onFinish: () => {
    // Always called (success or error)
  },
})
```

## Edit Form Pattern

```tsx
interface Post {
  id: number
  title: string
  body: string
}

interface Props {
  post: Post
}

export default function Edit({ post }: Props) {
  // Initialize with existing data
  const { data, setData, put, processing, errors } = useForm({
    title: post.title,
    body: post.body,
  })

  const handleSubmit = (e: FormEvent) => {
    e.preventDefault()
    put(route('posts.update', post.id))
  }

  return (
    <form onSubmit={handleSubmit}>
      {/* Form fields */}
      <button disabled={processing}>
        {processing ? 'Saving...' : 'Save Changes'}
      </button>
    </form>
  )
}
```

## Transform Data Before Submit

```tsx
const { data, setData, transform, post } = useForm({
  remember: false,
  email: '',
  password: '',
})

// Transform before sending
transform((data) => ({
  ...data,
  remember: data.remember ? 'on' : '',
}))
```

## Benefits

- Automatic error handling from Laravel
- Processing state management
- Form reset functionality
- TypeScript support
- Seamless Laravel integration
