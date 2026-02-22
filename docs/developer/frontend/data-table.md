# DataTable (React)

Server-side tables use the shared `<DataTable>` component from `@/components/data-table/data-table`, driven by `tableData` from the Laravel DataTable class (see [backend data-table.md](../backend/data-table.md)).

## Usage

- **Page**: Receive `tableData` from the Inertia page props and pass it with a unique `tableName` (used for localStorage column state).
- **Types**: Use `DataTableResponse<TRow>` from `@/components/data-table/types`; define a row type matching the backend DTO (e.g. id, name, email, created_at for Users).

Example:

```tsx
import { DataTable } from '@/components/data-table/data-table';
import type { DataTableResponse } from '@/components/data-table/types';

interface Row { id: number; name: string; email: string; created_at: string | null; }

export default function UsersTablePage({ tableData }: { tableData: DataTableResponse<Row> }) {
  return (
    <DataTable tableData={tableData} tableName="users" />
  );
}
```

## Options

- **actions**: Row actions (e.g. edit, delete).
- **bulkActions**: Toolbar actions when rows are selected.
- **renderCell**: Custom cell renderer by column id.
- **options**: Toggle quick views, exports, filters, column visibility/ordering (see `DataTableOptions`).

Column visibility and order are persisted in localStorage under `tableName`.
