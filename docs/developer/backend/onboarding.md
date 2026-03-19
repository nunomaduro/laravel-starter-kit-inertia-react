# User Onboarding

Multi-step user onboarding is implemented with [spatie/laravel-onboard](https://github.com/spatie/laravel-onboard). Steps are defined in code; completion is computed via callbacks (no per-step database columns).

## Overview

- **Package:** [spatie/laravel-onboard](https://github.com/spatie/laravel-onboard)
- **Feature flag:** `onboarding` (Laravel Pennant); can be org-delegated. When off, no redirect and no onboarding UI.
- **Middleware:** `EnsureOnboardingComplete` redirects authenticated users (with onboarding feature on and email verified) to the next unfinished step until they complete the flow or have `onboarding_completed` set.
- **Persistence:** Only `users.onboarding_completed` is stored. Step completion is derived from the model (e.g. email verified, profile filled, avatar present).

## Where steps are defined

Steps are registered in **`App\Providers\OnboardingServiceProvider::boot()`** using the `Onboard` facade. Each step is limited to `User::class` and has:

- **Title** – Display label (e.g. "Verify email", "Complete profile", "Get started").
- **Link** – URL the user should visit to complete the step (e.g. `/verify-email`, `/settings/profile`, `/onboarding`).
- **CTA** – Button/link text (e.g. "Verify email", "Complete profile", "Get started").
- **completeIf** – Closure that receives the `User` model (parameter must be named `$model`) and returns whether the step is complete.

Example (conceptually):

```php
Onboard::addStep('Verify email', User::class)
    ->link('/verify-email')
    ->cta('Verify email')
    ->completeIf(function (User $model): bool {
        return $model->email_verified_at !== null;
    });
```

## Middleware and redirects

- **EnsureOnboardingComplete** runs on the web middleware stack. It:
  - Skips when the user is guest, onboarding feature is off for the user, or email is not verified.
  - Skips when `user.onboarding_completed` is true (user is considered fully onboarded).
  - Otherwise uses `$user->onboarding()->inProgress()` and, when in progress, redirects to `$user->onboarding()->nextUnfinishedStep()->link`.
  - Excluded routes (step targets and auth) are listed in `EXCLUDED_ROUTES` so users can open those pages to complete steps.
- When `$user->onboarding()->finished()` is true, the middleware can set `onboarding_completed` so the DB stays in sync.

## Completing onboarding

- The last step ("Get started") is complete when `onboarding_completed` is true.
- The onboarding hub page (`/onboarding`) shows all steps and a **Go to Dashboard** button that POSTs to `onboarding.store`.
- **CompleteOnboardingAction** handles that request: it sets `onboarding_completed = true` and, when gamification is active, grants the "Profile Completed" achievement.

## API (on the User model)

The `User` model implements `Spatie\Onboard\Concerns\Onboardable` and uses `Spatie\Onboard\Concerns\GetsOnboarded`. You can use:

- `$user->onboarding()->inProgress()`
- `$user->onboarding()->finished()`
- `$user->onboarding()->percentageCompleted()`
- `$user->onboarding()->steps()` (collection of step objects with `title`, `link`, `cta`, `complete()`)
- `$user->onboarding()->nextUnfinishedStep()` (step or null)

See the [package README](https://github.com/spatie/laravel-onboard) for full API and options (e.g. `excludeIf`, model-specific steps).

## Related

- **Controller:** `OnboardingController` (show hub, store completion)
- **Action:** `CompleteOnboardingAction`
- **Routes:** `onboarding` (GET), `onboarding.store` (POST)
- **Frontend:** `resources/js/pages/onboarding/show.tsx`, `OnboardingCard` component; shared `onboarding` props from `HandleInertiaRequests`
- **User guide:** [Onboarding](../../user-guide/onboarding.md)
