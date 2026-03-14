import { DataTable } from '@/components/data-table/data-table';
import type {
    DataTableHeaderAction,
    DataTableResponse,
} from '@/components/data-table/types';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { FileText, Plus } from 'lucide-react';

export interface PostsTableRow {
    id: number;
    title: string;
    is_published: boolean;
    published_at: string | null;
    views: number;
    author_name: string | null;
    created_at: string | null;
}

interface Props {
    tableData?: DataTableResponse<PostsTableRow>;
    searchableColumns: string[];
}

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Posts', href: '/posts' }];

export default function PostsTablePage({
    tableData,
    searchableColumns = [],
}: Props) {
    const headerActions: DataTableHeaderAction[] = [
        {
            label: 'Add post',
            icon: Plus,
            variant: 'default',
            onClick: () => router.visit('/admin/posts/create'),
        },
    ];

    return (
        <AppSidebarLayout breadcrumbs={breadcrumbs}>
            <Head title="Posts" />
            <div
                className="flex h-full flex-1 flex-col gap-4 p-4"
                data-pan="posts-table"
            >
                <div>
                    <h1 className="text-2xl font-bold tracking-tight">Posts</h1>
                    {tableData && <p className="text-muted-foreground">{tableData.meta.total} results</p>}
                </div>
                <DataTable<PostsTableRow>
                    tableData={tableData}
                    tableName="posts"
                    searchableColumns={searchableColumns}
                    debounceMs={300}
                    partialReloadKey="tableData"
                    emptyState={
                        <div className="flex flex-col items-center justify-center gap-4 py-16 text-center">
                            <div className="rounded-full bg-muted p-4">
                                <FileText className="size-8 text-muted-foreground" />
                            </div>
                            <div>
                                <p className="font-medium">No posts found</p>
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
                        quickViews: true,
                        customQuickViews: true,
                        exports: true,
                        filters: true,
                        density: true,
                        copyCell: true,
                        emptyStateIllustration: true,
                        keyboardNavigation: true,
                        shortcutsOverlay: true,
                    }}
                    translations={{
                        noData: 'No posts',
                        search: 'Search posts',
                        clearAllFilters: 'Clear all filters',
                        density: 'Row density',
                        selectAllMatching: (count) =>
                            `Select all ${count} posts`,
                    }}
                />
            </div>
        </AppSidebarLayout>
    );
}
