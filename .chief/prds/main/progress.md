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
