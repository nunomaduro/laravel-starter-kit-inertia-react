import type { Meta, StoryObj } from '@storybook/react';
import React from 'react';

import { AuditLogViewer } from '@/components/admin/audit-log-viewer';
import type { AuditLogEntry } from '@/components/admin/audit-log-viewer';

const MOCK_ENTRIES: AuditLogEntry[] = [
    {
        id: '1',
        actor: { id: 'u1', name: 'Jane Doe', email: 'jane@example.com' },
        action: 'user.created',
        target: 'john@example.com',
        targetType: 'User',
        timestamp: new Date(Date.now() - 120000).toISOString(),
        variant: 'success',
    },
    {
        id: '2',
        actor: { id: 'u2', name: 'Admin Bot', email: 'bot@example.com' },
        action: 'role.updated',
        target: 'Editor',
        targetType: 'Role',
        timestamp: new Date(Date.now() - 600000).toISOString(),
        variant: 'info',
    },
    {
        id: '3',
        actor: { id: 'u1', name: 'Jane Doe', email: 'jane@example.com' },
        action: 'login.failed',
        target: 'jane@example.com',
        targetType: 'Auth',
        timestamp: new Date(Date.now() - 3600000).toISOString(),
        variant: 'error',
    },
    {
        id: '4',
        actor: null,
        action: 'settings.updated',
        target: 'MailSettings',
        targetType: 'Settings',
        timestamp: new Date(Date.now() - 7200000).toISOString(),
        variant: 'warning',
    },
    {
        id: '5',
        actor: { id: 'u3', name: 'Mark Lee', email: 'mark@example.com' },
        action: 'organization.deleted',
        target: 'Acme Corp',
        targetType: 'Organization',
        timestamp: new Date(Date.now() - 86400000).toISOString(),
        variant: 'error',
    },
];

const meta: Meta<typeof AuditLogViewer> = {
    title: 'Admin/AuditLogViewer',
    component: AuditLogViewer,
    tags: ['autodocs'],
    parameters: { layout: 'centered' },
    argTypes: {
        virtualized: { control: 'boolean' },
    },
};

export default meta;
type Story = StoryObj<typeof AuditLogViewer>;

export const Default: Story = {
    args: {
        entries: MOCK_ENTRIES,
        actionTypes: ['user.created', 'role.updated', 'login.failed', 'settings.updated', 'organization.deleted'],
    },
    render: (args) => <div className="w-[700px]"><AuditLogViewer {...args} /></div>,
};

export const Empty: Story = {
    args: { entries: [] },
    render: (args) => <div className="w-[700px]"><AuditLogViewer {...args} /></div>,
};
