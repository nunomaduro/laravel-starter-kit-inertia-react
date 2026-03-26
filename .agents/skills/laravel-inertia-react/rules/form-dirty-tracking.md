---
section: forms
priority: high
description: Track unsaved changes with isDirty and warn before navigation
keywords: [isDirty, unsaved, changes, navigation, warning, beforeunload]
---

# Form Dirty Tracking

Use Inertia's isDirty flag to track unsaved changes and warn users before navigating away from modified forms.

## Bad Example

```tsx
// Anti-pattern: Not tracking form changes
export default function EditProfile({ user }) {
  const { data, setData, put } = useForm({
    name: user.name,
    email: user.email,
  });

  // Users can navigate away and lose all changes without warning
  return (
    <form onSubmit={(e) => { e.preventDefault(); put('/profile'); }}>
      <input
        value={data.name}
        onChange={(e) => setData('name', e.target.value)}
      />
      <button type="submit">Save</button>
    </form>
  );
}

// Anti-pattern: Manual dirty tracking
const [originalData] = useState({ name: user.name, email: user.email });
const [isDirty, setIsDirty] = useState(false);

useEffect(() => {
  setIsDirty(
    data.name !== originalData.name ||
    data.email !== originalData.email
  );
}, [data]);
```

## Good Example

```tsx
// resources/js/Pages/Profile/Edit.tsx
import { useForm, router } from '@inertiajs/react';
import { FormEventHandler, useEffect } from 'react';

interface ProfileForm {
  name: string;
  email: string;
  bio: string;
}

interface EditProfileProps {
  user: User;
}

export default function EditProfile({ user }: EditProfileProps) {
  const { data, setData, put, processing, errors, isDirty, reset } = useForm<ProfileForm>({
    name: user.name,
    email: user.email,
    bio: user.bio || '',
  });

  // Warn before browser close/refresh
  useEffect(() => {
    const handleBeforeUnload = (e: BeforeUnloadEvent) => {
      if (isDirty) {
        e.preventDefault();
        e.returnValue = '';
      }
    };

    window.addEventListener('beforeunload', handleBeforeUnload);
    return () => window.removeEventListener('beforeunload', handleBeforeUnload);
  }, [isDirty]);

  // Intercept Inertia navigation
  useEffect(() => {
    const removeListener = router.on('before', (event) => {
      if (isDirty && !confirm('You have unsaved changes. Are you sure you want to leave?')) {
        event.preventDefault();
      }
    });

    return () => removeListener();
  }, [isDirty]);

  const submit: FormEventHandler = (e) => {
    e.preventDefault();
    put(route('profile.update'), {
      onSuccess: () => {
        // Form will no longer be dirty after successful save
        // because data matches what was saved
      },
    });
  };

  return (
    <div>
      {/* Unsaved changes indicator */}
      {isDirty && (
        <div className="mb-4 flex items-center justify-between rounded-md bg-amber-50 px-4 py-2 text-amber-800">
          <span>You have unsaved changes</span>
          <button
            type="button"
            onClick={() => reset()}
            className="text-sm underline hover:no-underline"
          >
            Discard changes
          </button>
        </div>
      )}

      <form onSubmit={submit} className="space-y-6">
        <div>
          <label htmlFor="name" className="block text-sm font-medium">
            Name
          </label>
          <input
            id="name"
            value={data.name}
            onChange={(e) => setData('name', e.target.value)}
            className="mt-1 block w-full rounded-md border-gray-300"
          />
        </div>

        <div>
          <label htmlFor="email" className="block text-sm font-medium">
            Email
          </label>
          <input
            id="email"
            type="email"
            value={data.email}
            onChange={(e) => setData('email', e.target.value)}
            className="mt-1 block w-full rounded-md border-gray-300"
          />
        </div>

        <div>
          <label htmlFor="bio" className="block text-sm font-medium">
            Bio
          </label>
          <textarea
            id="bio"
            value={data.bio}
            onChange={(e) => setData('bio', e.target.value)}
            rows={4}
            className="mt-1 block w-full rounded-md border-gray-300"
          />
        </div>

        <div className="flex items-center gap-4">
          <button
            type="submit"
            disabled={processing || !isDirty}
            className="rounded-md bg-blue-600 px-4 py-2 text-white disabled:opacity-50"
          >
            {processing ? 'Saving...' : 'Save Changes'}
          </button>

          <span className="text-sm text-gray-500">
            {isDirty ? 'Unsaved changes' : 'All changes saved'}
          </span>
        </div>
      </form>
    </div>
  );
}

// Custom hook for reusable dirty tracking with navigation blocking
import { useForm as useInertiaForm, router } from '@inertiajs/react';
import { useEffect, useCallback } from 'react';

function useFormWithNavBlock<T extends Record<string, unknown>>(initialData: T) {
  const form = useInertiaForm<T>(initialData);

  // Block browser navigation
  useEffect(() => {
    const handler = (e: BeforeUnloadEvent) => {
      if (form.isDirty) {
        e.preventDefault();
        e.returnValue = '';
      }
    };
    window.addEventListener('beforeunload', handler);
    return () => window.removeEventListener('beforeunload', handler);
  }, [form.isDirty]);

  // Block Inertia navigation
  useEffect(() => {
    return router.on('before', (event) => {
      if (form.isDirty) {
        if (!confirm('Discard unsaved changes?')) {
          event.preventDefault();
        }
      }
    });
  }, [form.isDirty]);

  return form;
}

// Usage
function MyForm({ initialData }) {
  const { data, setData, post, isDirty } = useFormWithNavBlock({
    title: initialData.title,
    content: initialData.content,
  });

  // Navigation blocking is automatic
  return <form>{/* ... */}</form>;
}
```

## Why

Dirty tracking prevents accidental data loss:

1. **User Protection**: Warn users before they lose unsaved work
2. **Built-in Support**: isDirty is provided by useForm automatically
3. **Browser Events**: Handle both page refresh and navigation away
4. **Inertia Navigation**: Intercept SPA navigation with router.on('before')
5. **Visual Feedback**: Show users when they have unsaved changes
6. **Button States**: Disable submit button when no changes exist
7. **Better UX**: Users trust the form won't lose their data
