import { DataTable } from '@/components/data-table/data-table';
import type {
    DataTableAction,
    DataTableBulkAction,
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
import { Skeleton } from '@/components/ui/skeleton';
import { Textarea } from '@/components/ui/textarea';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { Copy, Keyboard, Trash2, UserPlus, Users } from 'lucide-react';
import type { ReactNode } from 'react';
import { useEffect, useState } from 'react';

export interface UsersTableRow {
    id: number;
    hash_id: string;
    name: string;
    email: string;
    avatar: string | null;
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
}

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Users', href: '/users' }];

export default function UsersTablePage({
    tableData,
    searchableColumns: _searchableColumns = [],
    dataTableAi,
    batchEditAllowedColumns: _batchEditAllowedColumns = [],
}: Props) {
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
    const rowActions: DataTableAction<UsersTableRow>[] = [
        {
            label: 'View',
            onClick: (row) => router.visit(`/users/${row.hash_id}`),
        },
        {
            label: 'Duplicate',
            onClick: (row) => {
                router.post(
                    `/users/${row.hash_id}/duplicate`,
                    {},
                    { preserveScroll: true, only: ['tableData', 'flash'] },
                );
            },
        },
        {
            label: 'Send message',
            onClick: (row) => {
                setMessageDialog({ row, subject: '', body: '' });
            },
        },
        {
            label: 'More',
            onClick: () => {},
            group: [
                {
                    label: 'Send email',
                    onClick: (row) => {
                        window.open(`mailto:${row.email}`, '_blank');
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
    ];

    if (!tableData) {
        return (
            <AppSidebarLayout breadcrumbs={breadcrumbs}>
                <Head title="Users" />
                <div
                    className="flex h-full flex-1 flex-col gap-4 p-4"
                    data-pan="users-table"
                >
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">
                            Users
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
                    <p className="mt-1 text-xs text-muted-foreground">
                        Quick views = &quot;All&quot; dropdown. Columns = Colonnes. Export = Exporter.
                        {!dataTableAi?.aiBaseUrl && !dataTableAi?.thesysEnabled && (
                            <> To enable AI and Thesys Visualize: set <code className="rounded bg-muted px-1 py-0.5 text-[11px]">THESYS_API_KEY</code> and an AI provider in .env or Filament → Settings · Integrations → AI.</>
                        )}
                    </p>
                </div>
                {tableData.analytics && tableData.analytics.length > 0 && (
                    <div className="grid grid-cols-2 gap-3 py-2 md:grid-cols-4">
                        {tableData.analytics.map((card) => (
                            <div
                                key={card.label}
                                className="rounded-lg border bg-card p-3 text-card-foreground shadow-sm"
                            >
                                <div className="flex items-center justify-between gap-2">
                                    <span className="text-xs font-medium text-muted-foreground">
                                        {card.label}
                                    </span>
                                    {card.icon && (
                                        <span className="text-sm">
                                            {card.icon}
                                        </span>
                                    )}
                                </div>
                                <p className="mt-1 text-2xl font-semibold tabular-nums">
                                    {typeof card.value === 'number' &&
                                    card.format === 'number'
                                        ? card.value.toLocaleString()
                                        : card.value}
                                </p>
                                {card.description && (
                                    <p className="text-xs text-muted-foreground">
                                        {card.description}
                                    </p>
                                )}
                            </div>
                        ))}
                    </div>
                )}
                <DataTable<UsersTableRow>
                    tableData={tableData}
                    tableName="users"
                    debounceMs={300}
                    partialReloadKey="tableData"
                    aiBaseUrl={dataTableAi?.aiBaseUrl ?? undefined}
                    aiThesys={dataTableAi?.thesysEnabled ?? false}
                    groupByOptions={['status', 'onboarding_completed']}
                    kanbanColumnId="status"
                    cardTitleColumn="name"
                    cardSubtitleColumn="email"
                    onKanbanMove={async (rowId, _fromLane, toLane) => {
                        await router.patch(`/users/${rowId}`, { status: toLane });
                    }}
                    rowLink={(row) => `/users/${row.hash_id}`}
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
                        stickyHeader: true,
                        globalSearch: true,
                        loading: true,
                        keyboardNavigation: true,
                        printable: true,
                        density: true,
                        copyCell: true,
                        contextMenu: true,
                        rowGrouping: true,
                        rowReorder: true,
                        batchEdit: true,
                        searchHighlight: true,
                        undoRedo: true,
                        columnPinning: true,
                        persistSelection: true,
                        shortcutsOverlay: true,
                        exportProgress: true,
                        emptyStateIllustration: true,
                        cellFlashing: true,
                        statusBar: true,
                        clipboardPaste: true,
                        dragToFill: true,
                        headerFilters: true,
                        infiniteScroll: false,
                        columnAutoSize: true,
                        columnVirtualization: true,
                        cellRangeSelection: true,
                        autoSizer: true,
                        cellMeasurer: true,
                        scrollAwareRendering: true,
                        windowScroller: true,
                        directionalOverscan: true,
                        layoutSwitcher: true,
                        columnStatistics: true,
                        conditionalFormatting: true,
                        facetedFilters: true,
                        presence: false,
                        spreadsheetMode: true,
                        kanbanView: true,
                        masterDetail: false,
                        integratedCharts: true,
                        findReplace: true,
                        virtualScrolling: true,
                    }}
                    mobileBreakpoint={768}
                    slots={{
                        ...(tableData.analytics && tableData.analytics.length > 0
                            ? {
                                  beforeTable: (
                                      <div className="grid grid-cols-2 gap-3 py-2 md:grid-cols-4">
                                          {tableData.analytics.map((card) => (
                                              <div
                                                  key={card.label}
                                                  className="rounded-lg border bg-card p-3 text-card-foreground shadow-sm"
                                              >
                                                  <div className="flex items-center justify-between gap-2">
                                                      <span className="text-xs font-medium text-muted-foreground">
                                                          {card.label}
                                                      </span>
                                                      {card.icon && (
                                                          <span className="text-sm">
                                                              {card.icon}
                                                          </span>
                                                      )}
                                                  </div>
                                                  <p className="mt-1 text-2xl font-semibold tabular-nums">
                                                      {typeof card.value === 'number' &&
                                                      card.format === 'number'
                                                          ? card.value.toLocaleString()
                                                          : card.value}
                                                  </p>
                                                  {card.description && (
                                                      <p className="text-xs text-muted-foreground">
                                                          {card.description}
                                                      </p>
                                                  )}
                                              </div>
                                          ))}
                                      </div>
                                  ),
                              }
                            : {}),
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
