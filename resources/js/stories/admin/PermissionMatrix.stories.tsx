import type { Meta, StoryObj } from '@storybook/react';
import React from 'react';

import { PermissionMatrix } from '@/components/admin/permission-matrix';
import type { Permission, PermissionGrant, PermissionRole } from '@/components/admin/permission-matrix';

const ROLES: PermissionRole[] = [
    { id: 'admin', name: 'Admin', description: 'Full access', color: 'destructive' },
    { id: 'editor', name: 'Editor', description: 'Can edit content', color: 'primary' },
    { id: 'viewer', name: 'Viewer', description: 'Read-only access', color: 'secondary' },
];

const PERMISSIONS: Permission[] = [
    { id: 'users.view', name: 'View', resource: 'Users' },
    { id: 'users.create', name: 'Create', resource: 'Users' },
    { id: 'users.edit', name: 'Edit', resource: 'Users' },
    { id: 'users.delete', name: 'Delete', resource: 'Users' },
    { id: 'posts.view', name: 'View', resource: 'Posts' },
    { id: 'posts.create', name: 'Create', resource: 'Posts' },
    { id: 'posts.edit', name: 'Edit', resource: 'Posts' },
    { id: 'posts.delete', name: 'Delete', resource: 'Posts' },
    { id: 'settings.view', name: 'View', resource: 'Settings' },
    { id: 'settings.edit', name: 'Edit', resource: 'Settings' },
];

const GRANTS: PermissionGrant = {
    admin: {
        'users.view': true, 'users.create': true, 'users.edit': true, 'users.delete': true,
        'posts.view': true, 'posts.create': true, 'posts.edit': true, 'posts.delete': true,
        'settings.view': true, 'settings.edit': true,
    },
    editor: {
        'users.view': true, 'users.create': false, 'users.edit': true, 'users.delete': false,
        'posts.view': true, 'posts.create': true, 'posts.edit': true, 'posts.delete': false,
        'settings.view': true, 'settings.edit': false,
    },
    viewer: {
        'users.view': true, 'users.create': false, 'users.edit': false, 'users.delete': false,
        'posts.view': true, 'posts.create': false, 'posts.edit': false, 'posts.delete': false,
        'settings.view': false, 'settings.edit': false,
    },
};

const meta: Meta<typeof PermissionMatrix> = {
    title: 'Admin/PermissionMatrix',
    component: PermissionMatrix,
    tags: ['autodocs'],
    parameters: { layout: 'centered' },
    argTypes: {
        readonly: { control: 'boolean' },
    },
};

export default meta;
type Story = StoryObj<typeof PermissionMatrix>;

export const Editable: Story = {
    args: { roles: ROLES, permissions: PERMISSIONS, grants: GRANTS },
    render: (args) => <div className="w-[700px]"><PermissionMatrix {...args} /></div>,
};

export const Readonly: Story = {
    args: { roles: ROLES, permissions: PERMISSIONS, grants: GRANTS, readonly: true },
    render: (args) => <div className="w-[700px]"><PermissionMatrix {...args} /></div>,
};
