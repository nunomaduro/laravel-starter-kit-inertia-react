import { DataTable } from '@/components/data-table/data-table';
import type { DataTableResponse } from '@/components/data-table/types';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Head } from '@inertiajs/react';

export interface UsersTableRow {
    id: number;
    name: string;
    email: string;
    created_at: string | null;
}

interface Props {
    tableData: DataTableResponse<UsersTableRow>;
}

export default function UsersTablePage({ tableData }: Props) {
    return (
        <AppSidebarLayout>
            <Head title="Users" />
            <div className="flex h-full flex-1 flex-col gap-4 p-4" data-pan="users-table">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight">Users</h1>
                    <p className="text-muted-foreground">
                        {tableData.meta.total} results
                    </p>
                </div>
                <DataTable<UsersTableRow>
                    tableData={tableData}
                    tableName="users"
                />
            </div>
        </AppSidebarLayout>
    );
}
