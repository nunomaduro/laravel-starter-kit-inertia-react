# SocialAuthController

## Purpose

Handles OAuth redirects and callbacks for social login providers (Google and GitHub).

## Location

`app/Http/Controllers/SocialAuthController.php`

## Methods

| Method | HTTP Method | Route | Purpose |
|--------|------------|-------|---------|
| `redirect` | GET | `/auth/{provider}/redirect` | Redirects the user to the OAuth provider |
| `callback` | GET | `/auth/{provider}/callback` | Handles the OAuth callback and logs in the user |

## Routes

- `auth.social.redirect`: `GET /auth/{provider}/redirect` — Validates the provider is enabled and redirects to the provider's OAuth page.
- `auth.social.callback`: `GET /auth/{provider}/callback` — Handles the OAuth callback, finds or creates the user, and logs them in.

## Actions Used

- `FindOrCreateSocialUser` — Used in `callback()` to resolve the authenticated user from the OAuth response.

## Validation

No Form Requests. Provider validation is done inline:
- Provider must be one of `['google', 'github']` (returns 404 otherwise).
- Provider must be enabled in `AuthSettings` (returns 404 otherwise).

## Related Components

- **Actions**: `FindOrCreateSocialUser` (finds/creates the user from OAuth data)
- **Settings**: `AuthSettings` (checks `google_oauth_enabled` / `github_oauth_enabled`)
- **Services**: `config/services.php` entries for `google` and `github` (credentials overlaid from `AuthSettings` via `SettingsOverlayServiceProvider`)

## Notes

- Routes are not wrapped in `guest` middleware because the callback URL must be accessible even if a session exists (e.g., re-authenticating).
- On callback failure (e.g., user denied OAuth), the user is redirected to the login page with an error message.
- After successful authentication, the user is redirected to their intended destination or the dashboard.
