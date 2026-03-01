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
import { Head, router } from '@inertiajs/react';
import { Copy, Keyboard, Trash2, UserPlus, Users } from 'lucide-react';
import { useEffect, useState } from 'react';

export interface UsersTableRow {
    id: number;
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

interface Props {
    tableData?: DataTableResponse<UsersTableRow>;
    searchableColumns: string[];
}

export default function UsersTablePage({
    tableData,
    searchableColumns = [],
}: Props) {
    const [shortcutsOpen, setShortcutsOpen] = useState(false);
    const [messageDialog, setMessageDialog] = useState<{ row: UsersTableRow; subject: string; body: string } | null>(null);

    useEffect(() => {
        const onKey = (e: KeyboardEvent) => {
            if (e.key === '?' && !e.ctrlKey && !e.metaKey && !e.altKey) {
                const target = e.target as HTMLElement;
                if (target.tagName !== 'INPUT' && target.tagName !== 'TEXTAREA' && !target.isContentEditable) {
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
            onClick: (row) => router.visit(`/users/${row.id}`),
        },
        {
            label: 'Duplicate',
            onClick: (row) => {
                router.post(`/users/${row.id}/duplicate`, {}, { preserveScroll: true, only: ['tableData', 'flash'] });
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
                        window.location.href = `mailto:${row.email}`;
                    },
                },
                {
                    label: 'Log activity',
                    onClick: (row) => {
                        console.info('Log activity for user', row.id);
                    },
                },
            ],
        },
        {
            label: 'Deactivate',
            variant: 'destructive',
            confirm: {
                title: 'Deactivate user?',
                description: 'They will no longer be able to sign in until reactivated.',
                confirmLabel: 'Deactivate',
                cancelLabel: 'Cancel',
                variant: 'destructive',
            },
            onClick: (row) => {
                console.info('Deactivate user', row.id);
            },
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
                description: 'Users will be soft-deleted and can be restored from the "Only trashed" view.',
                confirmLabel: 'Delete',
                cancelLabel: 'Cancel',
                variant: 'destructive',
            },
            onClick: (rows) => {
                router.post('/users/bulk-soft-delete', { ids: rows.map((r) => r.id) }, {
                    preserveScroll: true,
                    only: ['tableData', 'flash'],
                });
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
            <AppSidebarLayout>
                <Head title="Users" />
                <div className="flex h-full flex-1 flex-col gap-4 p-4" data-pan="users-table">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Users</h1>
                        <Skeleton className="mt-1 h-5 w-24" />
                    </div>
                    <Skeleton className="h-[400px] w-full rounded-md" />
                </div>
            </AppSidebarLayout>
        );
    }

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
                    partialReloadKey="tableData"
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
                    headerActions={headerActions}
                    renderDetailRow={(row, detail) => (
                        <div className="grid grid-cols-2 gap-4 p-4 text-sm">
                            {detail?.email_verified_at != null && (
                                <div>
                                    <span className="font-medium text-muted-foreground">Email verified</span>
                                    <p>{String(detail.email_verified_at)}</p>
                                </div>
                            )}
                            {detail?.updated_at != null && (
                                <div>
                                    <span className="font-medium text-muted-foreground">Last updated</span>
                                    <p>{String(detail.updated_at)}</p>
                                </div>
                            )}
                            {detail?.organizations_count != null && (
                                <div>
                                    <span className="font-medium text-muted-foreground">Organizations</span>
                                    <p>{String(detail.organizations_count)}</p>
                                </div>
                            )}
                        </div>
                    )}
                    onInlineEdit={() => {
                        router.reload({ only: ['tableData'] });
                    }}
                    options={{
                        stickyHeader: true,
                        globalSearch: true,
                        columnVisibility: true,
                        columnOrdering: true,
                        columnResizing: true,
                        columnPinning: true,
                        quickViews: true,
                        customQuickViews: true,
                        exports: true,
                        filters: true,
                        density: 'comfortable',
                        copyCell: true,
                        emptyStateIllustration: true,
                        rowGrouping: true,
                        contextMenu: true,
                        batchEdit: true,
                        printable: true,
                        undoRedo: true,
                        persistSelection: true,
                        keyboardNavigation: true,
                        searchHighlight: true,
                        shortcutsOverlay: true,
                    }}
                    slots={{
                        toolbar: (
                            <div className="flex justify-end px-2">
                                <Dialog open={shortcutsOpen} onOpenChange={setShortcutsOpen}>
                                    <DialogTrigger asChild>
                                        <Button variant="ghost" size="icon" className="h-8 w-8" title="Keyboard shortcuts">
                                            <Keyboard className="h-4 w-4" />
                                        </Button>
                                    </DialogTrigger>
                                    <DialogContent>
                                        <DialogHeader>
                                            <DialogTitle>Keyboard shortcuts</DialogTitle>
                                        </DialogHeader>
                                        <ul className="list-inside list-disc space-y-1 text-sm text-muted-foreground">
                                            <li><kbd className="rounded border bg-muted px-1.5 py-0.5 font-mono text-xs">?</kbd> Show this help</li>
                                            <li><kbd className="rounded border bg-muted px-1.5 py-0.5 font-mono text-xs">Ctrl</kbd> + click row to open in new tab</li>
                                        </ul>
                                    </DialogContent>
                                </Dialog>
                            </div>
                        ),
                        beforeTable: (
                            <p className="px-2 py-1 text-xs text-muted-foreground">
                                Full usage example: filters, density, select-all, row actions with confirm and groups, slots, translations.
                            </p>
                        ),
                    }}
                    mobileBreakpoint={768}
                    translations={{
                        noData: 'No users',
                        search: 'Search users',
                        clearFilters: 'Clear all filters',
                        density: 'Row density',
                        selectAllMatching: (count) => `Select all ${count} users`,
                    }}
                />
            </div>

            {/* Send message dialog (form-in-action demo) */}
            <Dialog open={!!messageDialog} onOpenChange={(open) => !open && setMessageDialog(null)}>
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
                                window.location.href = mailto;
                                setMessageDialog(null);
                            }}
                        >
                            <p className="text-sm text-muted-foreground">To: {messageDialog.row.email}</p>
                            <div className="grid gap-2">
                                <Label htmlFor="msg-subject">Subject</Label>
                                <Input
                                    id="msg-subject"
                                    value={messageDialog.subject}
                                    onChange={(e) => setMessageDialog((d) => d && { ...d, subject: e.target.value })}
                                    placeholder="Subject"
                                />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="msg-body">Body</Label>
                                <Textarea
                                    id="msg-body"
                                    value={messageDialog.body}
                                    onChange={(e) => setMessageDialog((d) => d && { ...d, body: e.target.value })}
                                    placeholder="Message body"
                                    rows={4}
                                />
                            </div>
                            <div className="flex justify-end gap-2">
                                <Button type="button" variant="outline" onClick={() => setMessageDialog(null)}>
                                    Cancel
                                </Button>
                                <Button type="submit">Open in email client</Button>
                            </div>
                        </form>
                    )}
                </DialogContent>
            </Dialog>
        </AppSidebarLayout>
    );
}
