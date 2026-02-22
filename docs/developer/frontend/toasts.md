# Toasts (Sonner + Flash)

The app uses [Sonner](https://sonner.emilkowal.ski/) for toast notifications, wired to Laravel session flash so redirects can show success or error messages.

## Backend

- **Middleware**: `HandleInertiaRequests` shares `flash` from the session (`session('flash')`) on every request.
- **Controllers**: Use `redirect()->with('flash', ['success' => 'Message'])` or `->with('flash', ['error' => 'Message'])` so the next page load receives the flash and a toast is shown.

## Frontend

- **Toaster**: `<Toaster />` from `sonner` is rendered in the app root (see `resources/js/app.tsx`).
- **FlashListener**: `FlashListener` reads `page.props.flash` and `page.props.status` (legacy). It calls `toast.success()` or `toast.error()` when present. The legacy `status` key is still supported and shown as a success toast.

## Usage

- Prefer `flash.success` or `flash.error` for new code.
- Existing `status` redirects continue to work and are displayed as success toasts.
