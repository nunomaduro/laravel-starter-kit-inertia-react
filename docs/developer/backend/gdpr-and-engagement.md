# GDPR & Engagement

## Maintenance (503)

- **View**: `resources/views/errors/503.blade.php`
- Shown when the app is in maintenance mode (`php artisan down`).

## Cookie consent

- **Package**: `spatie/laravel-cookie-consent` (config and cookie semantics only; the package’s Blade middleware is not used so Inertia is not broken).
- **Config**: `config/cookie-consent.php` (`enabled`, `cookie_name`, `cookie_lifetime`).
- **Shared data**: `HandleInertiaRequests` shares `cookieConsent` (e.g. `accepted`, `cookieName`, `lifetimeDays`) when enabled.
- **Frontend**: `CookieConsentBanner` in `resources/js/components/cookie-consent-banner.tsx`; shown globally via the app resolve wrapper in `app.tsx`. “Accept” calls GET `cookie-consent/accept`.
- **Backend**: `CookieConsentController` sets the consent cookie and redirects back. Route: `GET cookie-consent/accept` (`cookie-consent.accept`).

## Contact form

- **Model**: `Modules\Contact\Models\ContactSubmission` (name, email, subject, message, status).
- **Routes**: GET `contact` → Inertia `contact/create`; POST `contact` with `ProtectAgainstSpam` → `ContactSubmissionController@store`.
- **Action**: `Modules\Contact\Actions\StoreContactSubmission`.
- **Filament**: `ContactSubmissionResource` under “Engagement” (list, view, edit status; create disabled). Submissions are only created from the public form.
- **Frontend**: `resources/js/pages/contact/create.tsx`; includes `HoneypotFields`. “Contact” link on welcome page.

## Personal data export (GDPR)

- **Package**: `spatie/laravel-personal-data-export`.
- **Config**: `config/personal-data-export.php` (disk, `delete_after_days`, notification). Disk `personal-data-exports` in `config/filesystems.php`.
- **User**: Implements `ExportsPersonalData`; `selectPersonalData()` adds `user.json` with id, name, email, email_verified_at, created_at, updated_at (no password or 2FA). `personalDataExportName()` returns a slugged zip name.
- **Routes**: `Route::personalDataExports('personal-data-exports')` (GET download, inside `auth`); GET `settings/personal-data-export` → Inertia page; POST `settings/personal-data-export` (throttle 3/min) → `PersonalDataExportController` dispatches `CreatePersonalDataExportJob`.
- **Settings**: “Data export” in settings sidebar; page at `resources/js/pages/settings/personal-data-export.tsx`. User receives an email with download link when the export is ready.
- **Cleanup**: `personal-data-export:clean` is scheduled daily in `routes/console.php`.
