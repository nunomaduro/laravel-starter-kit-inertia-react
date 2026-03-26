---
section: forms
priority: high
description: Reset form state after submissions using Inertia's reset helpers
keywords: [reset, clear, form, state, revert, cleanup]
---

# Form Reset Handling

Properly reset form state after successful submissions or when clearing user input using Inertia's reset helpers.

## Bad Example

```tsx
// Anti-pattern: Manual state reset
export default function ContactForm() {
  const { data, setData, post, processing } = useForm({
    name: '',
    email: '',
    message: '',
  });

  const submit = (e) => {
    e.preventDefault();
    post('/contact', {
      onSuccess: () => {
        // Manually resetting each field - tedious and error-prone
        setData('name', '');
        setData('email', '');
        setData('message', '');
      },
    });
  };
}

// Anti-pattern: Recreating the form on success
const submit = (e) => {
  e.preventDefault();
  post('/contact', {
    onSuccess: () => {
      window.location.reload(); // Destroys all state unnecessarily
    },
  });
};
```

## Good Example

```tsx
// resources/js/Pages/Contact.tsx
import { useForm } from '@inertiajs/react';
import { FormEventHandler, useRef } from 'react';

interface ContactForm {
  name: string;
  email: string;
  subject: string;
  message: string;
  attachment: File | null;
}

export default function Contact() {
  const fileInputRef = useRef<HTMLInputElement>(null);

  const { data, setData, post, processing, errors, reset, clearErrors } = useForm<ContactForm>({
    name: '',
    email: '',
    subject: '',
    message: '',
    attachment: null,
  });

  const submit: FormEventHandler = (e) => {
    e.preventDefault();

    post(route('contact.store'), {
      preserveScroll: true,
      onSuccess: () => {
        // Reset all fields to initial values
        reset();

        // Clear file input (not controlled by React)
        if (fileInputRef.current) {
          fileInputRef.current.value = '';
        }
      },
    });
  };

  // Reset specific fields only
  const resetMessageFields = () => {
    reset('subject', 'message');
    clearErrors('subject', 'message');
  };

  // Clear form manually (user action)
  const handleClear = () => {
    reset();
    clearErrors();
    if (fileInputRef.current) {
      fileInputRef.current.value = '';
    }
  };

  return (
    <form onSubmit={submit} className="space-y-6">
      <div>
        <label htmlFor="name">Name</label>
        <input
          id="name"
          value={data.name}
          onChange={(e) => setData('name', e.target.value)}
        />
      </div>

      <div>
        <label htmlFor="email">Email</label>
        <input
          id="email"
          type="email"
          value={data.email}
          onChange={(e) => setData('email', e.target.value)}
        />
      </div>

      <div>
        <label htmlFor="subject">Subject</label>
        <input
          id="subject"
          value={data.subject}
          onChange={(e) => setData('subject', e.target.value)}
        />
      </div>

      <div>
        <label htmlFor="message">Message</label>
        <textarea
          id="message"
          value={data.message}
          onChange={(e) => setData('message', e.target.value)}
          rows={4}
        />
      </div>

      <div>
        <label htmlFor="attachment">Attachment</label>
        <input
          ref={fileInputRef}
          id="attachment"
          type="file"
          onChange={(e) => setData('attachment', e.target.files?.[0] || null)}
        />
      </div>

      <div className="flex gap-4">
        <button
          type="submit"
          disabled={processing}
          className="rounded bg-blue-600 px-4 py-2 text-white"
        >
          Send Message
        </button>

        <button
          type="button"
          onClick={handleClear}
          disabled={processing}
          className="rounded bg-gray-200 px-4 py-2 text-gray-700"
        >
          Clear Form
        </button>

        <button
          type="button"
          onClick={resetMessageFields}
          className="rounded bg-gray-200 px-4 py-2 text-gray-700"
        >
          Clear Message Only
        </button>
      </div>
    </form>
  );
}

// Edit form with reset to original values
interface EditPostProps {
  post: Post;
}

function EditPost({ post }: EditPostProps) {
  const { data, setData, put, processing, errors, reset, isDirty } = useForm({
    title: post.title,
    content: post.content,
    published: post.published,
  });

  const submit: FormEventHandler = (e) => {
    e.preventDefault();
    put(route('posts.update', post.id));
  };

  // Reset to original prop values
  const handleRevert = () => {
    reset();
    // Or reset to specific values:
    // setData({
    //   title: post.title,
    //   content: post.content,
    //   published: post.published,
    // });
  };

  return (
    <form onSubmit={submit}>
      {/* Form fields */}

      <div className="flex items-center gap-4">
        <button type="submit" disabled={processing || !isDirty}>
          Save Changes
        </button>

        {isDirty && (
          <button
            type="button"
            onClick={handleRevert}
            className="text-gray-600 hover:text-gray-800"
          >
            Discard Changes
          </button>
        )}
      </div>

      {isDirty && (
        <p className="mt-2 text-sm text-amber-600">
          You have unsaved changes
        </p>
      )}
    </form>
  );
}
```

## Why

Proper reset handling ensures a smooth user experience:

1. **Clean State**: Forms return to a known initial state after submission
2. **Selective Reset**: Reset specific fields while preserving others
3. **Error Clearing**: clearErrors works alongside reset for complete cleanup
4. **File Inputs**: Remember to clear uncontrolled file inputs separately
5. **Dirty Tracking**: isDirty flag helps show unsaved changes warnings
6. **Revert Capability**: Let users undo their changes on edit forms
7. **Performance**: No page reload needed to reset form state
