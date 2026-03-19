# onboarding/show

## Purpose

Multi-step onboarding hub: user sees all onboarding steps (e.g. Verify email, Complete profile, Get started), progress, and a primary action that either links to the next unfinished step or submits completion. Submitting marks onboarding complete and redirects to the dashboard. Steps and completion are driven by spatie/laravel-onboard (see [Onboarding](../../backend/onboarding.md)).

## Location

`resources/js/pages/onboarding/show.tsx`

## Route Information

- **URL**: `onboarding`
- **Route Name**: `onboarding` (GET), `onboarding.store` (POST)
- **Middleware**: `auth`, `feature:onboarding`

## Props

| Prop | Type | Description |
|------|------|-------------|
| `steps` | `array` | List of step objects (title, link, cta, complete) from Spatie |
| `nextStep` | `object \| null` | Next unfinished step (title, link, cta) |
| `percentageCompleted` | `number` | 0–100 progress |
| `inProgress` | `boolean` | Whether onboarding is still in progress |
| `alreadyCompleted` | `boolean` | Whether user has already completed (e.g. re-opened from Settings) |
| `status` | `string` | Flash message (e.g. after completion) |

## Related Components

- **Controller**: `OnboardingController@show`, `OnboardingController@store`
- **Action**: `CompleteOnboardingAction`
- **Layout**: `AuthLayout`
- **Shared data**: `onboarding` (steps, inProgress, percentageCompleted, nextStep) is also shared via HandleInertiaRequests for the OnboardingCard component
