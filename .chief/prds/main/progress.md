# Progress Log

## 2026-03-07 - US-018
- Created `resources/js/components/ui/icon-button.tsx`: wraps `buttonVariants` with `size="icon"` default; requires accessible `label` prop for `aria-label`/`title`; supports `asChild` via Radix Slot.
- Created `resources/js/components/ui/button-group.tsx`: horizontal/vertical flex wrapper; `attached` prop removes inner border-radii and applies negative margin to visually join buttons.
- Created `resources/js/components/ui/fab.tsx`: fixed-position FAB with 4 `position` options; optional `actions` array enables speed-dial (shows labeled sub-buttons + animates FAB icon to X when open).
- Created `resources/js/components/ui/split-button.tsx`: primary `<Button>` + chevron dropdown trigger sharing same variant/size; uses `DropdownMenu` for the action list.
- Created `resources/js/components/ui/copy-button.tsx`: copies `value` to clipboard; animated CheckIcon fades in (scale) and CopyIcon fades out; reverts after configurable `timeout`; `onCopy` must be omitted from the base HTML interface to avoid conflict with React's native `onCopy: ClipboardEventHandler`.
- Created `resources/js/components/ui/swap.tsx`: controlled/uncontrolled toggle; `animation` prop supports `rotate`/`flip`/`fade`; uses `aria-pressed` for accessibility.
- `progress-button.tsx` already existed from US-012.
- `npx tsc --noEmit` ✓ | `npm run build` ✓
- **Learnings for future iterations:**
  - React's HTML button props include `onCopy: ClipboardEventHandler` — when adding a custom `onCopy?: (value: string) => void` prop, must `Omit<..., "onCopy">` from the base props to avoid interface extension conflict.
  - `Swap` component uses `aria-pressed` (not `aria-checked`) because it's a toggle button, not a checkbox.
  - `Fab` speed-dial shows labels as tooltips via absolutely-positioned text nodes; no extra tooltip library needed.
---

## 2026-03-07 - US-017
- Created layout primitives: `box.tsx` (polymorphic `as` prop via `React.JSX.IntrinsicElements`), `container.tsx` (max-width + padding CVA), `stack.tsx` (VStack/HStack wrappers), `grid.tsx` (Grid + GridItem with span CVA), `divider.tsx` (with optional label slot), `scroll-shadow.tsx` (CSS mask-image fade shadows), `masonry.tsx` (CSS columns with break-inside-avoid), `resizable.tsx` (wraps react-resizable-panels v4 Group/Panel/Separator).
- Created navigation components: `pagination.tsx` (headless + PaginationControl convenience), `bottom-nav.tsx` (fixed bottom mobile nav with badge), `tree-nav.tsx` (recursive tree with context, expand/select state), `collapsible-search.tsx` (animated expand/collapse search), `toc.tsx` (IntersectionObserver-driven active heading tracker).
- `stepper.tsx`, `animated-tabs.tsx`, `mode-toggle.tsx` already existed from earlier stories.
- `npx tsc --noEmit` ✓ | `npm run build` ✓
- **Learnings for future iterations:**
  - `Box` polymorphic component: use `keyof React.JSX.IntrinsicElements` (not `JSX.IntrinsicElements`) for the tag type, and `React.ComponentPropsWithoutRef<T>` to get the right HTML props.
  - `TreeNavProps`: must use `Omit<React.HTMLAttributes<HTMLElement>, "onSelect">` because `onSelect` conflicts with the native HTML event handler signature.
  - `react-resizable-panels` v4 exports are `Group`, `Panel`, `Separator` (not `PanelGroup`/`PanelResizeHandle`). The `Separator` component takes `data-panel-group-direction` and works with CSS attribute selectors.
  - Masonry layout with CSS `columns` + `break-inside-avoid` is the simplest pure-CSS approach; wrapping children in a `<div className="break-inside-avoid mb-4">` prevents column splits.
---

## 2026-03-07 - US-015
- Created `resources/js/lib/keyboard-shortcuts.ts`: module-level Map registry; exports `registerShortcut`, `unregisterShortcut`, `getShortcuts`, `subscribeToShortcuts`, and `useKeyboardShortcut` hook; handles modifier keys (mod/ctrl/cmd/shift/alt); prevents firing in editable elements for single-char shortcuts.
- Created `resources/js/components/ui/kbd.tsx`: simple styled `<kbd>` element for displaying key hints.
- Created `resources/js/components/ui/keyboard-shortcut-display.tsx`: Sheet panel listing all registered shortcuts grouped by scope; `?` key toggles the panel via `useKeyboardShortcut`; subscribes to registry changes for live updates.
- Updated `resources/js/app.tsx`: added `<KeyboardShortcutDisplay />` to the per-page wrapper alongside ThemeFromProps/Toaster.
- Updated `resources/js/hooks/index.ts`: re-exports `useKeyboardShortcut` from `@/lib/keyboard-shortcuts`.
- `npx tsc --noEmit` ✓ | `npm run build` ✓
- **Learnings for future iterations:**
  - The keyboard shortcuts registry is a module-level singleton (not React context) — works across component boundaries; `subscribeToShortcuts` lets components re-render when the registry changes.
  - Single-char shortcuts (like `?`) must be guarded to not fire when typing in inputs/textareas; modifier shortcuts (Mod+K) can fire anywhere.
  - The `KeyboardShortcutDisplay` component self-registers the `?` shortcut when mounted in `app.tsx`; no separate registration step needed.
  - `Kbd` component was created in `ui/kbd.tsx` for this story; US-020 also lists `kbd.tsx` — it already exists now.
---

