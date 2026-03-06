# dev/components

## Purpose

A developer-only kitchen-sink page at `/dev/components` that renders all UI components with the live application theme. It is only available in `local` and `staging` environments, gated by the `component_showcase` feature flag.

## Location

`resources/js/pages/dev/components.tsx`

## Route Information

- **URL**: `/dev/components`
- **Route Name**: `dev.components`
- **HTTP Method**: `GET`
- **Middleware**: `auth`, `feature:component_showcase`

## Props (from Controller)

| Prop | Type | Description |
|------|------|-------------|
| (none) | — | Page receives no props from the controller |

## User Flow

1. Developer navigates to `/dev/components` (local or staging only)
2. The page renders inside `AppShell` with a sticky `ThemeBar` showing live theme dimensions
3. A sidebar lists 16 component categories; clicking a category smooth-scrolls to its section
4. The `ThemeCustomizerPanel` is always visible so developers can switch themes and see components update in real time
5. Pressing `?` opens `KeyboardShortcutDisplay` showing available keyboard shortcuts

## Related Components

- **Controller**: `App\Http\Controllers\Dev\ComponentShowcaseController`
- **Feature flag**: `App\Features\ComponentShowcaseFeature` (`component_showcase`)
- **Route**: `dev.components`

## Implementation Details

- **Environment guard**: the route itself is only registered when `app()->environment(['local', 'staging'])` is true, and the `feature:component_showcase` middleware provides a second check.
- **Live theme tracking**: a `useActiveTheme()` hook attaches a `MutationObserver` to `document.documentElement` and reads `data-theme-dark`, `data-theme-primary`, `data-radius`, and `data-card-skin` attributes whenever they change.
- **Active section tracking**: a `useActiveSection()` hook uses `IntersectionObserver` to highlight the sidebar entry for the visible section.
- **ThemeCustomizerPanel**: imported and rendered without the `canCustomize` gate so developers can always toggle themes.
- **KeyboardShortcutDisplay**: mounted so pressing `?` opens the shortcuts overlay.
- **16 categories**: Foundation, Layout, Shells, Navigation, Buttons & Actions, Forms, Data Display, Feedback, Overlay, Charts, Maps, AI, SaaS, Admin, Composed, Accessibility.
