# Gamification (Gamification Module)

> **Module location:** `modules/gamification/` — enable/disable via `config/modules.php` or `php artisan module:enable gamification`.

Gamification is implemented with [cjmellor/level-up](https://github.com/cjmellor/level-up). It is gated by the **Gamification** Pennant feature (`Modules\Gamification\Features\GamificationFeature`); when inactive, no XP or achievements are awarded and the Level & achievements UI is hidden.

## Behaviour

- **User created:** `App\Events\User\UserCreated` is dispatched from `UserObserver::created`. `Modules\Gamification\Listeners\GrantGamificationOnUserCreated` awards 10 XP (reason: "Signed up") when the feature is active.
- **Onboarding completed:** `CompleteOnboardingAction` grants the **Profile Completed** achievement when the user completes onboarding and the feature is active.

## Data

- **Levels:** Seeded by `Modules\Gamification\Database\Seeders\GamificationSeeder` (100 levels; formula: cumulative XP per level).
- **Achievements:** Same seeder creates at least **Profile Completed** (“Complete your profile during onboarding”). Others can be added in the seeder or via Filament.

## UI

- **Settings → Level & achievements:** Inertia page at `settings/achievements` (route `achievements.show`), visible in settings nav when `features.gamification` is true. Shows level, XP, progress to next level, and unlocked achievements.
- **Filament:** `Modules\Gamification\Filament\Widgets\UserLevelWidget` shows level, XP, and achievements count on the dashboard when gamification is active for the current user.

## Config and feature flag

- Package config: `config/level-up.php` (published from the package).
- Feature: `config/feature-flags.php` (`gamification` in `inertia_features` and `route_feature_map`). Add `gamification` to `GLOBALLY_DISABLED_MODULES` to turn it off for everyone.

## Deferred

Organization-created and referral-based achievements are not implemented; add events/listeners and achievements when needed.
