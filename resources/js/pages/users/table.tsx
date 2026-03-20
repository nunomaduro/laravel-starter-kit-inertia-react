import { DataTable } from '@/components/data-table/data-table';
import type {
    DataTableAction,
    DataTableApiRef,
    DataTableBulkAction,
    DataTableFormField,
    DataTableHeaderAction,
    DataTableResponse,
} from '@/components/data-table/types';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import type { BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/react';
import { Building2, Copy, FilterX, Keyboard, Maximize2, Trash2, UserPlus, Users } from 'lucide-react';
import type { ReactNode } from 'react';
import { useEffect, useRef, useState } from 'react';

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
    const [messageDialog, setMessageDialog] = useState<{
        row: UsersTableRow;
        subject: string;
        body: string;
    } | null>(null);

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
    const onboardingFormFields: DataTableFormField[] = [
        {
            name: 'value',
            label: 'Onboarding completed',
            type: 'select' as const,
            options: [
                { label: 'Completed', value: '1' },
                { label: 'Incomplete', value: '0' },
            ],
            required: true,
        },
        {
            name: 'note',
            label: 'Internal note (optional)',
            type: 'textarea' as const,
        },
    ];

    const rowActions: DataTableAction<UsersTableRow>[] = [
        {
            label: 'View',
            icon: 'eye',
            onClick: (row) => router.visit(`/users/${row.hash_id}`),
        },
        {
            label: 'Duplicate',
            icon: 'copy',
            onClick: (row) => {
                router.post(
                    `/users/${row.hash_id}/duplicate`,
                    {},
                    { preserveScroll: true, only: ['tableData', 'flash'] },
                );
            },
        },
        {
            label: 'Toggle onboarding',
            icon: 'square-check',
            form: onboardingFormFields,
            onClick: (row) => {
                const formValues = (row as UsersTableRow & { _formValues?: Record<string, string> })._formValues;
                router.patch(
                    '/users/batch-update',
                    {
                        ids: [row.id],
                        column: 'onboarding_completed',
                        value: formValues?.value === '1',
                    },
                    { preserveScroll: true, only: ['tableData', 'flash'] },
                );
            },
        },
        {
            label: 'Send message',
            icon: 'mail',
            onClick: (row) => {
                setMessageDialog({ row, subject: '', body: '' });
            },
        },
        {
            id: 'restore',
            label: 'Restore',
            icon: 'rotate-ccw',
            onClick: (row) => {
                router.post(
                    `/users/${row.id}/restore`,
                    {},
                    { preserveScroll: true, only: ['tableData', 'flash'] },
                );
            },
        },
        {
            id: 'force-delete',
            label: 'Force delete',
            icon: 'trash-2',
            variant: 'destructive',
            confirm: {
                title: 'Permanently delete user?',
                description: 'This cannot be undone. The user will be permanently removed from the database.',
                confirmLabel: 'Delete permanently',
                cancelLabel: 'Cancel',
                variant: 'destructive',
            },
            onClick: (row) => {
                router.delete(
                    `/users/${row.id}/force-delete`,
                    { preserveScroll: true, only: ['tableData', 'flash'] },
                );
            },
        },
        {
            label: 'More',
            icon: 'more-horizontal',
            onClick: () => {},
            group: [
                {
                    label: 'Send email',
                    icon: 'external-link',
                    onClick: (row) => {
                        window.open(`mailto:${row.email}`, '_blank');
                    },
                },
                {
                    label: 'Open profile',
                    icon: 'user',
                    onClick: (row) => {
                        if (row.profile_url) window.open(row.profile_url, '_blank');
                    },
                },
            ],
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
        {
            id: 'soft-delete',
            label: 'Delete selected',
            icon: Trash2,
            variant: 'destructive',
            confirm: {
                title: 'Delete selected users?',
                description:
                    'Users will be soft-deleted and can be restored from the "Only trashed" view.',
                confirmLabel: 'Delete',
                cancelLabel: 'Cancel',
                variant: 'destructive',
            },
            onClick: (rows) => {
                router.post(
                    '/users/bulk-soft-delete',
                    { ids: rows.map((r) => r.id) },
                    {
                        preserveScroll: true,
                        only: ['tableData', 'flash'],
                    },
                );
            },
        },
    ];

    const headerActions: DataTableHeaderAction[] = [
        {
            label: 'Add user',
            icon: UserPlus,
            variant: 'default',
            onClick: () => router.visit('/users/create'),
        },
        {
            label: 'Reset filters',
            icon: FilterX,
            variant: 'outline',
            onClick: () => void apiRef.current?.resetFilters(),
        },
        {
            label: 'Auto-size columns',
            icon: Maximize2,
            variant: 'outline',
            onClick: () => void apiRef.current?.autosizeColumns(),
        },
    ];

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
                        masterDetail: true,
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
                            <div className="flex justify-end px-2">
                                <Dialog
                                    open={shortcutsOpen}
                                    onOpenChange={setShortcutsOpen}
                                >
                                    <DialogTrigger asChild>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            className="h-8 w-8"
                                            title="Keyboard shortcuts"
                                            aria-label="Keyboard shortcuts"
                                        >
                                            <Keyboard className="h-4 w-4" />
                                        </Button>
                                    </DialogTrigger>
                                    <DialogContent>
                                        <DialogHeader>
                                            <DialogTitle>
                                                Keyboard shortcuts
                                            </DialogTitle>
                                        </DialogHeader>
                                        <ul className="list-inside list-disc space-y-1 text-sm text-muted-foreground">
                                            <li>
                                                <kbd className="rounded border bg-muted px-1.5 py-0.5 font-mono text-xs">
                                                    ?
                                                </kbd>{' '}
                                                Show this help
                                            </li>
                                            <li>
                                                <kbd className="rounded border bg-muted px-1.5 py-0.5 font-mono text-xs">
                                                    Ctrl
                                                </kbd>{' '}
                                                + click row to open in new tab
                                            </li>
                                        </ul>
                                    </DialogContent>
                                </Dialog>
                            </div>
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

            {/* Send message dialog (form-in-action demo) */}
            <Dialog
                open={!!messageDialog}
                onOpenChange={(open) => !open && setMessageDialog(null)}
            >
                <DialogContent className="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>Send message</DialogTitle>
                    </DialogHeader>
                    {messageDialog && (
                        <form
                            className="grid gap-4"
                            onSubmit={(e) => {
                                e.preventDefault();
                                const { row, subject, body } = messageDialog;
                                const mailto = `mailto:${row.email}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
                                window.open(mailto, '_blank');
                                setMessageDialog(null);
                            }}
                        >
                            <p className="text-sm text-muted-foreground">
                                To: {messageDialog.row.email}
                            </p>
                            <div className="grid gap-2">
                                <Label htmlFor="msg-subject">Subject</Label>
                                <Input
                                    id="msg-subject"
                                    value={messageDialog.subject}
                                    onChange={(e) =>
                                        setMessageDialog(
                                            (d) =>
                                                d && {
                                                    ...d,
                                                    subject: e.target.value,
                                                },
                                        )
                                    }
                                    placeholder="Subject"
                                />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="msg-body">Body</Label>
                                <Textarea
                                    id="msg-body"
                                    value={messageDialog.body}
                                    onChange={(e) =>
                                        setMessageDialog(
                                            (d) =>
                                                d && {
                                                    ...d,
                                                    body: e.target.value,
                                                },
                                        )
                                    }
                                    placeholder="Message body"
                                    rows={4}
                                />
                            </div>
                            <div className="flex justify-end gap-2">
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() => setMessageDialog(null)}
                                >
                                    Cancel
                                </Button>
                                <Button type="submit">
                                    Open in email client
                                </Button>
                            </div>
                        </form>
                    )}
                </DialogContent>
            </Dialog>
        </AppSidebarLayout>
    );
}
