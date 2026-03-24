import type {
    DataTableAction,
    DataTableBulkAction,
    DataTableFormField,
    DataTableHeaderAction,
} from '@/components/data-table/types';
import { router } from '@inertiajs/react';
import { Copy, FilterX, Maximize2, Trash2, UserPlus } from 'lucide-react';
import type { RefObject } from 'react';
import type { UsersTableRow } from './table';

import type { DataTableApiRef } from '@/components/data-table/types';

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

export function createRowActions(
    onSendMessage: (row: UsersTableRow) => void,
): DataTableAction<UsersTableRow>[] {
    return [
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
                onSendMessage(row);
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
}

export const bulkActions: DataTableBulkAction<UsersTableRow>[] = [
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

export function createHeaderActions(
    apiRef: RefObject<DataTableApiRef | null>,
): DataTableHeaderAction[] {
    return [
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
}
