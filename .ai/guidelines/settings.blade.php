# Settings & Configuration Guidelines

- Runtime configuration is DB-backed via **spatie/laravel-settings**. Settings classes live in `app/Settings/` and are managed via Filament admin pages.
- Never use `env()` outside of config files. Use `config('key')` — the `SettingsOverlayServiceProvider` writes DB values into config at boot.
- When adding a new setting: create the Settings class, create a settings migration in `database/settings/`, add the mapping to `SettingsOverlayServiceProvider::OVERLAY_MAP`, and create a Filament `SettingsPage`.
- Settings that hold secrets (API keys, passwords) must define `public static function encrypted(): array` returning the encrypted property names.
- 7 groups are org-overridable (Billing, Mail, Stripe, Paddle, LemonSqueezy, Prism, AI). Set `'orgOverridable' => true` in `OVERLAY_MAP` to enable per-org overrides.
- Per-org overrides are stored in the `organization_settings` table and applied by `ApplyOrganizationSettings` middleware after `SetTenantContext`.
- Infrastructure settings (`APP_KEY`, `DB_*`, `CACHE_STORE`, `SESSION_DRIVER`, `QUEUE_CONNECTION`, `REDIS_*`, `LOG_CHANNEL`) stay in `.env` — they are needed before the DB is available.
- See `docs/developer/backend/settings.md` for full documentation.
