---
section: forms
priority: critical
description: Display Laravel validation errors with proper UX and accessibility patterns
keywords: [validation, errors, laravel, form, accessibility, ux]
---

# Form Validation Errors

Display Laravel validation errors using Inertia's built-in error handling with proper UX patterns for inline feedback.

## Bad Example

```tsx
// Anti-pattern: Not handling errors properly
export default function ContactForm() {
  const { data, setData, post } = useForm({
    email: '',
    message: '',
  });

  return (
    <form onSubmit={(e) => { e.preventDefault(); post('/contact'); }}>
      <input
        type="email"
        value={data.email}
        onChange={(e) => setData('email', e.target.value)}
      />
      {/* No error display */}
      <textarea
        value={data.message}
        onChange={(e) => setData('message', e.target.value)}
      />
      <button type="submit">Send</button>
    </form>
  );
}

// Anti-pattern: Alert-based error display
const submit = (e) => {
  e.preventDefault();
  post('/contact', {
    onError: (errors) => {
      alert(Object.values(errors).join('\n'));
    },
  });
};
```

## Good Example

```tsx
// resources/js/Components/InputError.tsx
interface InputErrorProps {
  message?: string;
  className?: string;
}

export default function InputError({ message, className = '' }: InputErrorProps) {
  if (!message) return null;

  return (
    <p className={`text-sm text-red-600 ${className}`}>
      {message}
    </p>
  );
}

// resources/js/Pages/Contact.tsx
import { useForm, usePage } from '@inertiajs/react';
import { FormEventHandler, useEffect, useRef } from 'react';
import InputError from '@/Components/InputError';

interface ContactForm {
  name: string;
  email: string;
  subject: string;
  message: string;
}

export default function Contact() {
  const { data, setData, post, processing, errors, clearErrors } = useForm<ContactForm>({
    name: '',
    email: '',
    subject: '',
    message: '',
  });

  const emailRef = useRef<HTMLInputElement>(null);

  // Focus first field with error
  useEffect(() => {
    const firstErrorField = Object.keys(errors)[0];
    if (firstErrorField) {
      document.getElementById(firstErrorField)?.focus();
    }
  }, [errors]);

  const submit: FormEventHandler = (e) => {
    e.preventDefault();
    post(route('contact.store'));
  };

  return (
    <form onSubmit={submit} className="space-y-6" noValidate>
      {/* Show summary of errors at top for accessibility */}
      {Object.keys(errors).length > 0 && (
        <div
          className="rounded-md bg-red-50 p-4"
          role="alert"
          aria-labelledby="error-heading"
        >
          <h3 id="error-heading" className="text-sm font-medium text-red-800">
            There were {Object.keys(errors).length} errors with your submission
          </h3>
          <ul className="mt-2 list-disc pl-5 text-sm text-red-700">
            {Object.entries(errors).map(([field, message]) => (
              <li key={field}>{message}</li>
            ))}
          </ul>
        </div>
      )}

      <div>
        <label htmlFor="name" className="block text-sm font-medium text-gray-700">
          Name
        </label>
        <input
          id="name"
          type="text"
          value={data.name}
          onChange={(e) => setData('name', e.target.value)}
          onFocus={() => clearErrors('name')}
          aria-invalid={!!errors.name}
          aria-describedby={errors.name ? 'name-error' : undefined}
          className={`mt-1 block w-full rounded-md shadow-sm ${
            errors.name
              ? 'border-red-300 focus:border-red-500 focus:ring-red-500'
              : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500'
          }`}
        />
        <InputError message={errors.name} className="mt-2" id="name-error" />
      </div>

      <div>
        <label htmlFor="email" className="block text-sm font-medium text-gray-700">
          Email
        </label>
        <input
          ref={emailRef}
          id="email"
          type="email"
          value={data.email}
          onChange={(e) => setData('email', e.target.value)}
          onFocus={() => clearErrors('email')}
          aria-invalid={!!errors.email}
          aria-describedby={errors.email ? 'email-error' : undefined}
          className={`mt-1 block w-full rounded-md shadow-sm ${
            errors.email
              ? 'border-red-300 focus:border-red-500 focus:ring-red-500'
              : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500'
          }`}
        />
        <InputError message={errors.email} className="mt-2" id="email-error" />
      </div>

      <div>
        <label htmlFor="message" className="block text-sm font-medium text-gray-700">
          Message
        </label>
        <textarea
          id="message"
          value={data.message}
          onChange={(e) => setData('message', e.target.value)}
          onFocus={() => clearErrors('message')}
          aria-invalid={!!errors.message}
          aria-describedby={errors.message ? 'message-error' : undefined}
          rows={4}
          className={`mt-1 block w-full rounded-md shadow-sm ${
            errors.message
              ? 'border-red-300 focus:border-red-500 focus:ring-red-500'
              : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500'
          }`}
        />
        <InputError message={errors.message} className="mt-2" id="message-error" />
      </div>

      <button
        type="submit"
        disabled={processing}
        className="rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 disabled:opacity-50"
      >
        {processing ? 'Sending...' : 'Send Message'}
      </button>
    </form>
  );
}
```

## Why

Proper error handling improves both UX and accessibility:

1. **Immediate Feedback**: Users see exactly which fields need correction
2. **Accessibility**: ARIA attributes help screen readers announce errors
3. **Error Summary**: Top-level summary helps users understand total issues
4. **Visual Indicators**: Red borders and text clearly mark problematic fields
5. **Focus Management**: Focusing the first error field guides user attention
6. **Clear on Focus**: Removing errors when focusing encourages retry
7. **Laravel Integration**: Errors automatically map from Laravel validation responses
