# Implementation Plan: Horizon, Reverb, Categorizable/Nested Set

Plan for adding Laravel Horizon (queues), Laravel Reverb (WebSockets), and Categorizable + Nested set (categories) to the Inertia starter kit. Reference: boilerplatelivewire.

---

## 1. Laravel Horizon (queues)

**Goal:** Redis-backed queue monitoring and workers; dashboard at `/horizon`.

**Steps:**
- [x] Require `laravel/horizon`, publish `config/horizon.php`.
- [x] Configure Horizon to use existing Redis connection; environment-specific supervisors (e.g. `production`, `local`).
- [x] Restrict `/horizon` to authorized users (e.g. gate or middleware; only users who can access admin).
- [x] Add `.env.example` block: `QUEUE_CONNECTION=redis` (optional, with comment that Horizon requires Redis), and any Horizon-specific vars (`HORIZON_PATH`, etc.) if needed.
- [x] Document: `docs/developer/backend/horizon.md` (config, running, monitoring); link from backend README.
- [x] Optional: Use Horizon in production only; keep `QUEUE_CONNECTION=database` for dev without Redis if desired (document both options).

**Current kit benefit:** Personal data export job, Scout queue, backup notifications, and all future queued jobs get monitoring and retries.

**Durable Workflow & Waterline (added):** [laravel-workflow/laravel-workflow](https://github.com/durable-workflow/workflow) and [laravel-workflow/waterline](https://github.com/durable-workflow/waterline) are installed for long-running workflows; Waterline dashboard at `/waterline` (admin only). See [durable-workflow.md](./durable-workflow.md).

---

## 2. Laravel Reverb (WebSockets)

**Goal:** Real-time broadcasting via Reverb; frontend listens with Laravel Echo (React).

**Steps:**
- [x] Require `laravel/reverb`; install and publish config; add Reverb env vars to `.env.example` (`BROADCAST_CONNECTION=reverb`, `REVERB_*`, `VITE_*` for Echo).
- [x] Define broadcast channels in `routes/channels.php` (e.g. private `App.Models.User.{id}` for user-specific events).
- [ ] Optional first event: broadcast when personal data export is ready (event + listener or job completion) so the user gets a real-time notification.
- [x] Frontend: Add Laravel Echo + Pusher driver (e.g. `laravel-echo`, `pusher-js`) in package.json; create Echo bootstrap (e.g. in `resources/js/bootstrap.ts` or a dedicated `echo.ts`) using Reverb/Pusher-compatible config; expose `VITE_REVERB_*` from env.
- [x] Document: `docs/developer/backend/reverb.md` (config, starting Reverb, channels, Echo on frontend); link from backend README.
- [ ] Optional: Filament broadcasting (notifications) if desired.

**Current kit benefit:** Real-time “export ready” notification; base for future live features (notifications, dashboards).

---

## 3. Categorizable + Nested set (categories)

**Goal:** Tree of categories; models (e.g. User) can be attached via `Categorizable` trait. Admin UI in Filament.

**Steps:**
- [x] Require `kalnoy/nestedset` (alibayat/laravel-categorizable does not support Laravel 12; implemented minimal Categorizable in-app).
- [x] Create migration for `categories` (with NestedSet columns) and `categoryables` (morph pivot).
- [x] Add `Categorizable` trait (`App\Models\Concerns\Categorizable`) and add it to `User` model.
- [x] Filament: Category resource for managing categories (name, type, parent).
- [x] Document: `docs/developer/backend/categorizable.md` (trait, migration, Filament); link from backend README. Note coexistence with Spatie Tags (tags = flat; categories = tree).
- [ ] Add to `docs/developer/backend/search-and-data.md` or similar if that’s where DTOs/slugs/sortable are documented (optional).

**Current kit benefit:** User segments/categories in admin; later, categorizable content when Blog/Help center modules are added.

---

## 4. Order and dependencies

| Order | Feature        | Deps              |
|-------|----------------|-------------------|
| 1     | Horizon        | Redis (already in config) |
| 2     | Reverb         | None              |
| 3     | Categorizable  | None              |

---

## 5. Testing

- Horizon: No unit test for Horizon itself; ensure existing queue tests (e.g. personal data export) still pass with `database` driver; document that with Redis + Horizon, run `php artisan horizon` for workers.
- Reverb: Optional feature test that broadcasts an event and (with a fake or in-memory driver) assert channel/event; or manual test with Echo.
- Categorizable: Feature test that User can attach/detach categories; optionally test tree structure (parent/child).

---

## 6. Backend README and compare_features.md

- Add Horizon, Reverb, Categorizable to backend README Contents and “At a glance” (for agents).
- Update `compare_features.md`: add “Horizon”, “Reverb”, “Categorizable / nested set” under “Later” with status “Done” and link to docs when implemented.
