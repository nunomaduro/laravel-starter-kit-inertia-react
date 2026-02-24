# index

## Purpose

Lists all custom pages for the current organization. Users with `org.pages.manage` can create, edit, duplicate, delete, and view published pages.

## Location

`resources/js/pages/pages/index.tsx`

## Route Information

- **URL**: `/pages`
- **Route Name**: `pages.index`
- **HTTP Method**: GET
- **Middleware**: `auth`, `tenant`

## Props (from Controller)

| Prop   | Type     | Description                                      |
|--------|----------|--------------------------------------------------|
| pages  | array    | List of `{ id, name, slug, is_published, updated_at }` |

## User Flow

1. User navigates to Pages (sidebar or direct URL).
2. Sees list of pages with name, slug, draft badge, updated date.
3. Can click "New page", "Edit", "View" (if published), Duplicate, or Delete.
4. Delete and Duplicate use confirmation where appropriate.

## Related Components

- **Controller**: `PageController@index`
- **Route**: `pages.index`
- **Layout**: AppLayout with sidebar
