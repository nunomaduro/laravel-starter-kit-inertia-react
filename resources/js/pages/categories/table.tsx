import { DataTable } from '@/components/data-table/data-table';
import type {
    DataTableHeaderAction,
    DataTableResponse,
} from '@/components/data-table/types';
import { Skeleton } from '@/components/ui/skeleton';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { Folder, Plus } from 'lucide-react';

export interface CategoriesTableRow {
    id: number;
    name: string;
    slug: string;
    type: string;
    parent_id: number | null;
    parent_name: string | null;
    created_at: string | null;
}

interface Props {
    tableData?: DataTableResponse<CategoriesTableRow>;
    searchableColumns: string[];
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Categories', href: '/categories' },
];

export default function CategoriesTablePage({
    tableData,
    searchableColumns = [],
}: Props) {
    const headerActions: DataTableHeaderAction[] = [
        {
            label: 'Add category',
            icon: Plus,
            variant: 'default',
            onClick: () => router.visit('/admin/categories/create'),
        },
    ];

    if (!tableData) {
        return (
            <AppSidebarLayout breadcrumbs={breadcrumbs}>
                <Head title="Categories" />
                <div
                    className="flex h-full flex-1 flex-col gap-4 p-4"
                    data-pan="categories-table"
                >
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">
                            Categories
                        </h1>
                        <Skeleton className="mt-1 h-5 w-24" />
                    </div>
                    <Skeleton className="h-[400px] w-full rounded-md" />
                </div>
            </AppSidebarLayout>
        );
    }

    return (
        <AppSidebarLayout breadcrumbs={breadcrumbs}>
            <Head title="Categories" />
            <div
                className="flex h-full flex-1 flex-col gap-4 p-4"
                data-pan="categories-table"
            >
                <div>
                    <h1 className="text-2xl font-bold tracking-tight">
                        Categories
                    </h1>
                    <p className="text-muted-foreground">
                        {tableData.meta.total} results
                    </p>
                </div>
                <DataTable<CategoriesTableRow>
                    tableData={tableData}
                    tableName="categories"
                    searchableColumns={searchableColumns}
                    debounceMs={300}
                    partialReloadKey="tableData"
                    emptyState={
                        <div className="flex flex-col items-center justify-center gap-4 py-16 text-center">
                            <div className="rounded-full bg-muted p-4">
                                <Folder className="size-8 text-muted-foreground" />
                            </div>
                            <div>
                                <p className="font-medium">
                                    No categories found
                                </p>
                                <p className="text-sm text-muted-foreground">
                                    Try adjusting your search or filters.
                                </p>
                            </div>
                        </div>
                    }
                    headerActions={headerActions}
                    options={{
                        stickyHeader: true,
                        globalSearch: true,
                        columnVisibility: true,
                        columnOrdering: true,
                        columnResizing: true,
                        columnPinning: true,
                        exports: true,
                        filters: true,
                        density: 'comfortable',
                        copyCell: true,
                        emptyStateIllustration: true,
                        keyboardNavigation: true,
                        shortcutsOverlay: true,
                    }}
                    translations={{
                        noData: 'No categories',
                        search: 'Search categories',
                        clearFilters: 'Clear all filters',
                        density: 'Row density',
                        selectAllMatching: (count) =>
                            `Select all ${count} categories`,
                    }}
                />
            </div>
        </AppSidebarLayout>
    );
}
