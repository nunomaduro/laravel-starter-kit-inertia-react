# Forms

## Recommended pattern: FormField + Inertia

Use **FormField** to wrap each form control so you get a consistent label, error message, and optional description or hint without repeating markup.

```tsx
import { FormField } from '@/components/ui/form-field';
import { Input } from '@/components/ui/input';

<Form
  {...SomeController.store.form()}
  className="space-y-4"
>
  {({ errors }) => (
    <>
      <FormField label="Email" htmlFor="email" error={errors.email}>
        <Input id="email" name="email" type="email" required />
      </FormField>
      <FormField
        label="Password"
        htmlFor="password"
        error={errors.password}
        labelAction={<TextLink href={request()}>Forgot password?</TextLink>}
      >
        <Input id="password" name="password" type="password" required />
      </FormField>
      <Button type="submit">Submit</Button>
    </>
  )}
</Form>
```

- **FormField** (`@/components/ui/form-field`) – Wraps label, description, error, and hint. Props: `label`, `htmlFor`, `error`, `description`, `hint`, `required`, `labelAction` (e.g. “Forgot password?” link), `horizontal`.
- **FormRow** (`@/components/ui/form-row`) – Layout for multiple fields in a row. Default 2 columns on `sm` and up. Use for side‑by‑side fields (e.g. first name / last name).

```tsx
import { FormRow } from '@/components/ui/form-row';

<FormRow cols={2}>
  <FormField label="First name" htmlFor="first_name" error={errors.first_name}>
    <Input id="first_name" name="first_name" />
  </FormField>
  <FormField label="Last name" htmlFor="last_name" error={errors.last_name}>
    <Input id="last_name" name="last_name" />
  </FormField>
</FormRow>
```

Prefer FormField over raw `<Label>`, `<Input>`, and `<InputError>` so styling and behaviour stay consistent and line count stays low.

## Primary: Inertia + useForm

Most forms use Inertia’s `useForm` and submit via `<Form>` or `form.submit(route())`. Validation errors and flash messages are handled by the backend and shared props.

## Schema-driven forms: AutoForm

For forms that map cleanly to a Zod schema, use **AutoForm** (`@/components/ui/auto-form`). It uses react-hook-form + zod and renders one FormField per schema key. You can wire it to Inertia by submitting from `onSubmit` and passing server errors.

```tsx
import { router } from '@inertiajs/react';
import { z } from 'zod';
import { AutoForm } from '@/components/ui/auto-form';

const schema = z.object({ name: z.string().min(1), email: z.string().email() });

<AutoForm
  schema={schema}
  defaultValues={{ name: '', email: '' }}
  serverErrors={page.props.errors}
  submitLabel="Save"
  onSubmit={(values) => router.post(route('settings.profile'), values)}
/>
```

- **serverErrors** – Optional. Pass `page.props.errors` (or similar) so Laravel validation errors are shown on the form.
- **fieldConfig** – Override labels, descriptions, or use custom components per field.
- **children** – Render function receiving the react-hook-form instance for extra fields or actions.

Use AutoForm when the form is mostly a flat set of fields; use Inertia Form + FormField when you need file uploads, conditional sections, or Wayfinder `.form()` integration.

## Advanced: TanStack Form

For complex, multi-step or heavily nested forms, you can use `@tanstack/react-form` (e.g. `useForm` from `@tanstack/react-form`) for client-side state and validation, then submit the final payload with Inertia or `fetch`. Prefer Inertia + FormField for standard CRUD and page-driven flows; use TanStack Form when you need fine-grained control, conditional sections, or client-only validation layers.

## Form requests

Server-side validation uses Laravel Form Request classes; see the backend docs for validation rules and authorization.
