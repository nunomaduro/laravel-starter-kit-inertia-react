# PRD: Complete Component Library + Per-Org Theme Switcher + Storybook

## Introduction

Build a comprehensive, AI-first, maximally-reusable component library covering 100% of Tailux's component vocabulary (and beyond), with a bold per-organization theme switcher that stores each org's visual identity in settings. Uses shadcn registry first, npm packages second, custom code only where needed. Storybook serves as the internal dev catalog.

**4 deliverables:**
1. Full component library (150+ typed components across ui/, charts/, maps/, ai/, composed/, shells/)
2. Per-org live theme customizer with named presets, system-controlled user access, and per-user dark/light/system mode preference
3. SaaS-specific feature components (billing banners, feature gates, onboarding, admin tools, global search)
4. Storybook with theme toolbar

## Goals

- Establish a shared CSS design token system (`tailux.css`) compatible with Tailwind CSS v4
- Enable per-organization visual identity stored in DB-backed settings (dark theme, primary color, border radius, card skin) with named one-click theme presets
- Allow system admins to decide whether org users (non-admins) can customize their own theme via a system setting
- Support per-user dark/light/system mode preference independently of the org theme
- Provide a live, floating theme customizer — visible to org admins always; visible to regular users only when the system setting permits
- Deliver 200+ typed, dark-mode-ready, theme-aware React components covering UI primitives, SaaS patterns, admin tools, media, AI, charts, and maps
- Provide pre-built app shell layout templates so pages can be assembled from shells rather than built from scratch
- Document every component in Storybook with argTypes and autodocs
- All changes must be backward-compatible with existing `ui/` components

## User Stories

### US-001: Tailux CSS design token foundation
**Priority:** 1
**Description:** As a developer, I need the Tailux CSS variable system installed so all subsequent components and theme switching work correctly.

**Acceptance Criteria:**
- [ ] `resources/css/tailux.css` created with `@theme` block containing all custom tokens: `--text-tiny`, `--text-xs-plus`, `--text-sm-plus`, `--shadow-soft`, `--shadow-soft-dark`, `--ease-elastic`, `--color-surface-1/2/3`, `--color-secondary`, `--color-info`, `--color-success`, `--color-warning`, `--color-error`
- [ ] `resources/css/app.css` contains `@import './tailux.css'`
- [ ] `resources/css/themes.css` extended with 5 dark theme blocks: `[data-theme-dark="navy|mirage|mint|black|cinder"]`
- [ ] `resources/css/themes.css` extended with 6 primary color blocks: `[data-theme-primary="indigo|blue|green|amber|purple|rose"]`
- [ ] `resources/css/themes.css` extended with 3 light scheme blocks: `[data-theme-light="slate|gray|neutral"]`
- [ ] `npm run build` passes with no errors
- [ ] `npx tsc --noEmit` passes

---

### US-002: Shared TypeScript theme constants
**Priority:** 2
**Description:** As a developer, I need typed constants for all theme options and named presets so components and settings can reference them without magic strings.

**Acceptance Criteria:**
- [ ] `resources/js/lib/tailux-themes.ts` created with `DARK_THEMES`, `PRIMARY_COLORS`, `LIGHT_THEMES`, `CARD_SKINS`, `RADIUS_OPTIONS` as const arrays with exported types
- [ ] `THEME_PRESETS` array defined in the same file, each preset having a name (e.g. `"Corporate"`, `"Midnight"`, `"Sunset"`, `"Forest"`, `"Ocean"`, `"Candy"`) and values for all 5 theme dimensions
- [ ] `resources/js/lib/color-variants.ts` created with `colorVariants` CVA definitions for `filled`, `soft`, `outlined` variants across 7 semantic colors (primary, secondary, info, success, warning, error, neutral)
- [ ] `npx tsc --noEmit` passes

---

### US-003: ThemeSettings backend — add Tailux theme fields
**Priority:** 3
**Description:** As an admin, I need the system to store the org's dark theme, primary color, light scheme, card skin, and border radius in the database so visual identity persists.

**Acceptance Criteria:**
- [ ] `app/Settings/ThemeSettings.php` extended with 5 new public fields: `dark_color_scheme` (default: `'navy'`), `primary_color` (default: `'indigo'`), `light_color_scheme` (default: `'slate'`), `card_skin` (default: `'shadow'`), `border_radius` (default: `'default'`)
- [ ] New settings migration created in `database/settings/` for the 5 new fields
- [ ] `php artisan migrate` completes successfully
- [ ] `SettingsOverlayServiceProvider::OVERLAY_MAP` updated with theme field mappings, `orgOverridable: true`
- [ ] `php artisan settings:cache` completes successfully
- [ ] `vendor/bin/pint --dirty --format agent` passes

---

### US-004: System setting — allow users to customize their own theme
**Priority:** 4
**Description:** As a system admin, I want a global toggle "Allow users to customize their own theme" so I can decide whether regular (non-admin) users in any organization can use the theme customizer to change their personal theme preferences.

**Acceptance Criteria:**
- [ ] A new boolean field `allow_user_theme_customization` (default: `false`) added to an appropriate existing Settings class (e.g. `GeneralSettings`) or a new class if none fits
- [ ] A new settings migration in `database/settings/` adds the field
- [ ] `php artisan migrate` completes successfully
- [ ] The field is NOT `orgOverridable` — it is a system-wide toggle only the system admin controls
- [ ] A Filament settings page exposes this as a labeled toggle: "Allow users to customize their own theme" with helper text: "When enabled, all authenticated users can use the theme customizer. When disabled, only organization admins can."
- [ ] `php artisan settings:cache` completes successfully
- [ ] `vendor/bin/pint --dirty --format agent` passes

---

### US-005: Share theme data and permissions via Inertia
**Priority:** 5
**Description:** As a developer, I need the current org's theme settings, the user's personal mode preference, and customization permissions all passed to the frontend on every page load.

**Acceptance Criteria:**
- [ ] `app/Http/Middleware/HandleInertiaRequests.php` `share()` method includes a `'theme'` key with: `dark`, `primary`, `light`, `skin`, `radius` (from `ThemeSettings`); `canCustomize` boolean — `true` if current user is an org admin OR `allow_user_theme_customization` is `true`; `userMode` — the authenticated user's personal preference (`'dark'`, `'light'`, or `'system'`, default `'system'`) read from `user_preferences` or a users table column
- [ ] Frontend `usePage().props.theme` is typed correctly in the project's types file
- [ ] `npx tsc --noEmit` passes
- [ ] `vendor/bin/pint --dirty --format agent` passes

---

### US-006: Per-user dark/light/system mode preference
**Priority:** 6
**Description:** As a user, I want to choose whether the app uses dark mode, light mode, or follows my operating system preference, independently of the org's chosen color palette.

**Acceptance Criteria:**
- [ ] Users table (or a `user_preferences` table) gains a `theme_mode` column: `'dark' | 'light' | 'system'` (default `'system'`)
- [ ] Migration created and `php artisan migrate` passes
- [ ] A mode toggle component (`resources/js/components/ui/mode-toggle.tsx`) renders 3 options: Dark / Light / System (with OS icon), placed in the existing user settings or app header
- [ ] Selecting a mode: (1) immediately applies `.dark` class or removes it from `document.documentElement` based on selection; (2) persists the choice via PATCH to a `/user/preferences` route; (3) choice survives page reload
- [ ] "System" mode respects `prefers-color-scheme` via a `matchMedia` listener that updates the class reactively if the OS preference changes
- [ ] `vendor/bin/pint --dirty --format agent` passes
- [ ] `npx tsc --noEmit` passes

---

### US-007: Apply Tailux theme attributes on page load
**Priority:** 7
**Description:** As a user, I want the page to load with my organization's correct dark theme, primary color, card skin, and my personal mode preference already applied so there is no flash of incorrect styling.

