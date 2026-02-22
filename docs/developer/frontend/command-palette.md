# Command palette

The app provides a global command palette (Mod+K / Cmd+K on macOS) built with **cmdk** and **@tanstack/hotkeys**.

## Location

- **Component**: `resources/js/components/command-dialog.tsx` (`CommandPalette`)
- **Layout**: Rendered in `AppSidebarLayout` so it is available on all authenticated app shell pages.

## Behavior

- **Shortcut**: `Mod+K` opens or closes the palette (registered via `getHotkeyManager().register('Mod+k', ...)` from `@tanstack/hotkeys`).
- **Content**: Navigation links (Dashboard, Organizations, Billing, Blog, Changelog, Help, Contact), Settings, and Log out. Visible items respect the same permissions and feature flags as the sidebar.
- **Analytics**: The dialog root has `data-pan="command-palette"`; the name is whitelisted in `AppServiceProvider::configurePan()`.

## Usage

No integration needed; the palette is always mounted in the sidebar layout. Use Wayfinder route functions for hrefs so links stay in sync with the backend.

For **server-backed search** (e.g. DataTable custom search or autocomplete), use TanStack Pacer’s debouncer so requests run after the user stops typing; see [Utilities](./utilities.md#tanstack-pacer-tanstackpacer).
