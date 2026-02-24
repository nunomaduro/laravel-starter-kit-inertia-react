# PageController

## Purpose

Handles CRUD and management of custom pages built with the Puck editor. Scoped to the current organization (tenant). Used by org admins to list, create, edit, duplicate, and delete pages.

## Location

`app/Http/Controllers/PageController.php`

## Methods

| Method     | HTTP Method | Route                   | Purpose                          |
|-----------|-------------|-------------------------|----------------------------------|
| index     | GET         | `pages`                 | List pages for current org      |
| create    | GET         | `pages/create`          | Show create form (with templates) |
| store     | POST        | `pages`                 | Create a new page               |
| edit      | GET         | `pages/{page}/edit`     | Show Puck editor (ssr: false)   |
| update    | PUT         | `pages/{page}`          | Update page name, slug, puck_json, is_published |
| duplicate | POST        | `pages/{page}/duplicate`| Duplicate page (new name/slug)   |
| destroy   | DELETE      | `pages/{page}`          | Delete page                     |

## Routes

- `pages.index`: GET `pages` - List pages
- `pages.create`: GET `pages/create` - Create form
- `pages.store`: POST `pages` - Store new page
- `pages.edit`: GET `pages/{page}/edit` - Edit in Puck editor
- `pages.update`: PUT `pages/{page}` - Update page
- `pages.duplicate`: POST `pages/{page}/duplicate` - Duplicate page
- `pages.destroy`: DELETE `pages/{page}` - Delete page

## Actions Used

None. PageController performs simple Eloquent operations and delegates validation to Form Requests.

## Validation

- **StorePageRequest** – name (required), slug (optional, unique per org), puck_json (optional array)
- **UpdatePageRequest** – name (required), slug (required, unique per org ignoring current), puck_json (optional), is_published (optional boolean)

## Related Components

- **Pages**: `pages/index`, `pages/edit` (rendered by index, create, store, edit, update, destroy)
- **Policy**: `PagePolicy` (org.pages.manage for create/update/delete; view for published or editors)
- **Routes**: All under `auth` + `tenant` middleware
