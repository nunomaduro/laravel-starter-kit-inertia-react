import { DataTable } from '@/components/data-table/data-table';
import type {
    DataTableHeaderAction,
    DataTableResponse,
} from '@/components/data-table/types';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { Building2, Plus } from 'lucide-react';

export interface OrganizationsTableRow {
    id: number;
    name: string;
    slug: string;
    owner_name: string | null;
    created_at: string | null;
}

interface Props {
    tableData?: DataTableResponse<OrganizationsTableRow>;
    searchableColumns: string[];
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Organizations', href: '/organizations' },
    { title: 'List', href: '/organizations/list' },
];

export default function OrganizationsTablePage({
    tableData,
    searchableColumns = [],
}: Props) {
    const headerActions: DataTableHeaderAction[] = [
        {
            label: 'Add organization',
            icon: Plus,
            variant: 'default',
            onClick: () => router.visit('/organizations/create'),
        },
    ];

    return (
        <AppSidebarLayout breadcrumbs={breadcrumbs}>
            <Head title="Organizations" />
            <div
                className="flex h-full flex-1 flex-col gap-4 p-4"
                data-pan="organizations-table"
            >
                <div>
                    <h1 className="text-2xl font-bold tracking-tight">
                        Organizations
                    </h1>
                    {tableData && <p className="text-muted-foreground">{tableData.meta.total} results</p>}
                </div>
                <DataTable<OrganizationsTableRow>
                    tableData={tableData}
                    tableName="organizations"
                    searchableColumns={searchableColumns}
                    debounceMs={300}
                    partialReloadKey="tableData"
                    rowLink={(row) => `/organizations/${row.id}`}
                    emptyState={
                        <div className="flex flex-col items-center justify-center gap-4 py-16 text-center">
                            <div className="rounded-full bg-muted p-4">
                                <Building2 className="size-8 text-muted-foreground" />
                            </div>
                            <div>
                                <p className="font-medium">
                                    No organizations found
                                </p>
                                <p className="text-sm text-muted-foreground">
                                    Try adjusting your search or filters.
                                </p>
                            </div>
                        </div>
                    }
                    headerActions={headerActions}
                    options={{
                        columnVisibility: true,
                        columnOrdering: true,
                        columnResizing: true,
                        columnPinning: true,
                        exports: true,
                        filters: true,
                        density: true,
                        copyCell: true,
                        emptyStateIllustration: true,
                        keyboardNavigation: true,
                        shortcutsOverlay: true,
                    }}
                    translations={{
                        noData: 'No organizations',
                        search: 'Search organizations',
                        clearAllFilters: 'Clear all filters',
                        density: 'Row density',
                        selectAllMatching: (count) =>
                            `Select all ${count} organizations`,
                    }}
                />
            </div>
        </AppSidebarLayout>
    );
}
