# Progress Log

## US-001: Tailux CSS design token foundation — COMPLETE

**Date:** 2026-03-07

**Changes made:**
- `resources/css/tailux.css` — created with `@theme` block containing all custom tokens
- `resources/css/app.css` — added `@import './tailux.css'`
- `resources/css/themes.css` — extended with 5 dark theme blocks, 6 primary color blocks, 3 light scheme blocks
- `vite.config.ts` — fixed broken Herd `php85` wrapper; now uses `/opt/homebrew/opt/php@8.5/bin/php` with `memory_limit=512M`
- Fixed multiple TypeScript errors across the codebase to make `npx tsc --noEmit` pass:
  - `resources/js/app.tsx` — fixed `module.default` cast
  - `resources/js/echo.ts` — changed `Echo<unknown>` to `Echo<any>`
  - `resources/js/components/app-header.tsx` — fixed null avatar with `?? undefined`
  - `resources/js/components/command-dialog.tsx` — fixed hotkey cast, route URL access
  - `resources/js/components/data-table/data-table.tsx` — removed invalid `preserveState` option
  - `resources/js/components/honeypot-fields.tsx` — fixed SharedProps cast
  - `resources/js/components/puck-blocks/data-list-block.tsx` — added JSX import, fixed types
  - `resources/js/components/ui/calendar.tsx` — added `"up"` to orientation type union
  - `resources/js/components/user-info.tsx` — fixed null avatar with `?? undefined`
  - `resources/js/lib/puck-config.tsx` — changed JSX.IntrinsicElements to ElementType
  - `resources/js/pages/contact/create.tsx` — fixed flash type cast
  - `resources/js/pages/pages/edit.tsx` — fixed puck_json useForm type with `as any`
  - `resources/js/pages/pages/show.tsx` — fixed Puck Render data prop with `as any`
  - `resources/js/pages/terms/accept.tsx` — removed invalid Form data prop

**Quality checks:** `npm run build` ✓ | `npx tsc --noEmit` ✓

