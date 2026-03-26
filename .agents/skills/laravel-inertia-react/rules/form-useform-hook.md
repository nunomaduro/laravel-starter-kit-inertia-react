---
section: forms
priority: critical
description: Complete useForm hook API with all methods and options
keywords: [useForm, hook, form, state, methods, api]
---

# Form useForm Hook

Use Inertia's useForm hook for all form handling to get automatic state management, validation error handling, and submission progress tracking.

## Bad Example

```tsx
// Anti-pattern: Manual state management
import { useState } from 'react';
import { router } from '@inertiajs/react';

export default function CreatePost() {
  const [title, setTitle] = useState('');
  const [content, setContent] = useState('');
  const [errors, setErrors] = useState({});
  const [processing, setProcessing] = useState(false);

  const handleSubmit = (e) => {
    e.preventDefault();
    setProcessing(true);
    setErrors({});

    router.post('/posts', { title, content }, {
      onError: (errors) => setErrors(errors),
      onFinish: () => setProcessing(false),
    });
  };

  return (
    <form onSubmit={handleSubmit}>
      <input
        value={title}
        onChange={e => setTitle(e.target.value)}
      />
      {errors.title && <span>{errors.title}</span>}
      {/* ... more fields */}
    </form>
  );
}
```

## Good Example

```tsx
// resources/js/Pages/Posts/Create.tsx
import { useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import PrimaryButton from '@/Components/PrimaryButton';

interface CreatePostForm {
  title: string;
  content: string;
  category_id: number | '';
  published: boolean;
  tags: string[];
}

export default function Create() {
  const { data, setData, post, processing, errors, reset } = useForm<CreatePostForm>({
    title: '',
    content: '',
    category_id: '',
    published: false,
    tags: [],
  });

  const submit: FormEventHandler = (e) => {
    e.preventDefault();
    post(route('posts.store'), {
      onSuccess: () => reset(),
    });
  };

  return (
    <form onSubmit={submit} className="space-y-6">
      <div>
        <InputLabel htmlFor="title" value="Title" />
        <TextInput
          id="title"
          type="text"
          value={data.title}
          onChange={(e) => setData('title', e.target.value)}
          className="mt-1 block w-full"
        />
        <InputError message={errors.title} className="mt-2" />
      </div>

      <div>
        <InputLabel htmlFor="content" value="Content" />
        <textarea
          id="content"
          value={data.content}
          onChange={(e) => setData('content', e.target.value)}
          className="mt-1 block w-full rounded-md border-gray-300"
          rows={6}
        />
        <InputError message={errors.content} className="mt-2" />
      </div>

      <div>
        <InputLabel htmlFor="category" value="Category" />
        <select
          id="category"
          value={data.category_id}
          onChange={(e) => setData('category_id', Number(e.target.value) || '')}
          className="mt-1 block w-full rounded-md border-gray-300"
        >
          <option value="">Select a category</option>
          {/* Category options */}
        </select>
        <InputError message={errors.category_id} className="mt-2" />
      </div>

      <div className="flex items-center gap-2">
        <input
          type="checkbox"
          id="published"
          checked={data.published}
          onChange={(e) => setData('published', e.target.checked)}
        />
        <InputLabel htmlFor="published" value="Publish immediately" />
      </div>

      <PrimaryButton disabled={processing}>
        {processing ? 'Creating...' : 'Create Post'}
      </PrimaryButton>
    </form>
  );
}
```

## Why

The useForm hook provides significant advantages over manual form handling:

1. **Automatic State Management**: Single source of truth for all form data
2. **Built-in Error Handling**: Validation errors from Laravel automatically populate
3. **Processing State**: Track submission status for button states and UI feedback
4. **Type Safety**: Generic typing ensures form data matches expected shape
5. **Helper Methods**: setData, reset, clearErrors, and transform simplify common operations
6. **Memory Management**: Proper cleanup prevents memory leaks on unmount
7. **Consistent API**: post, put, patch, delete methods handle HTTP verbs correctly