## 2026-03-07 - US-014
- Created `resources/js/hooks/use-reduced-motion.ts`: reads `matchMedia('(prefers-reduced-motion: reduce)')` on init and subscribes to changes; returns boolean.
- Created `resources/js/hooks/use-focus-trap.ts`: traps Tab/Shift+Tab within a given `RefObject<HTMLElement>`; focuses first focusable element on activation; accepts `enabled` flag to toggle.
- Created `resources/js/components/ui/skip-to-content.tsx`: `sr-only` anchor that becomes visible (`focus:not-sr-only`) and jumps to `#main-content`; exported with `SkipToContentProps` type.
- Created `resources/js/hooks/index.ts`: barrel export for all hooks (useAppearance, useThemePreset, useCan, useClipboard, useFocusTrap, useInitials, useIsMobile, useMobileNavigation, useReducedMotion, useTwoFactorAuth).
- Updated `resources/js/components/ui/skeleton.tsx`: now calls `useReducedMotion()` and omits animation classes when reduced motion is preferred.
- `npx tsc --noEmit` ✓ | `npm run build` ✓
- **Learnings for future iterations:**
  - `feed.tsx` and `streaming-text.tsx` don't exist yet — `useReducedMotion` is applied to `skeleton.tsx` now; apply to the others when they are created.
  - `useFocusTrap` auto-focuses the first focusable element on mount; callers that want to preserve focus (e.g. drawers already handled by Radix) should pass `enabled={false}`.
  - The `SkipToContent` component must be the **first** element inside each app shell layout with `id="main-content"` on the main wrapper — this is deferred to US-025.
---

## 2026-03-07 - US-013
- Enhanced `button.tsx`: added `filled`/`soft`/`flat` variants, `color` prop (7 semantic colors via compound CVA variants), `isLoading` prop (shows spinner, disables button), `leftIcon`/`rightIcon` props. Used `Omit<React.ComponentProps<"button">, "color">` to avoid conflict with deprecated HTML `color` attribute.
- Enhanced `badge.tsx`: added `filled`/`soft` variants, `color` prop (7 semantic colors), `glow` prop (shadow glow).
- Enhanced `card.tsx`: added `skin` prop (`shadow`/`bordered`/`flat`/`elevated`), `hoverable` bool; respects `data-card-skin` attribute.
- Enhanced `input.tsx`: added `variant` (`outlined`/`filled`/`soft`), `size` (`xs`/`sm`/`md`/`lg`), `startContent`/`endContent` slots. Used `Omit<..., "size"> & { size?: InputSize | number }` to accept both HTML numeric size and new string size.
- Enhanced `textarea.tsx`: wraps `react-textarea-autosize` when `autoSize=true`; same `variant` options as input.
- Enhanced `alert.tsx`: added `filled`/`soft`/`outlined` variants + semantic `color` prop.
- Enhanced `tabs.tsx`: added `underline`/`pill`/`card`/`lifted` variants to `tabsListVariants` and updated `TabsTrigger` styles with group-data selectors for each new variant.
- Enhanced `avatar.tsx`: added `indicator` slot (status dot: online/offline/busy/away); exported `AvatarGroup` with `max` prop; added auto-color from name in `AvatarFallback`.
- Enhanced `skeleton.tsx`: added `animation` prop with `pulse`/`shimmer`/`wave` variants.
- Enhanced `tooltip.tsx`: added `TooltipRichContent` component with `title`/`description` props and an arrow.
- Enhanced `dialog.tsx`: added `size` prop (`xs`/`sm`/`md`/`lg`/`xl`/`fullscreen`) to `DialogContent`.
- Enhanced `sheet.tsx`: added `size` prop (`quarter`/`half`/`full`) and `backdropBlur` prop to `SheetContent`/`SheetOverlay`.
- Fixed `calendar.tsx`: destructured `color: _color` to prevent HTML `color: string` from conflicting with Button's new `color?: SemanticColor`.
- **Learnings for future iterations:**
  - When adding a prop to a component that conflicts with a deprecated HTML attribute (like `color` on `<button>` or `size` on `<input>`), use `Omit<React.ComponentProps<"element">, "conflicting-prop">` in the function signature.
  - Accepting `InputSize | number` for `size` prop allows backward compat with react-hook-form spreads that include native `size: number`.
  - `react-textarea-autosize` has a custom `Style` type `{height?: number}` incompatible with `CSSProperties` — omit `style` from props before spreading and cast the rest.
  - When spread props from a library (react-day-picker DayButton) flow through to a component with a new typed prop, destructure the conflicting prop in the parent component to prevent TypeScript errors.
---

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

## 2026-03-07 - US-016
- Created `resources/js/components/shells/app-shell.tsx`: collapsible sidebar + top header + main content area + optional right panel slot; includes `<SkipToContent />` and `id="main-content"` on `<main>`.
- Created `resources/js/components/shells/master-detail.tsx`: left list + right detail; stacks on mobile, uses `react-resizable-panels` (Group+Panel+Separator) side-by-side on desktop.
- Created `resources/js/components/shells/split-view.tsx`: horizontal or vertical two-pane split with draggable resizer via `react-resizable-panels`.
- Created `resources/js/components/shells/marketing-layout.tsx`: centered max-width layout with optional sticky nav and footer slots.
- Created `resources/js/components/shells/dashboard-layout.tsx`: stat cards row + main chart area + optional sidebar widgets column.
- All shells accept `className` and slot props; all include `<SkipToContent />` and `id="main-content"`.
- `npx tsc --noEmit` ✓ | `npm run build` ✓
- **Learnings for future iterations:**
  - `react-resizable-panels` v4 exports `Group`, `Panel`, `Separator` — NOT `PanelGroup`/`PanelResizeHandle`. Use `Group` with `orientation` prop (not `direction`).
  - Shells live in `resources/js/components/shells/` (separate from `ui/`) — import with `@/components/shells/app-shell`.
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
