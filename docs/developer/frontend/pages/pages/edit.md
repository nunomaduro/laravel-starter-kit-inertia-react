# edit

## Purpose

Puck-based editor for creating or editing a custom page. Supports name, slug, optional template selection (on create), and is_published (on edit). Editor is lazy-loaded and runs with `ssr: false` to avoid SSR issues.

## Location

`resources/js/pages/pages/edit.tsx`

## Route Information

- **URL**: `/pages/create` (create) or `/pages/{page}/edit` (edit)
- **Route Names**: `pages.create`, `pages.edit`
- **HTTP Method**: GET (page), POST (store), PUT (update)
- **Middleware**: `auth`, `tenant`

## Props (from Controller)

| Prop      | Type   | Description                                                    |
|-----------|--------|----------------------------------------------------------------|
| page      | object \| null | `{ id, name, slug, puck_json, is_published }` or null for create |
| puckJson  | object | `{ root, content }` for Puck (used as initial data)          |
| templates | array  | Optional; `[{ key, label, data }]` for "Create from template"  |

## User Flow

1. User opens create or edit (from index or direct URL).
2. Fills name, slug; optionally selects a template (create only); toggles published (edit only).
3. Edits content in the Puck editor (drag-and-drop blocks).
4. Submits to store (create) or update (edit). Redirects to edit or index with flash message.

## Related Components

- **Controller**: `PageController@create`, `PageController@edit`, `PageController@store`, `PageController@update`
- **Config**: `resources/js/lib/puck-config.tsx` (same config used in show for Render)
- **Layout**: AppLayout
