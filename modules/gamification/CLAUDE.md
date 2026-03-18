# Gamification Module

Badges, points, levels, and achievements for users.

## Purpose

Adds gamification mechanics (XP, levels, achievements) to the application. Automatically grants initial XP when a user registers. Includes a Filament widget and achievements page.

## Structure

```
modules/gamification/
├── module.json
├── database/
│   └── seeders/GamificationSeeder.php
├── routes/
│   └── web.php
└── src/
    ├── GamificationServiceProvider.php
    ├── Features/GamificationFeature.php
    ├── Filament/Widgets/UserLevelWidget.php
    ├── Http/Controllers/AchievementsController.php
    └── Listeners/GrantGamificationOnUserCreated.php
```

## Key Classes

- **Feature**: `Modules\Gamification\Features\GamificationFeature`
- **Provider**: `Modules\Gamification\GamificationServiceProvider`
- **Listener**: `GrantGamificationOnUserCreated` — auto-grants XP on user creation (registered in boot when module is enabled)

## Notes

- `GiveExperience` and `HasAchievements` traits remain on the `User` model (package traits, harmless when module is disabled)
- The event listener is only registered when the module is enabled

## Toggle

Enable/disable via `config/modules.php` or `php artisan module:enable gamification` / `module:disable gamification`.
