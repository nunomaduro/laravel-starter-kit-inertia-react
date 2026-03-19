# OnboardingController

## Purpose

Shows the multi-step onboarding hub for users who have not completed onboarding (or who re-open it from Settings) and handles the completion action. Steps and completion state come from spatie/laravel-onboard; see [Onboarding](./onboarding.md).

## Location

`app/Http/Controllers/OnboardingController.php`

## Actions

- **show** – Redirects to dashboard when user is guest or onboarding feature is off; otherwise passes `steps`, `alreadyCompleted`, `inProgress`, `percentageCompleted`, `nextStep` from the user's onboarding and renders `onboarding/show` Inertia page.
- **store** – Calls `CompleteOnboardingAction` and redirects to dashboard with status message.

## Dependencies

- **Action**: `CompleteOnboardingAction`
- **Routes**: `onboarding` (GET), `onboarding.store` (POST)
- **Package**: spatie/laravel-onboard (steps in OnboardingServiceProvider)

## Related Components

- **Middleware**: `EnsureOnboardingComplete` (excludes these routes)
- **Page**: `onboarding/show`
