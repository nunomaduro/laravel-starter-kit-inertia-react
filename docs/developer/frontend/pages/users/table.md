# Users table

## Purpose

Displays a server-side paginated table of users (id, name, email, created_at) with sorting, filtering, quick views (All, Created this year), column visibility/ordering, and optional export. Powered by `UserDataTable` and the shared `<DataTable>` component.

## Location

`resources/js/pages/users/table.tsx`

## Route Information

- **URL**: `/users`
- **Route Name**: `users.table`
- **HTTP Method**: GET
- **Middleware**: web, auth, verified

## Props (from Controller)

| Prop | Type | Description |
|------|------|-------------|
| tableData | DataTableResponse&lt;UsersTableRow&gt; | Data, columns, quick views, meta (pagination, sorts, filters), and optional exportUrl from `UserDataTable::makeTable($request)`. |

## User Flow

1. User navigates to `/users` (or Users from nav).
2. User can select a quick view (All, Created this year), change sort, apply filters, change page size, or reorder/toggle columns.
3. Table state is reflected in the URL (bookmarkable); column visibility/order are persisted in localStorage under `tableName="users"`.

## Related Components

- **Backend**: `App\DataTables\UserDataTable`; route passes `UserDataTable::makeTable($request)` as `tableData`.
- **Route**: `users.table` (GET /users).
- **Component**: `<DataTable>` from `@/components/data-table/data-table`.

## Implementation Details

- Row type `UsersTableRow`: id, name, email, created_at. Matches the backend DTO.
- Page has `data-pan="users-table"` for product analytics; name is whitelisted in `AppServiceProvider::configurePan()`.
- See `docs/developer/backend/data-table.md` and `docs/developer/frontend/data-table.md`.