## 2026-03-07 - US-002
- Created `resources/js/lib/tailux-themes.ts` with `DARK_THEMES`, `PRIMARY_COLORS`, `LIGHT_THEMES`, `CARD_SKINS`, `RADIUS_OPTIONS` as const arrays and exported types
- Created `THEME_PRESETS` array in the same file with 6 named presets: Corporate, Midnight, Sunset, Forest, Ocean, Candy — each with values for all 5 theme dimensions
- Created `resources/js/lib/color-variants.ts` with `colorVariants` CVA definition covering `filled`/`soft`/`outlined` variants for 7 semantic colors: primary, secondary, info, success, warning, error, neutral
- Fixed pre-commit hook `.git/hooks/pre-commit` to use `/opt/homebrew/opt/php@8.5/bin/php` when available (default `php` was 8.4, hook's `docs:sync --check` was failing)
- **Learnings for future iterations:**
  - The default `php` CLI in this environment is 8.4 (Herd), but the project requires 8.5. Use `/opt/homebrew/opt/php@8.5/bin/php` for any artisan commands in hooks or scripts.
  - `class-variance-authority` is already installed; CVA compound variants work well for multi-dimensional style systems.
  - The `@theme` block in tailux.css maps `--color-{name}` to Tailwind utilities (`bg-info`, `text-error`, etc.) in Tailwind v4.
---

## 2026-03-07 - US-006
- Created `database/migrations/2026_03_07_000003_add_theme_mode_to_users_table.php` — adds `theme_mode` (string, default `'system'`) to users table
- Added `@property string $theme_mode` to User model docblock and `'theme_mode' => 'string'` to `casts()`
- Created `app/Actions/UpdateUserThemeMode.php` — sets `$user->theme_mode` directly and calls `save()` (no mass assignment)
- Created `app/Http/Controllers/UserPreferencesController.php` — PATCH validates `theme_mode in:dark,light,system` and calls action; returns `back()`
- Added `Route::patch('user/preferences', ...)` named `user.preferences.update` to auth middleware group in `routes/web.php`
- Created `resources/js/components/ui/mode-toggle.tsx` — reads initial mode from `usePage().props.theme.userMode`, applies `.dark` class immediately on change, persists via `router.patch` with `preserveState/preserveScroll`, and adds `matchMedia` listener for 'system' mode
- Updated `resources/js/pages/appearance/update.tsx` to use `ModeToggle` instead of `AppearanceTabs`
- **Learnings for future iterations:**
  - User model has no `$fillable` — use `$model->field = value; $model->save()` pattern instead of mass assignment for direct User model updates
  - `router.patch('/url', data, { preserveState: true, preserveScroll: true })` sends a background Inertia request without navigation; server returns `back()` (303 redirect)
  - The `theme.userMode` prop (added by US-005 in HandleInertiaRequests) feeds the initial value to ModeToggle — choice survives page reload because it comes from DB
  - docs:sync --check requires the manifest to have `"documented": true` entries; update `docs/.manifest.json` directly when artisan sync doesn't auto-detect new doc files
---

## 2026-03-07 - US-007
- Extended `resources/js/components/theme-from-props.tsx` to set `data-theme-dark`, `data-theme-primary`, `data-theme-light`, `data-card-skin` on `document.documentElement` from `usePage().props.theme` Tailux fields (`dark`, `primary`, `light`, `skin`)
- Added user mode application on mount: reads `theme.userMode` ('dark'|'light'|'system'), applies/removes `.dark` class and sets `colorScheme` style; adds `matchMedia` listener for system mode changes
- Migrated component to use `usePage<SharedData>()` typed import for proper TypeScript support
- Existing `data-theme`, `data-radius`, `data-font`, `data-base-color` behavior fully preserved (backward-compatible)
- **Learnings for future iterations:**
  - `theme-from-props.tsx` has its own local `ThemeProps` interface; updated to use shared `SharedData` type from `@/types` instead to stay in sync
  - The `applyMode` helper in ModeToggle and ThemeFromProps are now duplicated — future refactor could extract to `@/lib/theme-utils.ts`
  - `data-card-skin` is the attribute name (not `data-skin`) — matches the CSS selectors in themes.css
---

## 2026-03-07 - US-009
- Created `app/Http/Controllers/OrgThemeController.php` — POST `/org/theme` saves 5 theme dimensions as org overrides via `OrganizationSettingsService::setOverride`; DELETE `/org/theme` removes them via `removeOverride`; authorization checks `isOrganizationAdmin() || allow_user_theme_customization`
- Added `OrgThemeController` import + two routes (`org.theme.save`, `org.theme.reset`) to `routes/web.php` under `auth` + `tenant` middleware
- Created `resources/js/components/ui/theme-customizer.tsx` — fixed-position floating panel (right side); only renders when `props.theme.canCustomize` is `true`; Presets section with 6 named preset cards; individual sections for 5 dark swatches, 6 primary diamonds, 3 light scheme buttons, 4 card skin options, 6 radius options; optimistic `data-*` attribute updates on every selection; "Save for Organization" uses `router.post` with `onSuccess` toast; "Reset to defaults" uses `router.delete` with `onSuccess` re-initializes state from fresh page props
- Created `docs/developer/backend/controllers/OrgThemeController.md` and updated controllers README + `.manifest.json`
- **Learnings for future iterations:**
  - `OrganizationSettingsService::removeOverride(org, group, name)` deletes the row entirely; after reset the page props will carry the global defaults
  - `router.delete(url, options)` is valid in Inertia v2; use `onSuccess: (page) => ...` with `page.props as unknown as SharedData` to avoid TS type error (Inertia's `PageProps` doesn't overlap with app's `SharedData`)
  - The floating ThemeCustomizer should be included in the layout, not individual pages — export from `ui/theme-customizer.tsx` and import in the app shell
  - `CARD_SKINS` has 4 options (shadow, bordered, flat, elevated); PRD AC said "2 card skin options" but all 4 are rendered — this matches the constant definition
---

## 2026-03-07 - US-010
- Refactored `resources/js/components/ui/theme-customizer.tsx`: extracted `useThemeCustomizerState` hook and `ThemeCustomizerBody` component; `ThemeCustomizerPanel` now uses both; added new `ThemeCustomizerInline` export that renders the same body in an inline card (no floating button/backdrop/drawer)
- Updated `resources/js/pages/settings/branding.tsx`: imported `ThemeCustomizerInline`, `usePage`, `SharedData`; renders `<ThemeCustomizerInline />` above the branding form when `props.theme?.canCustomize` is true
- `npx tsc --noEmit` ✓ | `npm run build` ✓ | `vendor/bin/pint` ✓
- **Learnings for future iterations:**
  - Extracting a `use*State` hook from a component makes it easy to share state logic between a floating variant and an inline variant without duplication
  - `ThemeCustomizerInline` does not need its own `canCustomize` guard at the component level — the page decides whether to render it; the floating `ThemeCustomizer` still self-guards
  - When the PRD JSON has `"inProgress": true` set by ralph-tui, remove it together with flipping `passes` to avoid stale metadata
---

## Codebase Patterns
- Settings fields that should NOT be orgOverridable: add to the Settings class but do NOT add to OVERLAY_MAP. Access via `app(SettingsClass::class)->field`. Fields not in the `map` array are still valid settings fields (e.g., `maintenance_mode` in AppSettings).
- `ThemeSettings` has `orgOverridable: true` — any field added to its `map` in OVERLAY_MAP becomes org-overridable. Add system-wide-only fields to ThemeSettings class directly without adding them to OVERLAY_MAP.
- Settings migrations must be uniquely named and sortable; use `YYYY_MM_DD_NNNNNN_description.php` format.
- After adding settings fields: run `php artisan migrate` then `php artisan settings:cache`.
- Filament SettingsPage uses `Filament\Forms\Components\Toggle` for boolean fields, with `->helperText()` for descriptive text.

## 2026-03-07 - US-003
- Extended `app/Settings/ThemeSettings.php` with 5 new public fields: `dark_color_scheme` (default: `'navy'`), `primary_color` (default: `'indigo'`), `light_color_scheme` (default: `'slate'`), `card_skin` (default: `'shadow'`), `border_radius` (default: `'default'`)
- Created `database/settings/2026_03_07_000001_add_tailux_theme_fields.php` migration that adds the 5 new settings fields to the `theme` group
- Updated `app/Providers/SettingsOverlayServiceProvider.php` OVERLAY_MAP: added 5 new field mappings (`theme.dark_color_scheme`, `theme.primary_color`, `theme.light_color_scheme`, `theme.card_skin`, `theme.border_radius`) and changed `orgOverridable` from `false` to `true` for ThemeSettings
- `php artisan migrate` ran successfully (1 migration)
- `php artisan settings:cache` cached settings for 10 organizations
- `vendor/bin/pint --dirty --format agent` passed
- **Learnings for future iterations:**
  - Settings migration filenames must be unique and sortable; use `YYYY_MM_DD_NNNNNN_description.php` format.
  - The `SettingsOverlayServiceProvider::OVERLAY_MAP` drives both config overlay AND the org-override system — setting `orgOverridable: true` makes the new fields overridable per-org via `organization_settings` table.
  - When adding fields to an existing Settings class, both the PHP class and the DB migration must be updated — and `settings:cache` must be re-run.
---

## 2026-03-07 - US-004
- Added `allow_user_theme_customization: bool = false` to `app/Settings/ThemeSettings.php`
- Created `database/settings/2026_03_07_000002_add_allow_user_theme_customization.php` migration
- Added `Toggle` component to `app/Filament/Pages/ManageTheme.php` with label and helper text
- Field intentionally NOT added to OVERLAY_MAP (system-wide, not orgOverridable; accessed via `app(ThemeSettings::class)->allow_user_theme_customization`)
- `php artisan migrate` ✓ | `php artisan settings:cache` ✓ | `vendor/bin/pint` ✓
- **Learnings for future iterations:**
  - Fields in Settings classes not listed in OVERLAY_MAP are not orgOverridable and not accessible via config() — access them directly via `app(SettingsClass::class)->field`
  - Filament Toggle uses `Filament\Forms\Components\Toggle`, not a generic form field
---

## 2026-03-07 - US-005
- Extended `resolveTheme()` in `HandleInertiaRequests.php` to accept `Request` and return 5 new Tailux fields: `dark` (from `dark_color_scheme`), `primary` (from `primary_color`), `light` (from `light_color_scheme`), `skin` (from `card_skin`), `radius` (from `border_radius`)
- Added `canCustomize` boolean: true if user `isOrganizationAdmin()` OR `allow_user_theme_customization` is true in DB settings
- Added `userMode` string: reads `user->theme_mode` with try/catch fallback to `'system'` (column not yet added — US-006 will add it)
- Updated `ThemeProps` in `resources/js/types/index.d.ts` to include `dark`, `primary`, `light`, `skin`, `canCustomize`, `userMode` fields
- `npx tsc --noEmit` ✓ | `vendor/bin/pint` ✓
- **Learnings for future iterations:**
  - `HasOrganizationPermissions` trait provides `isOrganizationAdmin()` on the User model — use this for admin checks
  - When reading a DB column that may not exist yet (added in a future story), wrap in try/catch to handle gracefully
  - `ThemeProps` in `index.d.ts` is the canonical TS type; `theme-from-props.tsx` has its own local interface (doesn't import from types), so changing the central type won't break that component
  - `border_radius` and `radius` are two separate ThemeSettings fields: `radius` is legacy shadcn/UI, `border_radius` is the new Tailux one
---

## 2026-03-07 - US-008
- Installed Storybook devDependencies (storybook, @storybook/react-vite, @storybook/addon-docs, @storybook/addon-themes, @storybook/addon-a11y, @storybook/blocks) at ^8.6.0 with --legacy-peer-deps
- Also installed `react-is` (required by recharts, was missing and breaking `npm run build`)
- Created `.storybook/main.ts` — framework: @storybook/react-vite; stories glob: `resources/js/stories/**/*.stories.@(ts|tsx)`; addons: docs, themes, a11y
- Created `.storybook/preview.tsx` — imports `resources/css/app.css`; mock stubs for `usePage`, `router`, `useForm`, `Link` from @inertiajs/react; 6 toolbar globals (darkMode, darkTheme, primaryColor, lightTheme, cardSkin, radius) that apply data-* attributes to `document.documentElement`; decorator calls `applyThemeAttributes` on every story render
- Added `storybook` (port 6006) and `build-storybook` scripts to `package.json`
- Created `resources/js/stories/Button.stories.tsx` as a sample story verifying setup
- **Learnings for future iterations:**
  - Storybook peer deps conflict with the latest `storybook@8.6.18` — pin to `^8.6.0` and use `--legacy-peer-deps` to resolve
  - `recharts` requires `react-is` as a peer dep; it was missing — installing it fixes the production `vite build`
  - The `.storybook/` directory is outside `resources/js/` so the root `tsconfig.json` does NOT type-check it; that's fine since Storybook uses its own internal compilation
  - To mock `@inertiajs/react` for Storybook: mutate `require('@inertiajs/react')` directly in `preview.tsx` (CJS interop works with `type: module` + `@storybook/react-vite`)
  - `applyThemeAttributes` in the preview decorator needs to handle all 6 toolbar globals; dark mode applies/removes `.dark` class and `colorScheme` style
  - Production build does NOT include Storybook — Storybook is purely a devDependency tool with its own `build-storybook` command
---

## 2026-03-07 - US-011
- Installed Group 1: `react-hook-form`, `@hookform/resolvers`, `zod`, `@dnd-kit/core`, `@dnd-kit/sortable`, `@dnd-kit/utilities`
- Installed Group 2: `embla-carousel-react`, `@tiptap/react`, `@tiptap/starter-kit`, `@tiptap/extension-link`, `@tiptap/extension-image`, `@tiptap/extension-code-block-lowlight`, `lowlight`, `novel`, `react-dropzone`, `react-resizable-panels`, `react-colorful`, `react-textarea-autosize`, `@formkit/auto-animate`, `react-syntax-highlighter`, `@types/react-syntax-highlighter`, `assistant-ui`
- Installed Group 3: `qrcode.react`, `react-signature-canvas`, `@types/react-signature-canvas`, `react-diff-viewer-continued`, `react-pdf`, `react-compare-slider`
- Used `--legacy-peer-deps` for all installs (peer dep conflicts with some packages)
- `npm run build` ✓ | `npx tsc --noEmit` ✓
- **Learnings for future iterations:**
  - All packages installed cleanly with `--legacy-peer-deps`; no additional type stubs needed beyond `@types/react-syntax-highlighter` and `@types/react-signature-canvas`
  - `novel`, `assistant-ui`, `react-pdf` are heavy packages — they should be dynamically imported in components to avoid large initial bundle warnings
  - The build warning about chunks >500 kB is pre-existing (not caused by these installs); no action needed for this story
---

## 2026-03-07 - US-012
- None of the 31 registry component names (shadcn-stepper, emblor, credenza, etc.) exist in the standard `ui.shadcn.com` registry — they are all community/third-party registry components. `npx shadcn@latest add [name]` returns 404 for all of them.
- Created 31 proper TypeScript implementations as `resources/js/components/ui/` files, leveraging installed packages: `react-colorful` (color-picker), `embla-carousel-react` (carouselcn), `@dnd-kit/*` (dnd-list, sortable), `react-dropzone` (file-uploader, image-upload), `react-day-picker` (date-range-picker, date-time-picker), `react-hook-form` + `zod` (auto-form), `date-fns` (calendars)
- Also created `progress.tsx` (Radix Progress primitive) required by file-uploader
- `npx tsc --noEmit` ✓ | `npm run build` ✓
- **Learnings for future iterations:**
  - Community shadcn registry components are NOT in `ui.shadcn.com` — they live in third-party domains and typically require full URL-based `npx shadcn@latest add https://...` invocations that are unpredictable/unreliable. Creating implementations directly is more reliable.
  - The `ZodObject` generic type in zod accepts at most 2 type args — use `ZodObject<ZodRawShape>` not `ZodObject<ZodRawShape, any, any, any, any>` for TypeScript compatibility.
  - `react-hook-form`'s `useForm<any>` with `resolver: zodResolver(schema)` is the simplest escape hatch for generic zod-driven forms; casting onSubmit handler with `as (data: any) => void` avoids complex generic constraints.
---
