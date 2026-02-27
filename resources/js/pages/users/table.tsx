import { DataTable } from '@/components/data-table/data-table';
import type {
    DataTableAction,
    DataTableBulkAction,
    DataTableResponse,
} from '@/components/data-table/types';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Head, router } from '@inertiajs/react';
import { Copy, Users } from 'lucide-react';

export interface UsersTableRow {
    id: number;
    name: string;
    email: string;
    created_at: string | null;
}

interface Props {
    tableData: DataTableResponse<UsersTableRow>;
    searchableColumns: string[];
}

export default function UsersTablePage({
    tableData,
    searchableColumns = [],
}: Props) {
    const rowActions: DataTableAction<UsersTableRow>[] = [
        {
            label: 'View',
            onClick: (row) => router.visit(`/users/${row.id}`),
        },
    ];

    const bulkActions: DataTableBulkAction<UsersTableRow>[] = [
        {
            id: 'copy-ids',
            label: 'Copy selected IDs',
            icon: Copy,
            onClick: (rows) => {
                const ids = rows.map((r) => r.id).join(', ');
                void navigator.clipboard.writeText(ids);
            },
        },
    ];

    return (
        <AppSidebarLayout>
            <Head title="Users" />
            <div
                className="flex h-full flex-1 flex-col gap-4 p-4"
                data-pan="users-table"
            >
                <div>
                    <h1 className="text-2xl font-bold tracking-tight">Users</h1>
                    <p className="text-muted-foreground">
                        {tableData.meta.total} results
                    </p>
                </div>
                <DataTable<UsersTableRow>
                    tableData={tableData}
                    tableName="users"
                    searchableColumns={searchableColumns}
                    debounceMs={300}
                    rowLink={(row) => `/users/${row.id}`}
                    emptyState={
                        <div className="flex flex-col items-center justify-center gap-2 py-8 text-muted-foreground">
                            <Users className="size-10" />
                            <p className="font-medium">No users found</p>
                            <p className="text-sm">
                                Try adjusting your search or filters.
                            </p>
                        </div>
                    }
                    actions={rowActions}
                    bulkActions={bulkActions}
                    options={{
                        stickyHeader: true,
                        globalSearch: true,
                        columnVisibility: true,
                        columnOrdering: true,
                        quickViews: true,
                        customQuickViews: true,
                        exports: true,
                        filters: true,
                    }}
                />
            </div>
        </AppSidebarLayout>
    );
}
