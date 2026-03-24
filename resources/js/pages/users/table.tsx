import { DataTable } from '@/components/data-table/data-table';
import type {
    DataTableApiRef,
    DataTableResponse,
} from '@/components/data-table/types';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import type { BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/react';
import { Building2, Users } from 'lucide-react';
import type { ReactNode } from 'react';
import { useEffect, useRef, useState } from 'react';
import { bulkActions, createHeaderActions, createRowActions } from './columns';
import {
    KeyboardShortcutsDialog,
    SendMessageDialog,
    type SendMessageDialogState,
} from './table-toolbar';

export interface UsersTableRow {
    id: number;
    hash_id: string;
    name: string;
    email: string;
    avatar: string | null;
    profile_url: string | null;
    status: string;
    onboarding_completed: boolean;
    organizations_count: number;
    first_organization_name: string | null;
    created_at: string | null;
    updated_at: string | null;
}

interface DataTableAiProps {
    aiBaseUrl: string | null;
    thesysEnabled: boolean;
}

interface Props {
    tableData?: DataTableResponse<UsersTableRow>;
    searchableColumns?: string[];
    dataTableAi?: DataTableAiProps;
    batchEditAllowedColumns?: string[];
    realtimeChannel?: string;
    presenceChannel?: string;
}

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Users', href: '/users' }];

export default function UsersTablePage({
    tableData,
    searchableColumns: _searchableColumns = [],
    dataTableAi,
    batchEditAllowedColumns: _batchEditAllowedColumns = [],
    realtimeChannel,
    presenceChannel,
}: Props) {
    const { auth } = usePage<{ auth: { user: { id: number; name: string; avatar: string | null } | null } }>().props;
    const apiRef = useRef<DataTableApiRef | null>(null);
    const [shortcutsOpen, setShortcutsOpen] = useState(false);
    const [messageDialog, setMessageDialog] = useState<SendMessageDialogState | null>(null);

    useEffect(() => {
        const onKey = (e: KeyboardEvent) => {
            if (e.key === '?' && !e.ctrlKey && !e.metaKey && !e.altKey) {
                const target = e.target as HTMLElement;
                if (
                    target.tagName !== 'INPUT' &&
                    target.tagName !== 'TEXTAREA' &&
                    !target.isContentEditable
                ) {
                    setShortcutsOpen((v) => !v);
                    e.preventDefault();
                }
            }
        };
        window.addEventListener('keydown', onKey);
        return () => window.removeEventListener('keydown', onKey);
    }, []);

    const rowActions = createRowActions((row) => {
        setMessageDialog({ row, subject: '', body: '' });
    });
    const headerActions = createHeaderActions(apiRef);

    return (
        <AppSidebarLayout breadcrumbs={breadcrumbs}>
            <Head title="Users" />
            <div
                className="flex h-full flex-1 flex-col gap-4 p-4"
                data-pan="users-table"
            >
                <div>
                    <h1 className="text-2xl font-bold tracking-tight">Users</h1>
                    {!dataTableAi?.aiBaseUrl && !dataTableAi?.thesysEnabled && (
                        <p className="mt-1 text-xs text-muted-foreground">
                            To enable AI features: set <code className="rounded bg-muted px-1 py-0.5 text-[11px]">THESYS_API_KEY</code> and an AI provider in .env or Filament → Settings · Integrations → AI.
                        </p>
                    )}
                </div>
                <DataTable<UsersTableRow>
                    tableData={tableData}
                    tableName="users"
                    debounceMs={300}
                    partialReloadKey="tableData"
                    aiBaseUrl={dataTableAi?.aiBaseUrl ?? undefined}
                    aiThesys={dataTableAi?.thesysEnabled ?? false}
                    groupByOptions={['status', 'onboarding_completed', 'first_organization_name']}
                    kanbanColumnId="status"
                    cardTitleColumn="name"
                    cardSubtitleColumn="email"
                    cardImageColumn="avatar"
                    chartTypes={['bar', 'line', 'pie', 'doughnut']}
                    selectionMode="checkbox"
                    sparklineData={(tableData as DataTableResponse<UsersTableRow> & { sparklineData?: Record<string, number[][]> }).sparklineData}
                    onKanbanMove={async (rowId, _fromLane, toLane) => {
                        await router.patch(`/users/${rowId}`, { status: toLane });
                    }}
                    rowLink={(row) => `/users/${row.hash_id}`}
                    rowClassName={(row) =>
                        row.status === 'deleted' ? 'opacity-60 line-through-none' : ''
                    }
                    rowDataAttributes={(row) => ({
                        'data-user-id': String(row.id),
                        'data-status': row.status,
                        'data-onboarded': String(row.onboarding_completed),
                    })}
                    renderHeader={{
                        organizations_count: (
                            <span className="flex items-center gap-1">
                                <Building2 className="h-3 w-3" />
                                Orgs
                            </span>
                        ),
                    }}
                    onClipboardPaste={async (startRowIdx, startColId, data) => {
                        const editableCols = ['name', 'email'];
                        if (!editableCols.includes(startColId)) return;
                        const patches = data
                            .map((rowData, i) => ({ rowIdx: startRowIdx + i, value: rowData[0] }))
                            .filter((p) => p.value !== undefined);
                        if (patches.length === 0) return;
                        // clipboard paste handled
                    }}
                    onDragToFill={async (columnId, value, targetRowIds) => {
                        const editableCols = ['name', 'email', 'onboarding_completed'];
                        if (!editableCols.includes(columnId)) return;
                        await router.patch('/users/batch-update', {
                            ids: targetRowIds.map(Number),
                            column: columnId,
                            value: String(value),
                        });
                    }}
                    onFindReplace={async (rowId, columnId, _oldValue, newValue) => {
                        await router.patch('/users/batch-update', {
                            ids: [Number(rowId)],
                            column: columnId,
                            value: String(newValue),
                        });
                    }}
                    emptyState={
                        <div className="flex flex-col items-center justify-center gap-4 py-16 text-center">
                            <div className="rounded-full bg-muted p-4">
                                <Users className="size-8 text-muted-foreground" />
                            </div>
                            <div>
                                <p className="font-medium">No users found</p>
                                <p className="text-sm text-muted-foreground">
                                    Try adjusting your search or filters.
                                </p>
                            </div>
                        </div>
                    }
                    actions={rowActions}
                    bulkActions={bulkActions}
                    headerActions={headerActions}
                    renderDetailRow={(row): ReactNode => {
                        const d = (row as UsersTableRow & {
                            email_verified_at?: string | null;
                            updated_at?: string | null;
                            organizations_count?: number;
                        });
                        return (
                            <div className="grid grid-cols-2 gap-4 p-4 text-sm md:grid-cols-3">
                                {d?.email_verified_at != null && (
                                    <div className="space-y-0.5">
                                        <p className="text-xs font-medium text-muted-foreground">
                                            Email verified
                                        </p>
                                        <p>
                                            {new Date(
                                                String(d.email_verified_at),
                                            ).toLocaleString()}
                                        </p>
                                    </div>
                                )}
                                {d?.updated_at != null && (
                                    <div className="space-y-0.5">
                                        <p className="text-xs font-medium text-muted-foreground">
                                            Last updated
                                        </p>
                                        <p>
                                            {new Date(
                                                String(d.updated_at),
                                            ).toLocaleString()}
                                        </p>
                                    </div>
                                )}
                                {d?.organizations_count != null && (
                                    <div className="space-y-0.5">
                                        <p className="text-xs font-medium text-muted-foreground">
                                            Organizations
                                        </p>
                                        <p>
                                            {d.organizations_count}{' '}
                                            {d.organizations_count === 1
                                                ? 'org'
                                                : 'orgs'}
                                        </p>
                                    </div>
                                )}
                            </div>
                        );
                    }}
                    apiRef={apiRef}
                    onStateChange={(_state) => {
                        // state change callback — could log or sync externally
                    }}
                    onGroupByChange={(columnId) => {
                        // group-by change callback
                        if (columnId) {
                            // group-by changed
                        }
                    }}
                    renderFooterCell={(columnId, value) => {
                        if (columnId === 'organizations_count' && typeof value === 'number') {
                            return (
                                <span className="font-semibold tabular-nums text-primary">
                                    {value.toLocaleString()} total
                                </span>
                            );
                        }
                        if (columnId === 'id' && typeof value === 'string') {
                            return (
                                <span className="text-xs font-medium text-muted-foreground">
                                    {value}
                                </span>
                            );
                        }
                        return undefined;
                    }}
                    onInlineEdit={() => {
                        router.reload({ only: ['tableData'] });
                    }}
                    onBatchEdit={async (rows, columnId, value) => {
                        await router.patch('/users/batch-update', {
                            ids: rows.map((r) => r.id),
                            column: columnId,
                            value:
                                typeof value === 'boolean'
                                    ? value
                                    : String(value),
                        });
                    }}
                    options={{
                        quickViews: true,
                        customQuickViews: true,
                        exports: true,
                        filters: true,
                        columnVisibility: true,
                        columnOrdering: true,
                        columnResizing: true,
                        loading: true,
                        keyboardNavigation: true,
                        density: true,
                        copyCell: true,
                        contextMenu: true,
                        searchHighlight: true,
                        undoRedo: true,
                        columnPinning: true,
                        persistSelection: true,
                        shortcutsOverlay: true,
                        exportProgress: true,
                        emptyStateIllustration: true,
                        columnAutoSize: true,
                        columnVirtualization: true,
                        autoSizer: true,
                        cellMeasurer: true,
                        scrollAwareRendering: true,
                        directionalOverscan: true,
                        layoutSwitcher: true,
                        facetedFilters: true,
                        presence: true,
                        kanbanView: true,
                        integratedCharts: true,
                        virtualScrolling: false,
                        rowGrouping: true,
                        batchEdit: true,
                        findReplace: true,
                        printable: true,
                        rowReorder: true,
                        columnStatistics: true,
                        conditionalFormatting: true,
                        statusBar: true,
                        cellRangeSelection: true,
                        cellFlashing: true,
                        clipboardPaste: true,
                        dragToFill: true,
                        headerFilters: true,
                        masterDetail: false,
                        stickyHeader: true,
                        // Disabled features
                        spreadsheetMode: false,
                        infiniteScroll: false,
                        windowScroller: false,
                    }}
                    realtimeChannel={realtimeChannel}
                    realtimeEvent=".user.updated"
                    presenceChannel={presenceChannel}
                    currentUser={
                        auth?.user
                            ? {
                                  id: auth.user.id,
                                  name: auth.user.name,
                                  avatar: auth.user.avatar ?? undefined,
                              }
                            : undefined
                    }
                    mobileBreakpoint={768}
                    slots={{
                        toolbar: (
                            <KeyboardShortcutsDialog
                                open={shortcutsOpen}
                                onOpenChange={setShortcutsOpen}
                            />
                        ),
                    }}
                    translations={{
                        noData: 'No users',
                        search: 'Search users',
                        clearAllFilters: 'Clear all filters',
                        density: 'Row density',
                        selectAllMatching: (count) =>
                            `Select all ${count} users`,
                    }}
                />
            </div>

            <SendMessageDialog
                state={messageDialog}
                onStateChange={setMessageDialog}
            />
        </AppSidebarLayout>
    );
}