**Acceptance Criteria:**
- [ ] `resources/js/components/theme-from-props.tsx` extended to set `data-theme-dark`, `data-theme-primary`, `data-theme-light`, `data-card-skin`, `data-radius` on `document.documentElement` from `usePage().props.theme`
- [ ] `theme-from-props.tsx` also applies user mode on mount: if `userMode = 'dark'` → add `.dark`; if `userMode = 'light'` → remove `.dark`; if `userMode = 'system'` → apply based on `matchMedia('(prefers-color-scheme: dark)')`
- [ ] Existing `data-theme`, `data-radius`, `data-font` behavior is unchanged (backward-compatible)
- [ ] Setting `dark_color_scheme = 'cinder'` in DB → page loads with Cinder palette applied
- [ ] `npx tsc --noEmit` passes

---

### US-008: Storybook setup with Tailux theme toolbar
**Priority:** 8
**Description:** As a developer, I need Storybook running with a theme toolbar so I can visually catalog and test every component across all theme combinations without running the Laravel backend.

**Acceptance Criteria:**
- [ ] Storybook devDependencies installed: `storybook`, `@storybook/react-vite`, `@storybook/addon-docs`, `@storybook/addon-themes`, `@storybook/addon-a11y`, `@storybook/blocks`
- [ ] `.storybook/main.ts` created; stories glob points to `resources/js/stories/**/*.stories.@(ts|tsx)`; framework is `@storybook/react-vite`
- [ ] `.storybook/preview.tsx` created with: mock stubs for `usePage`, `Link`, `router`, `useForm` from `@inertiajs/react`; toolbar globals for `darkMode`, `darkTheme`, `primaryColor`, `lightTheme`, `cardSkin`, `radius` that apply corresponding data attributes
- [ ] `npm run storybook` starts on port 6006 with no errors, without requiring Laravel to be running
- [ ] All 5 dark themes, all 6 primary colors, and dark/light/system mode switch via toolbar
- [ ] `npm run build` does not include Storybook in production bundle

---

### US-009: Theme Customizer component with presets
**Priority:** 9
**Description:** As an admin (or permitted user), I want a floating theme panel where I can apply a named preset in one click or fine-tune individual dimensions, then save for the organization.

**Acceptance Criteria:**
- [ ] `resources/js/components/ui/theme-customizer.tsx` created as a fixed-position floating panel (right side of screen)
- [ ] The floating button is only rendered when `usePage().props.theme.canCustomize` is `true`
- [ ] Panel has a "Presets" section at the top showing named preset cards (e.g. Corporate, Midnight, Sunset, Forest, Ocean, Candy); clicking a preset applies all 5 dimensions at once
- [ ] Panel also has individual sections for: 5 dark theme swatches; 6 primary color diamonds; 3 light scheme previews; 2 card skin options; 6 border radius options
- [ ] Selecting any option immediately updates the corresponding `data-*` attribute on `document.documentElement` (optimistic UI, no page reload)
- [ ] "Save for Organization" button POSTs the current selections to the settings API and shows a success toast
- [ ] "Reset to defaults" link reverts all attributes and clears saved org overrides
- [ ] `npx tsc --noEmit` passes

---

### US-010: Embed ThemeCustomizer in branding settings page
**Priority:** 10
**Description:** As an admin, I want the theme customizer available inline on the branding settings page alongside a live dashboard mockup preview.

**Acceptance Criteria:**
- [ ] `resources/js/pages/settings/branding.tsx` updated to embed `ThemeCustomizer` as an inline panel (not floating)
- [ ] Existing branding fields remain unchanged and functional
- [ ] Theme changes apply the same live preview as the floating version
- [ ] `npx tsc --noEmit` passes

---

### US-011: Install npm component dependencies
**Priority:** 11
**Description:** As a developer, I need all required npm packages installed so complex UI components can be built without low-level implementations.

**Acceptance Criteria:**
- [ ] Installed (confirmed NOT in package.json): `react-hook-form`, `@hookform/resolvers`, `zod`, `@dnd-kit/core`, `@dnd-kit/sortable`, `@dnd-kit/utilities`
- [ ] Installed: `embla-carousel-react`, `@tiptap/react`, `@tiptap/starter-kit`, `@tiptap/extension-link`, `@tiptap/extension-image`, `@tiptap/extension-code-block-lowlight`, `lowlight`, `novel`, `react-dropzone`, `react-resizable-panels`, `react-colorful`, `react-textarea-autosize`, `@formkit/auto-animate`, `react-syntax-highlighter`, `@types/react-syntax-highlighter`, `assistant-ui`
- [ ] Installed: `qrcode.react`, `react-signature-canvas`, `@types/react-signature-canvas`, `react-diff-viewer-continued`, `react-pdf`, `react-compare-slider`
- [ ] `npm run build` passes after all installs
- [ ] `npx tsc --noEmit` passes

---

### US-012: Pull shadcn registry components
**Priority:** 12
**Description:** As a developer, I need the shadcn registry components installed so complex components don't require low-level implementations from scratch.

**Acceptance Criteria:**
- [ ] All registry components successfully pulled via `npx shadcn@latest add [name]`: `shadcn-stepper`, `shadcn-tree-view`, `shadcn-timeline`, `shadcn-phone-input`, `shadcn-multi-select-component`, `emblor`, `date-range-picker-for-shadcn`, `date-time-picker-shadcn`, `auto-form`, `shadcn-number-scrubber`, `shadcn-country-dropdown`, `shadcn-color-picker`, `shadcn-image-cropper`, `image-upload-shadcn`, `file-uploader`, `carouselcn`, `shadcn-event-calendar`, `shadcn-calendar-heatmap`, `roadmap-ui`, `audio/ui`, `shadcn-chat`, `confirm-dialog`, `credenza`, `progress-button`, `fancy-switch`, `animated-tabs`, `react-dnd-kit-tailwind-shadcn-ui`, `sortable`, `shadcn-spinner`, `shadcn-cookies`, `mindmapcn`
- [ ] `npm run build` passes
- [ ] `npx tsc --noEmit` passes

---

### US-013: Enhance existing ui/ components with new variants
**Priority:** 13
**Description:** As a developer, I want existing ui/ components enhanced with new variants so the design system is more expressive without breaking any existing usage.

**Acceptance Criteria:**
- [ ] `button.tsx`: adds `filled`/`soft`/`flat` variants; `color` prop (7 semantic colors); `isLoading` prop; `leftIcon`/`rightIcon` props
- [ ] `badge.tsx`: adds `soft`/`outlined`/`filled` + semantic colors + `glow` prop
- [ ] `card.tsx`: adds `skin` prop (`shadow`/`bordered`/`flat`/`elevated`); `hoverable` bool; respects `data-card-skin`
- [ ] `input.tsx`: adds `variant` (`outlined`/`filled`/`soft`); `size` (`xs`/`sm`/`md`/`lg`); `startContent`/`endContent` slots
- [ ] `textarea.tsx`: wraps `react-textarea-autosize`; same variants as input
- [ ] `alert.tsx`: adds `soft`/`outlined`/`filled` + semantic colors
- [ ] `tabs.tsx`: adds `variant` (`underline`/`pill`/`card`/`lifted`)
- [ ] `avatar.tsx`: adds `indicator` slot (status dot); exports `AvatarGroup`; auto-color from name
- [ ] `skeleton.tsx`: adds `shimmer`/`wave`/`pulse` animation variants
- [ ] `tooltip.tsx`: adds `rich` variant (title + description + arrow)
- [ ] `dialog.tsx`: adds `size` prop (`xs`/`sm`/`md`/`lg`/`xl`/`fullscreen`)
- [ ] `sheet.tsx`: adds `size` prop (`quarter`/`half`/`full`); optional backdrop blur
- [ ] All existing prop interfaces unchanged (backward-compatible, additions only)
- [ ] `npx tsc --noEmit` passes

