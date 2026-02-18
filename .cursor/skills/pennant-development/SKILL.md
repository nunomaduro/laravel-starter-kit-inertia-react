---
name: pennant-development
description: "Manages feature flags with Laravel Pennant. Activates when creating, checking, or toggling feature flags; showing or hiding features conditionally; implementing A/B testing; working with @feature directive; or when the user mentions feature flags, feature toggles, Pennant, conditional features, rollouts, or gradually enabling features."
license: MIT
metadata:
  author: laravel
---

# Pennant Features

## When to Apply

Activate this skill when:

- Creating or checking feature flags
- Managing feature rollouts
- Implementing A/B testing

## Documentation

Use `search-docs` for detailed Pennant patterns and documentation.

## In this app

This app uses class-based features in `App\Features\*` and `App\Support\FeatureHelper` for checks. Use `FeatureHelper::isActiveForKey($key, $user)` or `FeatureHelper::isActiveForClass($featureClass, $user)` instead of raw `Feature::for($user)->active()` — FeatureHelper respects `GLOBALLY_DISABLED_MODULES` (env), so globally disabled features are always off for everyone. See `docs/developer/backend/feature-flags.md`.

## Basic Usage

### Defining Features

<!-- Defining Features -->
```php
use Laravel\Pennant\Feature;

Feature::define('new-dashboard', function (User $user) {
    return $user->isAdmin();
});
```

### Checking Features

<!-- Checking Features -->
```php
if (Feature::active('new-dashboard')) {
    // Feature is active
}

// With scope
if (Feature::for($user)->active('new-dashboard')) {
    // Feature is active for this user
}
```

### Blade Directive

<!-- Blade Directive -->
```blade
@feature('new-dashboard')
    <x-new-dashboard />
@else
    <x-old-dashboard />
@endfeature
```

### Activating / Deactivating

<!-- Activating Features -->
```php
Feature::activate('new-dashboard');
Feature::for($user)->activate('new-dashboard');
```

## Verification

1. Check feature flag is defined
2. Test with different scopes/users

## Common Pitfalls

- Forgetting to scope features for specific users/entities
- Not following existing naming conventions