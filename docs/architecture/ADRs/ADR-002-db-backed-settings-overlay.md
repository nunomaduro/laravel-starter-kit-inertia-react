# ADR-002: DB-backed settings with config overlay and per-org overrides

## Status

Accepted

## Context

The application had 541 `env()` calls across 49 config files. Changing runtime configuration (billing, mail, tenancy, AI providers, security, etc.) required editing `.env` and redeploying. In a multi-tenant setup, there was no way to give individual organizations different settings (e.g., different Stripe keys, different SMTP servers).

We needed:
- Runtime-configurable settings editable from an admin UI without redeployment.
- Backward compatibility — all existing `config('...')` consumers must keep working.
- Per-organization overrides for multi-tenant scenarios.
- Encrypted storage for secrets (API keys, passwords).

## Decision

We adopted a **dual-layer resolution chain** using spatie/laravel-settings:

1. **Global overlay** — `SettingsOverlayServiceProvider` writes all Spatie settings values into `config()` at boot time. The provider maintains a static `OVERLAY_MAP` mapping each settings class to its config keys.

2. **Per-org overlay** — `ApplyOrganizationSettings` middleware runs after `SetTenantContext`, loading org-specific overrides from the `organization_settings` table (cached 60 min per org) and writing them into `config()`.

3. **Filament admin UI** — 27 Filament pages (26 `SettingsPage` subclasses + 1 `ManageOrganizationOverrides` table page) for managing all settings.

4. **Encryption** — Spatie's `encrypted()` method for global settings; `Crypt::encryptString()` for org overrides with `is_encrypted` column flag.

Infrastructure settings (DB connection, cache driver, session, etc.) remain in `.env` since they're needed before the database is available.

## Consequences

- **Easier:** Change any runtime setting from Filament without redeployment; per-org billing/mail/AI settings just work; all `config()` consumers are automatically updated; encrypted secrets stored safely in DB.
- **Harder:** Settings changes require cache invalidation for queue workers (`settings:clear-cache`); new settings require adding to `OVERLAY_MAP`; debugging config values requires checking both DB and `.env` (overlay provider applies on top of env fallbacks). The 26 settings classes and migrations add file count.