---

### US-014: Accessibility infrastructure
**Priority:** 14
**Description:** As a developer, I need accessibility utilities baked into the component system so WCAG compliance is easy by default rather than an afterthought.

**Acceptance Criteria:**
- [ ] `resources/js/components/ui/skip-to-content.tsx` created: renders a visually-hidden link at the top of every layout that becomes visible on focus and jumps to `#main-content`; must be included in all app shell templates (US-025)
- [ ] `resources/js/hooks/use-focus-trap.ts` created: custom hook that traps keyboard focus within a given ref container (for custom overlays that don't use Radix); exported from `resources/js/hooks/index.ts`
- [ ] `resources/js/hooks/use-reduced-motion.ts` created: returns `boolean` from `matchMedia('(prefers-reduced-motion: reduce)')`; all animated components (`skeleton.tsx`, `feed.tsx`, `streaming-text.tsx`, etc.) must consult this hook and disable/reduce animations when `true`
- [ ] All three utilities have TypeScript types
- [ ] `npx tsc --noEmit` passes

---

### US-015: Keyboard shortcut system
**Priority:** 15
**Description:** As a developer, I need a central keyboard shortcut registry so app-wide shortcuts are registered, discoverable, and documented consistently.

**Acceptance Criteria:**
- [ ] `resources/js/lib/keyboard-shortcuts.ts` created: exports `registerShortcut({ keys, description, action, scope? })`, `unregisterShortcut(keys)`, `getShortcuts()`, and a `useKeyboardShortcut(keys, handler)` hook
- [ ] Shortcuts registered globally (e.g. `Cmd+K` for command bar, `?` for help) in the app root, cleaned up on unmount
- [ ] `resources/js/components/ui/keyboard-shortcut-display.tsx` created: a Sheet/Dialog listing all registered shortcuts grouped by scope, triggered by pressing `?` or via a help button; shows keys as `<Kbd>` components
- [ ] `npx tsc --noEmit` passes

---

### US-016: Layout shell templates
**Priority:** 16
**Description:** As a developer, I need pre-built page shell compositions so I can assemble new pages from established layout patterns rather than composing primitives from scratch every time.

**Acceptance Criteria:**
- [ ] Created in `resources/js/components/shells/`:
  - `app-shell.tsx` — sidebar (collapsible) + top header + main content area + optional right panel slot; includes `SkipToContent` and `id="main-content"` on `<main>`
  - `master-detail.tsx` — left list panel + right detail panel (responsive: stacks on mobile, side-by-side on desktop); uses `react-resizable-panels` for desktop split
  - `split-view.tsx` — horizontal or vertical split of two arbitrary content areas with resizable divider
  - `marketing-layout.tsx` — centered max-width content with optional top nav and footer slots; for auth/landing pages
  - `dashboard-layout.tsx` — grid of slots: header stat cards row + main chart area + sidebar widget area
- [ ] All shells accept `className` and children slot props
- [ ] All shells include `<SkipToContent />` from US-014
- [ ] `npx tsc --noEmit` passes

---

### US-017: New layout and navigation ui/ components
**Priority:** 17
**Description:** As a developer, I need layout primitives and navigation components so complex layouts can be built consistently.

**Acceptance Criteria:**
- [ ] Created in `resources/js/components/ui/`: `box.tsx`, `container.tsx`, `stack.tsx` (VStack + HStack), `grid.tsx`, `divider.tsx`, `scroll-shadow.tsx`, `masonry.tsx`, `resizable.tsx`
- [ ] Created: `stepper.tsx`, `pagination.tsx`, `animated-tabs.tsx`, `bottom-nav.tsx`, `tree-nav.tsx`, `collapsible-search.tsx`, `toc.tsx`
- [ ] `mode-toggle.tsx` (from US-006) included here if not already created
- [ ] `npx tsc --noEmit` passes

---

### US-018: New button/action ui/ components
**Priority:** 18
**Description:** As a developer, I need specialized action components for FABs, copy buttons, and progress patterns.

**Acceptance Criteria:**
- [ ] Created in `resources/js/components/ui/`: `icon-button.tsx`, `button-group.tsx`, `fab.tsx` (optional speed-dial), `split-button.tsx`, `copy-button.tsx` (clipboard + animated tick), `progress-button.tsx`, `swap.tsx`
- [ ] `npx tsc --noEmit` passes

---

### US-019: New form ui/ components
**Priority:** 19
**Description:** As a developer, I need a complete set of typed form components covering all input types, including auto-save indicator, translatable fields, and timezone selector.

**Acceptance Criteria:**
- [ ] Created in `resources/js/components/ui/`: `radio.tsx`, `radio-group.tsx`, `checkbox-group.tsx`, `multi-select.tsx`, `tags-input.tsx`, `tag.tsx`, `date-range-picker.tsx`, `datetime-picker.tsx`, `phone-input.tsx`, `color-picker.tsx`, `file-dropzone.tsx`, `image-upload.tsx`, `image-cropper.tsx`, `number-input.tsx`, `number-scrubber.tsx`, `password-input.tsx`, `search-input.tsx`, `country-select.tsx`, `rich-text-editor.tsx` (Tiptap), `novel-editor.tsx` (novel + Laravel AI SDK), `auto-form.tsx`, `fancy-switch.tsx`, `rating.tsx`, `range-slider.tsx`, `listbox.tsx`, `combobox.tsx`, `input-group.tsx`, `form-field.tsx`, `form-section.tsx`, `contextual-help.tsx`
- [ ] `autosave-indicator.tsx` created: shows `Saving...` / `Saved ✓` / `Error` status badge; accepts `status: 'idle' | 'saving' | 'saved' | 'error'` prop; intended for use with debounced form auto-save patterns
- [ ] `translatable-field.tsx` created: wraps any input with a tab row of configured locale codes (e.g. `en`, `fr`, `ar`); each tab shows the input for that locale; value is `Record<string, string>`; locale list configurable via prop
- [ ] `timezone-select.tsx` created: searchable combobox backed by the IANA timezone list; displays current UTC offset alongside each option; groups by region
- [ ] `novel-editor.tsx` connects AI suggestions to Laravel AI SDK backend via configurable `aiEndpoint` prop (default `/ai/complete`)
- [ ] `npx tsc --noEmit` passes

---

### US-020: New data display ui/ components
**Priority:** 20
**Description:** As a developer, I need data display components for timelines, kanban, calendars, feeds, media, and developer-oriented views.

**Acceptance Criteria:**
- [ ] Created in `resources/js/components/ui/`: `timeline.tsx`, `tree.tsx`, `kanban.tsx`, `sortable-list.tsx`, `carousel.tsx`, `event-calendar.tsx`, `calendar-heatmap.tsx`, `roadmap.tsx`, `mind-map.tsx`, `audio-player.tsx`, `gallery.tsx`, `stat-card.tsx`, `list.tsx` + `list-item.tsx`, `description-list.tsx`, `feed.tsx` + `feed-item.tsx`, `file-item.tsx` + `file-item-square.tsx`, `kbd.tsx`, `highlight.tsx`
- [ ] `virtual-list.tsx` created: standalone infinite-scroll / virtualized list component using `@tanstack/react-virtual`; accepts `items`, `renderItem`, `fetchNextPage`, `hasNextPage`, `isFetchingNextPage` props; shows a skeleton loader row while fetching
- [ ] `json-viewer.tsx` created: collapsible, syntax-highlighted JSON tree; accepts any `value` prop; nodes are collapsible; strings/numbers/booleans/nulls rendered with distinct colors matching the active theme; copy-to-clipboard button on hover
- [ ] `diff-viewer.tsx` created: side-by-side (desktop) or unified (mobile) diff display using `react-diff-viewer-continued`; accepts `oldValue`, `newValue`, `language` props; syntax highlighted; theme-aware
- [ ] `pdf-viewer.tsx` created: embedded PDF display using `react-pdf`; shows page count, prev/next page controls, zoom in/out; accepts `url` or `file` prop; shows skeleton while loading
- [ ] `video-player.tsx` created: HTML5 `<video>` wrapper with custom controls (play/pause, scrubber, volume, fullscreen, speed selector); accepts `src`, `poster`, `autoPlay`, `loop` props; respects `useReducedMotion` for autoPlay
- [ ] `image-comparison.tsx` created: before/after drag-to-reveal slider using `react-compare-slider`; accepts `before`, `after` (img src or ReactNode) and `orientation` (horizontal/vertical) props
- [ ] `qr-code.tsx` created: renders a QR code using `qrcode.react`; accepts `value`, `size`, `level` (error correction), `includeMargin` props; includes a download-as-PNG button
- [ ] `signature-pad.tsx` created: canvas-based signature capture using `react-signature-canvas`; accepts `onSave(dataUrl)`, `onClear` props; shows "Clear" and "Save" buttons
- [ ] `npx tsc --noEmit` passes

---

### US-021: New feedback ui/ components
**Priority:** 21
**Description:** As a developer, I need feedback and state components for async flows, empty screens, and loading states.

**Acceptance Criteria:**
- [ ] Created in `resources/js/components/ui/`: `progress.tsx`, `progress-circle.tsx`, `spinner.tsx`, `empty-state.tsx`, `error-state.tsx`, `loading-state.tsx`, `upload-progress.tsx`, `splash-screen.tsx`, `loadable.tsx`
- [ ] `npx tsc --noEmit` passes

---

### US-022: New overlay ui/ components
**Priority:** 22
**Description:** As a developer, I need overlay components for common interaction patterns including confirm dialogs, responsive modals, lightbox, and context menus.

**Acceptance Criteria:**
- [ ] Created in `resources/js/components/ui/`: `confirm-dialog.tsx`, `responsive-modal.tsx` (credenza), `lightbox.tsx`, `context-menu.tsx`
- [ ] `npx tsc --noEmit` passes

---

### US-023: SaaS feature components
**Priority:** 23
**Description:** As a developer building SaaS features, I need billing-related, activation, and system status UI components that are common across every SaaS application so I don't build them from scratch.

**Acceptance Criteria:**
- [ ] Created in `resources/js/components/saas/`:
  - `trial-banner.tsx` — full-width top banner: "Your trial ends in N days. Upgrade now →"; accepts `daysRemaining`, `onUpgrade` props; hidden when `daysRemaining` is null; dismissible with auto-hide after dismiss stored in `localStorage`
  - `feature-gate.tsx` — wrapper component: renders `children` if `hasAccess` prop is `true`; otherwise renders a centered upgrade prompt card with title, description, and a CTA button; accepts `feature`, `title`, `description`, `ctaLabel`, `onUpgrade` props
  - `usage-meter.tsx` — progress bar + label showing current/max usage (e.g. "8/10 seats used"); accepts `used`, `limit`, `label`, `unit` props; color shifts to warning at 80%, error at 100%
  - `onboarding-checklist.tsx` — floating or inline checklist of setup tasks with checkboxes, progress bar, and completion percentage; accepts `steps: { id, label, completed, href? }[]` prop; collapses when all steps are done
  - `whats-new-modal.tsx` — Dialog shown automatically on first login after a new deploy; accepts `version`, `items: { title, description, badge? }[]`; "Got it" button dismisses and stores seen version in `localStorage`
  - `impersonation-banner.tsx` — sticky top bar rendered when `usePage().props.auth.impersonating` is true; shows "You are viewing as [User Name]" with a "Stop Impersonating" button that calls the existing impersonation exit route
  - `maintenance-banner.tsx` — dismissible top banner for scheduled maintenance notices; accepts `message`, `scheduledAt?` props; reads from Inertia shared props key `maintenance`
  - `setup-wizard.tsx` — multi-step full-page onboarding shell for new organizations; uses `Stepper` + `Card` + step slot props; tracks current step in URL param; shows completion progress
- [ ] `npx tsc --noEmit` passes

---

### US-024: Admin power-user components
**Priority:** 24
**Description:** As a developer building admin features, I need ready-made UI components for API key management, session management, permission matrices, audit logs, CSV import, and webhook configuration.

**Acceptance Criteria:**
- [ ] Created in `resources/js/components/admin/`:
  - `api-key-manager.tsx` — table of API keys with name, last-used, created-at columns; inline "Copy" button (shows key once on creation, then masked); "Revoke" confirm dialog; "Create New Key" button with name input; accepts `keys`, `onCreate`, `onRevoke` props
  - `session-manager.tsx` — list of active sessions with device icon, IP address, last-active timestamp, current session badge; "Revoke" button per session and "Revoke All Other Sessions" bulk action; accepts `sessions`, `onRevoke`, `onRevokeAll` props
  - `permission-matrix.tsx` — visual grid: rows = roles, columns = permissions grouped by resource; each cell is a checkbox; read-only or editable mode via `readonly` prop; accepts `roles`, `permissions`, `grants`, `onChange` props
  - `audit-log-viewer.tsx` — composes `Timeline` + date/type filters + user filter + search; each entry shows actor avatar, action verb, target resource, timestamp; accepts `entries`, `filters`, `onFilterChange` props; virtualized via `VirtualList`
  - `import-wizard.tsx` — 4-step Sheet/Dialog: (1) upload CSV/XLSX via `FileDropzone`; (2) column mapping UI (detected columns → target fields dropdown per column); (3) preview of first 10 rows with validation errors highlighted; (4) import progress with success/error counts; accepts `targetFields`, `onImport` props
  - `webhook-config.tsx` — form for webhook endpoint URL + event checkboxes grouped by resource; "Test Webhook" button that sends a ping; shows last delivery status; accepts `events`, `value`, `onChange`, `onTest` props
- [ ] `npx tsc --noEmit` passes

---

### US-025: Global search
**Priority:** 25
**Description:** As a user, I want a fast, keyboard-driven global search that queries the existing Typesense/Scout backend and returns grouped results across multiple resource types.

**Acceptance Criteria:**
- [ ] `resources/js/components/ui/global-search.tsx` created: a full-screen modal (triggered by `Cmd+K` via keyboard shortcut registry) with a text input, result groups (e.g. Users, Organizations, Records), keyboard navigation (arrow keys + Enter), and recent searches list when empty
- [ ] Results are fetched from a backend route `/search?q=...` that routes through Laravel Scout/Typesense; the route returns grouped results with `type`, `id`, `title`, `subtitle`, `url` per item
- [ ] Backend: a `GlobalSearchController` (or action) created that queries Scout across configured models and returns the JSON structure expected by the frontend component
- [ ] `Cmd+K` (and `Ctrl+K` on Windows) registered in the keyboard shortcut registry (US-015) and opens the global search modal
- [ ] Empty state shows recent searches (stored in `localStorage`, max 10)
- [ ] Result items are navigable via Inertia `router.visit()` when clicked or when Enter is pressed
- [ ] `vendor/bin/pint --dirty --format agent` passes
- [ ] `npx tsc --noEmit` passes

---

### US-026: Chart components
**Priority:** 26
**Description:** As a developer, I need clean typed recharts wrappers for all common chart types.

**Acceptance Criteria:**
- [ ] Created in `resources/js/components/charts/`: `area-chart.tsx`, `bar-chart.tsx`, `line-chart.tsx`, `pie-chart.tsx`, `scatter-chart.tsx`, `radar-chart.tsx`, `sparkline.tsx` (inline 40px), `gauge-chart.tsx`, `heatmap-chart.tsx`, `funnel-chart.tsx`, `treemap-chart.tsx`, `progress-ring.tsx`
- [ ] All charts: responsive container, dark-mode via CSS vars, primary color theming, skeleton state prop, `useReducedMotion` disables entry animations when true
- [ ] `npx tsc --noEmit` passes

---

### US-027: Map components
**Priority:** 27
**Description:** As a developer, I need MapLibre/mapcn map wrappers for common geospatial visualizations that work without an API key.

**Acceptance Criteria:**
- [ ] mapcn installed via `npx shadcn@latest add @mapcn/map`
- [ ] Created in `resources/js/components/maps/`: `base-map.tsx`, `markers-map.tsx`, `clusters-map.tsx`, `routes-map.tsx`, `analytics-map.tsx`, `tracking-map.tsx`, `location-picker.tsx`
- [ ] All maps render with mock data using OpenFreeMap tiles (no API key required)
- [ ] `npx tsc --noEmit` passes

---

### US-028: AI components
**Priority:** 28
**Description:** As a developer, I need AI-specific UI components for chat, streaming, agent status, and tool calls connected to the Laravel AI SDK backend.

**Acceptance Criteria:**
- [ ] Created in `resources/js/components/ai/`: `AssistantThread`, `AssistantModal`, `AssistantSidebar`, `AssistantRuntimeProvider` (connects to Laravel AI SDK backend, not mocked)
- [ ] Created custom: `streaming-text.tsx`, `thinking-indicator.tsx` (3 variants), `tool-call-card.tsx`, `ai-response-card.tsx`, `markdown-response.tsx`, `code-block.tsx`, `confidence-score.tsx`, `ai-insight-card.tsx`, `entity-highlight.tsx`, `ai-summary-card.tsx`, `prediction-widget.tsx`, `anomaly-alert.tsx`, `model-selector.tsx`, `prompt-input.tsx`, `voice-input.tsx`, `token-usage.tsx`, `agent-status.tsx`, `context-drawer.tsx`
- [ ] All animated components (`streaming-text`, `thinking-indicator`) consult `useReducedMotion` and disable/reduce animations when true
- [ ] `npx tsc --noEmit` passes

---

### US-029: DataTable enhancements
**Priority:** 29
**Description:** As a developer, I need the existing DataTable enhanced with grouping, expandable rows, column pinning, virtual scrolling, bulk actions, and CSV export.

**Acceptance Criteria:**
- [ ] DataTable supports TanStack row grouping, expandable rows, and column pinning APIs
- [ ] Virtual scrolling via `@tanstack/react-virtual` (already installed)
- [ ] Bulk action toolbar appears when rows are selected
- [ ] CSV export button available
- [ ] New cell types: `CopyableCell`, `HighlightableCell`
- [ ] `ItemViewTypeSelect` toggle (grid/list/table)
- [ ] Additional filters: `RangeFilter`, `RadioFilter`, `DateFilter`
- [ ] Table density toggle: Compact / Comfortable / Spacious (applies row padding via data attribute)
- [ ] Column visibility toggle: show/hide individual columns via a popover menu
- [ ] `npx tsc --noEmit` passes

---

### US-030: Composed components
**Priority:** 30
**Description:** As a developer, I need high-level composed components for complex pages (dashboards, file managers, command bars).

**Acceptance Criteria:**
- [ ] Created in `resources/js/components/composed/`: `data-view.tsx`, `form-wizard.tsx`, `command-bar.tsx` (cmdk + AI prompt + registered shortcuts), `notification-center.tsx`, `file-manager.tsx`, `metric-dashboard.tsx`, `activity-log.tsx`, `user-card.tsx`, `pricing-card.tsx`, `kanban-board.tsx`, `location-dashboard.tsx`, `right-sidebar.tsx`
- [ ] `command-bar.tsx` is wired to the keyboard shortcut registry (US-015) and lists registered shortcuts in a dedicated section
- [ ] `npx tsc --noEmit` passes

---

### US-031: In-app component showcase page
**Priority:** 31
**Description:** As a developer or designer, I want an in-app kitchen-sink page that renders all components with the real app theme so I can visually review the full library, test theme switching live, and share it with clients or stakeholders without needing Storybook running.

**Acceptance Criteria:**
- [ ] A new Inertia page created at `resources/js/pages/dev/components.tsx` rendered by a `ComponentShowcaseController`
- [ ] Route registered at `/dev/components` (GET), named `dev.components`; the route is gated by a Pennant feature flag `component-showcase` (default: enabled in local/staging environments, disabled in production)
- [ ] The page uses `app-shell.tsx` (from US-016) with a sticky left sidebar nav listing all component categories; clicking a category smooth-scrolls to that section
- [ ] Component categories and their sections rendered on the page:
  - **Foundation** — color swatches for all CSS vars (semantic + Tailux surface colors), typography scale, spacing scale, shadow tokens
  - **Layout** — Box, Container, Stack (H+V), Grid, Divider, ScrollShadow, Masonry, Resizable
  - **Shells** — AppShell, MasterDetail, SplitView, MarketingLayout, DashboardLayout (each shown with labelled placeholder slots)
  - **Navigation** — Stepper, Pagination, AnimatedTabs, BottomNav, TreeNav, CollapsibleSearch, TOC, Breadcrumbs
  - **Buttons & Actions** — all Button variants + colors, IconButton, ButtonGroup, FAB, SplitButton, CopyButton, ProgressButton, Swap
  - **Forms** — every form component with a working controlled example (value state managed locally); AutosaveIndicator shown in saving/saved/error states; TranslatableField shown with en/fr/ar tabs; TimezoneSelect
  - **Data Display** — Timeline, Tree, Kanban, SortableList, Carousel, EventCalendar, CalendarHeatmap, Roadmap, MindMap, AudioPlayer, Gallery, StatCard, List, DescriptionList, Feed, FileItem, Kbd, Highlight, VirtualList (with 1000 mock items), JsonViewer, DiffViewer, PdfViewer, VideoPlayer, ImageComparison, QrCode, SignaturePad
  - **Feedback** — Progress (all states), ProgressCircle, Spinner (all variants), EmptyState, ErrorState, LoadingState, UploadProgress, SplashScreen
  - **Overlay** — ConfirmDialog, ResponsiveModal, Lightbox, ContextMenu (each triggered by a demo button)
  - **Charts** — all 12 chart types with realistic mock datasets
  - **Maps** — all 7 map variants with mock coordinate data
  - **AI** — AssistantThread (mock messages), StreamingText (animated demo), ThinkingIndicator (3 variants), ToolCallCard, AiResponseCard, CodeBlock, ConfidenceScore, AgentStatus, TokenUsage
  - **SaaS** — TrialBanner (daysRemaining=5), FeatureGate (hasAccess=false), UsageMeter, OnboardingChecklist (2/5 complete), WhatsNewModal (trigger button), ImpersonationBanner (mock), MaintenanceBanner, SetupWizard (step 1 of 4)
  - **Admin** — ApiKeyManager (mock keys), SessionManager (mock sessions), PermissionMatrix (mock roles/permissions), AuditLogViewer (mock entries), ImportWizard (trigger button), WebhookConfig
  - **Composed** — DataView, FormWizard, CommandBar (trigger button), NotificationCenter, FileManager, MetricDashboard, ActivityLog, UserCard, PricingCard, KanbanBoard, LocationDashboard, RightSidebar
  - **Accessibility** — SkipToContent (shown as visible, not hidden), FocusTrap demo, ReducedMotion toggle demo
- [ ] `ThemeCustomizer` floating button is always visible on this page regardless of `canCustomize` (hardcoded `true` for the showcase route) so theme switching can be tested live
- [ ] `KeyboardShortcutDisplay` help modal (press `?`) works on this page showing all registered shortcuts
- [ ] A sticky top bar on the page shows the current active theme dimensions (dark palette name, primary color, radius, skin) updating live as the customizer changes them
- [ ] The route is excluded from `php artisan route:list` output in production (handled by the Pennant gate or a `App::isLocal()` check on the route registration)
- [ ] `vendor/bin/pint --dirty --format agent` passes
- [ ] `npx tsc --noEmit` passes

---

### US-032: Storybook stories for all components
**Priority:** 32
**Description:** As a developer, I need a Storybook story for every component so the full library is documented and browsable without running the Laravel backend.

**Acceptance Criteria:**
- [ ] Stories directory structure includes: `foundation/`, `layout/`, `shells/`, `navigation/`, `forms/`, `data-display/`, `feedback/`, `overlay/`, `charts/`, `maps/`, `ai/`, `saas/`, `admin/`, `ThemeSwitcher/`, `composed/`
- [ ] `Foundation/` stories: Colors, Typography, Icons, Tailux Tokens, Theme Presets, Theme Preview
- [ ] Every component has at least one CSF3 story with `argTypes`, `autodocs`, and realistic mock data
- [ ] `saas/` stories: each SaaS component with realistic props (e.g. `trial-banner` with `daysRemaining=5`, `feature-gate` in both `hasAccess=true` and `hasAccess=false` states)
- [ ] `admin/` stories: each admin component with mock data
- [ ] `shells/` stories: each shell layout shown with placeholder content inside each slot
- [ ] All stories respect the Storybook theme toolbar (dark/light, palette, radius, skin)
- [ ] `ThemeCustomizer` story renders with `canCustomize: true` (mocked)
- [ ] `GlobalSearch` story uses mock search results (no real backend call)
- [ ] `npm run storybook` runs all stories with 0 errors

---

### US-033: ThemeSettings backend — sidebar layout, font, menu color, menu accent
**Priority:** 33
**Description:** As an admin, I need additional theme dimensions (sidebar layout variant, font choice, menu background color, and menu accent style) stored in the database so the full visual identity of the app can be configured per organization.

**Acceptance Criteria:**
- [ ] `app/Settings/ThemeSettings.php` extended with 4 new fields: `sidebar_layout` (default: `'main'`, options: `main|sideblock`), `font` (default: `'inter'`, options: `inter|geist|poppins|outfit|plus-jakarta-sans`), `menu_color` (default: `'default'`, options: `default|primary|muted`), `menu_accent` (default: `'subtle'`, options: `subtle|strong|bordered`)
- [ ] New settings migration in `database/settings/` for the 4 new fields
- [ ] `php artisan migrate` completes successfully
- [ ] All 4 fields added to `SettingsOverlayServiceProvider::OVERLAY_MAP` with `orgOverridable: true`
- [ ] `HandleInertiaRequests` `share()` updated — `theme` key now includes `layout`, `font`, `menuColor`, `menuAccent` alongside existing fields
- [ ] `theme-from-props.tsx` extended to apply `data-sidebar-layout`, `data-font` (already exists — verify), `data-menu-color`, `data-menu-accent` on `document.documentElement`
- [ ] `resources/css/themes.css` extended with CSS blocks for each new dimension: `[data-sidebar-layout="sideblock"]`, `[data-menu-color="primary|muted"]`, `[data-menu-accent="strong|bordered"]`
- [ ] Frontend types updated in `resources/js/types/index.d.ts`
- [ ] `php artisan settings:cache` completes successfully
- [ ] `vendor/bin/pint --dirty --format agent` passes
- [ ] `npx tsc --noEmit` passes

---

### US-034: ThemeCustomizer UX — compact panel, Try Random, all dimensions
**Priority:** 34
**Description:** As an admin, I want the ThemeCustomizer panel to match the UX quality of shadcn/ui create — a compact property list where each row shows the current value and expands inline, plus a "Try Random" shuffle button and the ability to export/import the theme as JSON.

**Acceptance Criteria:**
- [ ] `theme-customizer.tsx` redesigned as a **compact vertical list panel** (inspired by shadcn/ui create's right panel): each theme dimension is a single row showing `Property Name / Current Value + icon`; clicking a row expands an inline option picker below it; only one row expanded at a time
- [ ] Panel sections cover all theme dimensions in this order:
  1. **Theme Mode** — System / Light / Dark (visual mini-card thumbnails as per Tailux screenshot)
  2. **Preset** — named preset pills (≥6 presets from `THEME_PRESETS`); selecting one sets all dimensions and collapses the preset row
  3. **Layout** — Main Layout / Sideblock (visual thumbnail cards showing sidebar position, as per Tailux)
  4. **Style / Dark Palette** — 5 dark theme swatches (Navy/Mirage/Mint/Black/Cinder)
  5. **Primary Color** — 6 color diamonds
  6. **Base Color / Light Scheme** — Slate / Gray / Neutral mini thumbnails
  7. **Font** — font name pills (Inter, Geist, Poppins, Outfit, Plus Jakarta Sans); each rendered in its own typeface
  8. **Menu Color** — Default / Primary / Muted
  9. **Menu Accent** — Subtle / Strong / Bordered
  10. **Card Skin** — Shadow / Bordered
  11. **Radius** — None / SM / Default / MD / LG / Full (rendered as actual rounded boxes)
- [ ] **"Try Random" button** at the top of the panel (keyboard shortcut `R` while panel is open): randomizes all theme dimensions to a valid random combination and applies them immediately
- [ ] **"Export theme"** button: copies the current 9 theme dimensions as a JSON blob to the clipboard; shows "Copied!" toast
- [ ] **"Import theme"** action: a small textarea that appears when clicking "Import"; pasting valid theme JSON and clicking "Apply" applies all dimensions at once; invalid JSON shows an inline error
- [ ] **"Reset to defaults"** link at the bottom reverts all attributes to system defaults and clears saved org overrides
- [ ] **"Save for Organization"** button POSTs all dimensions (including new ones from US-033) to the settings API
- [ ] All dimension changes still apply immediately via `data-*` attributes on `document.documentElement` (optimistic UI, no page reload)
- [ ] Hover over a preset card or color swatch shows a live preview instantly (mouse-enter applies the change; mouse-leave reverts to current saved state; click commits the selection)
- [ ] `npx tsc --noEmit` passes

---

### US-035: Hooks library
**Priority:** 35
**Description:** As a developer, I need a collection of commonly-needed React hooks available in `resources/js/hooks/` so I don't reinvent them in every component.

**Acceptance Criteria:**
- [ ] `resources/js/hooks/` directory created with an `index.ts` barrel file that re-exports all hooks
- [ ] The following hooks implemented with full TypeScript types and JSDoc:
  - `useDebounce<T>(value: T, delay: number): T` — returns a debounced version of the value; updates after `delay` ms of no changes
  - `useLocalStorage<T>(key: string, initialValue: T): [T, Setter, () => void]` — synced localStorage state; third return value clears the key
  - `useMediaQuery(query: string): boolean` — e.g. `useMediaQuery('(min-width: 768px)')`; reactive to viewport changes
  - `useIntersectionObserver(ref, options?): IntersectionObserverEntry | null` — triggers when the element enters/exits the viewport
  - `useClickOutside(ref, handler)` — calls `handler` when a click occurs outside the given ref
  - `useEventListener(eventName, handler, element?)` — typed `addEventListener`/`removeEventListener` with cleanup
  - `usePrevious<T>(value: T): T | undefined` — returns the previous render's value
  - `useWindowSize(): { width: number; height: number }` — reactive window dimensions
  - `useCopyToClipboard(): [boolean, (text: string) => void]` — returns `[copied, copy]`; `copied` is `true` for 2 seconds after copying
  - `useOnline(): boolean` — returns `true` when browser has network access; reactive to `online`/`offline` events
  - `useToggle(initial?: boolean): [boolean, () => void, (v: boolean) => void]` — boolean toggle with optional force-set
  - `useInterval(callback, delay: number | null)` — safe `setInterval` with cleanup; pass `null` to pause
- [ ] `useReducedMotion` (already created in US-014) moved here and re-exported from `hooks/index.ts`
- [ ] `useFocusTrap` (already created in US-014) moved here and re-exported from `hooks/index.ts`
- [ ] `useKeyboardShortcut` (from US-015) re-exported from `hooks/index.ts`
- [ ] `npx tsc --noEmit` passes

---

### US-036: Utilities library, Error Boundary, and Offline banner
**Priority:** 36
**Description:** As a developer, I need a shared utility function library, a React Error Boundary component with a custom fallback, and an offline detection banner so these foundational patterns don't get reinvented per feature.

**Acceptance Criteria:**
- [ ] `resources/js/lib/utils/` directory created (separate from existing `resources/js/lib/utils.ts` which holds `cn()` — do not break it); new utilities in separate files re-exported from `resources/js/lib/utils/index.ts`:
  - `formatDate(date: Date | string, format?: string): string` — locale-aware; default format is `MMM D, YYYY`; wraps `date-fns` (already installed) with a sensible API
  - `formatRelativeTime(date: Date | string): string` — e.g. `"2 hours ago"`, `"just now"`, `"in 3 days"`; used by Feed, AuditLog, Timeline
  - `formatCurrency(amount: number, currency?: string, locale?: string): string` — e.g. `"$1,234.56"`; defaults to USD + user's browser locale
  - `formatNumber(n: number, options?: Intl.NumberFormatOptions): string` — compact notation option: `1200` → `"1.2K"`, `3_400_000` → `"3.4M"`
  - `formatBytes(bytes: number, decimals?: number): string` — `2_400_000` → `"2.4 MB"`
  - `truncate(str: string, length: number, suffix?: string): string` — appends `"…"` by default
  - `slugify(str: string): string` — `"Hello World!"` → `"hello-world"`
  - `initials(name: string, maxChars?: number): string` — `"John Doe"` → `"JD"`; used by Avatar auto-color
  - `groupBy<T>(array: T[], key: keyof T): Record<string, T[]>` — groups an array by a key
  - `clamp(value: number, min: number, max: number): number`
  - `randomItem<T>(array: T[]): T` — picks a random element; used by ThemeCustomizer "Try Random"
  - `randomTheme(): ThemeConfig` — picks a random valid combination of all theme dimensions from `tailux-themes.ts` constants; used by "Try Random" in ThemeCustomizer
- [ ] `resources/js/components/ui/error-boundary.tsx` created: a class-based React Error Boundary (required by React for this pattern); accepts `fallback` prop (ReactNode or render-prop `(error, reset) => ReactNode`); default fallback renders an `ErrorState` component with a "Try again" button that calls `reset()`; wraps `children`; catches render errors and prevents full-page crash
- [ ] `resources/js/components/ui/offline-banner.tsx` created: uses `useOnline()` hook; renders a fixed top banner "You're offline. Some features may be unavailable." when offline; banner slides in and out with a smooth transition (respects `useReducedMotion`); auto-dismisses 3 seconds after connection is restored; manual close button available
- [ ] All utility functions have JSDoc with input/output examples
- [ ] `npx tsc --noEmit` passes

---

## Functional Requirements

- FR-1: `tailux.css` must define all design tokens using `@theme` blocks compatible with Tailwind CSS v4 syntax
- FR-2: Dark theme, primary color, light scheme, card skin, border radius, sidebar layout, font, menu color, and menu accent stored per-org in `ThemeSettings`, all `orgOverridable: true`
- FR-3: Named theme presets (≥6) defined in `tailux-themes.ts`; each preset covers all 9 theme dimensions; applying a preset sets all dimensions at once
- FR-4: A system-wide `allow_user_theme_customization` boolean (default: `false`) controls whether non-admin users see the `ThemeCustomizer`; org admins always see it
- FR-5: `canCustomize` must be computed server-side in `HandleInertiaRequests`, never client-side
- FR-6: Per-user `theme_mode` (`dark`/`light`/`system`) stored in DB; applied on page load by `theme-from-props.tsx`; `system` mode follows `prefers-color-scheme` reactively
- FR-7: `ThemeCustomizer` renders nothing when `canCustomize` is `false`
- FR-8: All new component variants must be additive; no breaking changes to existing prop interfaces
- FR-9: All components respond to all theme `data-*` attributes including `data-sidebar-layout`, `data-menu-color`, `data-menu-accent`
- FR-10: All animated components consult `useReducedMotion`; animations disabled/reduced when `true`
- FR-11: Chart components use CSS vars for colors so they switch automatically with theme changes
- FR-12: Map components work without an API key using OpenFreeMap tiles
- FR-13: `novel-editor.tsx` AI suggestions call the Laravel AI SDK backend via configurable `aiEndpoint` prop
- FR-14: `AssistantRuntimeProvider` connects to the existing Laravel AI SDK backend
- FR-15: Global search (`Cmd+K`) queries Scout/Typesense via a dedicated backend route; results grouped by model type
- FR-16: Keyboard shortcut registry is the single source of truth; `command-bar.tsx` and `keyboard-shortcut-display.tsx` both read from it
- FR-17: All app shell templates (`shells/`) include `<SkipToContent />` and `id="main-content"` on the main content area
- FR-18: The in-app component showcase page (`/dev/components`) must be gated by the `component-showcase` Pennant feature flag — disabled in production by default
- FR-19: `ThemeCustomizer` is always rendered with `canCustomize: true` on the showcase route, overriding the normal permission check
- FR-20: "Try Random" in `ThemeCustomizer` uses `randomTheme()` from the utilities library (US-036) to generate a valid random theme combination
- FR-21: Hover-to-preview in `ThemeCustomizer` must revert on mouse-leave if the user does not click; the in-progress hover state must not be sent to the API
- FR-22: `useOnline`, `useReducedMotion`, `useFocusTrap`, and `useKeyboardShortcut` must all be importable from `@/hooks`
- FR-23: All utility functions must be importable from `@/lib/utils`; the existing `cn()` function must remain importable from its current path (do not break existing imports)
- FR-24: Storybook excluded from the production Vite build
- FR-25: `vendor/bin/pint --dirty --format agent` passes on all PHP files
- FR-26: `npx tsc --noEmit` passes on all TypeScript files

## Non-Goals

- No migration or replacement of existing shadcn `ui/` components — additions only
- No Tailux license purchase or use of closed-source Tailux assets — CSS variable patterns only
- No changes to the authentication or authorization system
- No Figma design file creation or maintenance
- No automated visual regression testing (screenshot diffing)
- No production deployment of Storybook — local dev only
- The in-app showcase page is not a substitute for Storybook — it has no argTypes controls, no isolated rendering, and no per-component documentation; it is a kitchen-sink visual review only
- No new AI backend routes beyond novel-editor completions and AssistantRuntimeProvider — use existing Laravel AI SDK conventions
- No removal of existing `appearance-tabs.tsx` or `config/theme.php`
- No per-user persistent theme (palette/colors) storage in DB — user changes to palette are session/localStorage only; only `theme_mode` (dark/light/system) is persisted to DB

## Design Considerations

- Follow existing `resources/js/components/ui/` naming and file structure
- All components support dark/light via CSS vars, not JS logic
- Reference Tailux source at `/Users/apple/Downloads/tailux/ts/demo/src/` for tokens and patterns
- `ThemeCustomizer` floating button: paintbrush icon, fixed right side of screen
- `ThemeCustomizer` panel UX: compact property-list rows (shadcn/ui create style) — not a long page of swatches; each row shows property + current value; click to expand inline options
- Theme Mode thumbnails (System/Light/Dark) and Layout thumbnails (Main/Sideblock) should be visual mini-card illustrations, not text-only buttons — reference Tailux screenshot
- "Try Random" keyboard shortcut is `R` while the panel is open; the button shows the shortcut key hint
- Hover-to-preview: a subtle live preview on hover before the user commits by clicking; visual feedback that something will change
- Card skin: `bordered` = 1px border no shadow; `shadow` = shadow-soft no border
- `trial-banner` and `impersonation-banner` render above the app shell header, not inside it
- `feature-gate.tsx` upgrade prompt should be subtle — not a full-page block; more like a soft overlay with blurred content behind it
- `onboarding-checklist.tsx` should be collapsible to a floating button when minimized
- `offline-banner.tsx` slides in from the top with a smooth animation; auto-dismisses 3s after reconnection
- Filament toggle label: "Allow users to customize their own theme" with helper: "When enabled, all authenticated users can access the theme customizer. When disabled, only organization admins can."

## Technical Considerations

- Tailwind CSS v4 `@theme` syntax required for custom tokens (not `tailwind.config.js`)
- `react-hook-form`, `@hookform/resolvers`, `zod`, `@dnd-kit/*` confirmed absent from `package.json` — must install
- New npm packages needed: `qrcode.react`, `react-signature-canvas`, `@types/react-signature-canvas`, `react-diff-viewer-continued`, `react-pdf`, `react-compare-slider`
- `react-resizable-panels` check: shadcn `resizable` may already wrap this — verify before creating `resizable.tsx`
- `SettingsOverlayServiceProvider::OVERLAY_MAP` — follow exact pattern of existing entries
- `allow_user_theme_customization` must NOT be `orgOverridable`
- `theme_mode` per-user: simplest approach is a `theme_mode` column on the `users` table (nullable, default null = 'system'); avoid a separate table unless users table is off-limits
- Storybook Vite config must share `vite.config.ts` aliases (`@/` path alias)
- `@inertiajs/react` must be mocked in `.storybook/preview.tsx` — never imported directly
- Global search backend route: check existing `routes/web.php` and `routes/api.php` for Scout usage patterns before creating `GlobalSearchController`
- `import-wizard.tsx` — step 2 column mapping must handle headers-not-matching-target-fields gracefully (show "Skip this column" option)
- `pdf-viewer.tsx` using `react-pdf` requires a PDF.js worker — configure worker URL in the component using the CDN or Vite asset copy
- `ThemeCustomizer` hover-to-preview: track `hoveredTheme` state locally; apply it to `data-*` attrs on `mouseenter`, revert to `savedTheme` on `mouseleave`, commit to `selectedTheme` on `click`; only `selectedTheme` is sent to the API on "Save for Organization"
- `randomTheme()` utility must only produce combinations where all values come from the defined constant arrays in `tailux-themes.ts`; it must never generate invalid/unsupported theme values
- `useLocalStorage` hook must handle JSON parse errors gracefully (corrupt storage value → fall back to `initialValue`)
- `formatRelativeTime` utility should use `date-fns/formatDistanceToNow` (already installed as a dependency of `react-day-picker`)
- New utilities in `resources/js/lib/utils/` must NOT modify or move the existing `cn()` function in `resources/js/lib/utils.ts`; they are additive alongside it
- Hooks directory `resources/js/hooks/` must have a path alias `@/hooks` configured in `vite.config.ts` and `tsconfig.json` if not already present

## Success Metrics

- `npm run storybook` starts on port 6006 with 0 errors; all stories render including saas/, admin/, shells/ categories
- All 5 dark themes, 6 primary colors, and named presets switch correctly in Storybook toolbar and in the app
- `ThemeCustomizer` floating button visible to org admins; invisible to regular users when `allow_user_theme_customization = false`; visible when `true`
- A theme change applies instantly without page reload
- User selecting "Dark" mode persists across page reload; OS preference change is reflected live in "System" mode
- `php artisan migrate` runs all new migrations with no errors
- `feature-gate.tsx` renders children when `hasAccess=true`; renders upgrade prompt when `false`
- `global-search.tsx` opens on `Cmd+K`, queries backend, returns grouped results
- `import-wizard.tsx` completes all 4 steps with a mock CSV
- `keyboard-shortcut-display.tsx` lists all registered shortcuts grouped by scope
- Navigating to `/dev/components` in a local environment renders the full showcase page with all categories visible; the route 404s in production
- The sticky live theme bar on the showcase page updates instantly when the `ThemeCustomizer` panel changes a dimension
- All map stories render tiles with no API key
- `novel-editor.tsx` shows AI suggestion UI
- `ThemeCustomizer` compact panel opens; pressing `R` randomizes all dimensions instantly; hover over a swatch previews it live and reverts on mouse-leave; clicking commits; "Export theme" copies valid JSON; "Import theme" applies pasted JSON
- Sidebar layout "Sideblock" option visually repositions the sidebar in the app shell
- `formatCurrency(1234.56)` returns `"$1,234.56"`; `formatNumber(1_200_000)` returns `"1.2M"`; `formatRelativeTime` returns `"2 hours ago"` for a date 2 hours ago
- `useDebounce`, `useLocalStorage`, `useOnline`, and all other hooks importable from `@/hooks`
- `offline-banner.tsx` appears when network is disconnected and disappears 3 seconds after reconnection
- `error-boundary.tsx` catches a thrown error in a child component and renders the default `ErrorState` fallback with a working "Try again" button
- `npm run build` passes (Storybook excluded)
- `npx tsc --noEmit` passes (0 type errors)
- `vendor/bin/pint --dirty --format agent` passes (0 PHP style violations)

## Open Questions

- Which existing Settings class should receive `allow_user_theme_customization`? Check `GeneralSettings` first.
- Should `theme_mode` be a column on the `users` table or a separate `user_preferences` table? Prefer `users` table if schema permits.
- For `novel-editor.tsx` and `AssistantRuntimeProvider` AI endpoints, check existing `/ai/*` routes before creating new ones to avoid duplication.
- Which Scout-indexed models should be included in global search results by default? Suggested defaults: `User`, `Organization` — implementor to check which models have `Searchable` trait.
- Should `impersonation-banner.tsx` use the existing `auth.impersonating` Inertia shared prop, or does one need to be added to `HandleInertiaRequests`?
- Should `whats-new-modal.tsx` version be driven by `config('app.version')` or a hardcoded constant per deploy?
- Should the showcase page be accessible to all authenticated users in local/staging (for designer/client review), or restricted to users with the `access admin panel` gate? Recommend: all authenticated users in local/staging.
- For the `sideblock` sidebar layout variant — should it be a visually detached floating sidebar (like a card beside content) or a full-height panel that just shifts position? Implementor should reference Tailux's Sideblock demo at `https://tailux.piniastudio.com` for the intended visual.
- Should `formatRelativeTime` use `date-fns` or the native `Intl.RelativeTimeFormat` API? Prefer `date-fns/formatDistanceToNow` for consistency with existing `react-day-picker` dependency.
- Should the fonts offered in the Font picker (Inter, Geist, Poppins, etc.) be loaded via Google Fonts / Bunny Fonts CDN, or self-hosted? Recommend: CDN links added to the `<head>` only when a given font is active (lazy-loaded via `theme-from-props.tsx`).
