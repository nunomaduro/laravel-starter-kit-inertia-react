import { DataTable } from '@/components/data-table/data-table';
import type {
    DataTableHeaderAction,
    DataTableResponse,
} from '@/components/data-table/types';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { Megaphone, Plus } from 'lucide-react';

export interface AnnouncementsTableRow {
    id: number;
    title: string;
    level: string;
    scope: string;
    is_active: boolean;
    starts_at: string | null;
    ends_at: string | null;
    organization_name: string | null;
    creator_name: string | null;
    created_at: string | null;
}

interface Props {
    tableData?: DataTableResponse<AnnouncementsTableRow>;
    searchableColumns: string[];
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Announcements', href: '/announcements' },
];

export default function AnnouncementsTablePage({
    tableData,
    searchableColumns = [],
}: Props) {
    const headerActions: DataTableHeaderAction[] = [
        {
            label: 'Add announcement',
            icon: Plus,
            variant: 'default',
            onClick: () => router.visit('/admin/announcements/create'),
        },
    ];

    return (
        <AppSidebarLayout breadcrumbs={breadcrumbs}>
            <Head title="Announcements" />
            <div
                className="flex h-full flex-1 flex-col gap-4 p-4"
                data-pan="announcements-table"
            >
                <div>
                    <h1 className="text-2xl font-bold tracking-tight">
                        Announcements
                    </h1>
                    {tableData && <p className="text-muted-foreground">{tableData.meta.total} results</p>}
                </div>
                <DataTable<AnnouncementsTableRow>
                    tableData={tableData}
                    tableName="announcements"
                    searchableColumns={searchableColumns}
                    debounceMs={300}
                    partialReloadKey="tableData"
                    emptyState={
                        <div className="flex flex-col items-center justify-center gap-4 py-16 text-center">
                            <div className="rounded-full bg-muted p-4">
                                <Megaphone className="size-8 text-muted-foreground" />
                            </div>
                            <div>
                                <p className="font-medium">
                                    No announcements found
                                </p>
                                <p className="text-sm text-muted-foreground">
                                    Try adjusting your search or filters.
                                </p>
                            </div>
                        </div>
                    }
                    headerActions={headerActions}
                    onReorder={() => {
                        router.reload({ only: ['tableData'] });
                    }}
                    options={{
                        columnVisibility: true,
                        columnOrdering: true,
                        columnResizing: true,
                        columnPinning: true,
                        quickViews: true,
                        customQuickViews: true,
                        exports: true,
                        filters: true,
                        density: true,
                        copyCell: true,
                        emptyStateIllustration: true,
                        rowReorder: !!tableData?.reorderUrl,
                        keyboardNavigation: true,
                        shortcutsOverlay: true,
                    }}
                    translations={{
                        noData: 'No announcements',
                        search: 'Search announcements',
                        clearAllFilters: 'Clear all filters',
                        density: 'Row density',
                        selectAllMatching: (count) =>
                            `Select all ${count} announcements`,
                    }}
                />
            </div>
        </AppSidebarLayout>
    );
}
