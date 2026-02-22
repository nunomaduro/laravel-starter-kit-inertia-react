# Forms

## Primary: Inertia + useForm

Most forms use Inertia’s `useForm` and submit via `<Form>` or `form.submit(route())`. Validation errors and flash messages are handled by the backend and shared props.

## Advanced: TanStack Form (`@tanstack/react-form`)

For complex, multi-step or heavily nested forms, you can use `@tanstack/react-form` (e.g. `useForm` from `@tanstack/react-form`) for client-side state and validation, then submit the final payload with Inertia or `fetch`. Prefer Inertia for standard CRUD and page-driven flows; use TanStack Form when you need fine-grained control, conditional sections, or client-only validation layers.

## Form requests

Server-side validation uses Laravel Form Request classes; see the backend docs for validation rules and authorization.
