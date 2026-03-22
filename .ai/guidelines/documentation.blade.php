# Documentation Guidelines

## When to Document

Document new Actions, Controllers, Pages, and Routes. Skip documentation for bug fixes, refactors, and UI-only changes unless they change workflows.

## Documentation Locations

| Component | Location |
|-----------|----------|
| Actions | `docs/developer/backend/actions/` |
| Controllers | `docs/developer/backend/controllers/` |
| Pages | `docs/developer/frontend/pages/` |
| Routes | `docs/developer/api-reference/routes.md` |
| User-facing features | `docs/user-guide/` |

## Templates

Templates in `docs/.templates/`: `action.md`, `controller.md`, `page.md`, `user-feature.md`.

## Manifest Sync

Run `php artisan docs:sync` to update `docs/.manifest.json` with codebase state and relationships.

- `--check`: Check for undocumented items without updating
- `--generate`: Create documentation stubs for undocumented items

## Cross-Referencing

The manifest tracks relationships automatically:

- **Actions**: Which controllers use them, which models they use, which routes call them
- **Controllers**: Which actions they use, which form requests, which routes, which pages they render
- **Pages**: Which controllers render them, which routes lead to them
