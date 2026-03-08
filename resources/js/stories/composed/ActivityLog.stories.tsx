import type { Meta, StoryObj } from '@storybook/react';

import type { ActivityEntry } from '@/components/composed/activity-log';
import { ActivityLog } from '@/components/composed/activity-log';

const ENTRIES: ActivityEntry[] = [
    {
        id: '1',
        actor: { name: 'Jane Doe', initials: 'JD' },
        action: 'created a new project',
        target: 'Q4 Launch',
        targetUrl: '#',
        timestamp: new Date(Date.now() - 120000),
        type: 'project',
    },
    {
        id: '2',
        actor: { name: 'Mark Lee', initials: 'ML' },
        action: 'commented on',
        target: 'Homepage redesign',
        description: 'Looks great! Maybe we can adjust the hero section color.',
        timestamp: new Date(Date.now() - 600000),
        type: 'comment',
    },
    {
        id: '3',
        actor: { name: 'Admin Bot' },
        action: 'automatically closed',
        target: 'Issue #124',
        timestamp: new Date(Date.now() - 3600000),
        type: 'issue',
    },
    {
        id: '4',
        actor: { name: 'Sarah Kim', initials: 'SK' },
        action: 'invited',
        target: 'alex@example.com',
        description: 'Invited as a Viewer.',
        timestamp: new Date(Date.now() - 86400000),
        type: 'invite',
    },
    {
        id: '5',
        actor: { name: 'Jane Doe', initials: 'JD' },
        action: 'deleted',
        target: 'Old Archive',
        timestamp: new Date(Date.now() - 172800000),
        type: 'delete',
    },
];

const meta: Meta<typeof ActivityLog> = {
    title: 'Composed/ActivityLog',
    component: ActivityLog,
    tags: ['autodocs'],
    parameters: { layout: 'centered' },
    argTypes: {
        showFilters: { control: 'boolean' },
        isLoading: { control: 'boolean' },
        maxHeight: { control: { type: 'number' } },
    },
};

export default meta;
type Story = StoryObj<typeof ActivityLog>;

export const Default: Story = {
    args: {
        entries: ENTRIES,
        showFilters: false,
    },
    render: (args) => (
        <div className="w-[500px]">
            <ActivityLog {...args} />
        </div>
    ),
};

export const WithFilters: Story = {
    args: {
        entries: ENTRIES,
        showFilters: true,
        types: ['project', 'comment', 'issue', 'invite', 'delete'],
    },
    render: (args) => (
        <div className="w-[500px]">
            <ActivityLog {...args} />
        </div>
    ),
};

export const Loading: Story = {
    args: {
        entries: [],
        isLoading: true,
    },
    render: (args) => (
        <div className="w-[500px]">
            <ActivityLog {...args} />
        </div>
    ),
};

export const Empty: Story = {
    args: {
        entries: [],
        emptyMessage: 'No activity yet.',
    },
    render: (args) => (
        <div className="w-[500px]">
            <ActivityLog {...args} />
        </div>
    ),
};
