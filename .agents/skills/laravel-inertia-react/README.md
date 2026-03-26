# Laravel + Inertia.js + React

Patterns for building modern monolithic applications with Laravel, Inertia.js, and React.

## Overview

This skill provides guidance for:
- Page component structure and typing
- Form handling with useForm
- Navigation and partial reloads
- Shared data and authentication
- Persistent layouts
- File uploads

## Categories

### 1. Page Components (Critical)
Structure, typing, and best practices for Inertia pages.

### 2. Forms & Validation (Critical)
useForm hook, error handling, and form state management.

### 3. Navigation & Links (High)
Link component, preserve state, and partial reloads.

### 4. Shared Data (High)
Authentication, flash messages, and global props.

### 5. Layouts (Medium)
Persistent layouts for better UX and performance.

### 6. File Uploads (Medium)
Handling file uploads with progress tracking.

## Quick Start

```tsx
// Page component with form
import { useForm } from '@inertiajs/react'

export default function Create() {
  const { data, setData, post, processing, errors } = useForm({
    title: '',
    body: '',
  })

  const handleSubmit = (e) => {
    e.preventDefault()
    post(route('posts.store'))
  }

  return (
    <form onSubmit={handleSubmit}>
      <input
        value={data.title}
        onChange={(e) => setData('title', e.target.value)}
      />
      {errors.title && <span>{errors.title}</span>}
      <button disabled={processing}>Submit</button>
    </form>
  )
}
```

## Usage

This skill triggers automatically when:
- Building Inertia.js pages
- Handling forms with useForm
- Managing shared data
- Implementing layouts

## References

- [Inertia.js Documentation](https://inertiajs.com/)
- [Laravel Documentation](https://laravel.com/docs)
