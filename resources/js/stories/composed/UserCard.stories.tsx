import type { Meta, StoryObj } from '@storybook/react';
import React from 'react';

import { UserCard } from '@/components/composed/user-card';

const MOCK_USER = {
    id: 'u1',
    name: 'Jane Doe',
    email: 'jane@example.com',
    role: 'Senior Engineer',
    initials: 'JD',
    bio: 'Full-stack developer with 8 years of experience. Passionate about clean code and great UX.',
    status: 'online' as const,
    badges: ['Admin', 'Core Team'],
    stats: [
        { label: 'Projects', value: 24 },
        { label: 'Commits', value: '1.2k' },
        { label: 'Reviews', value: 148 },
    ],
};

const meta: Meta<typeof UserCard> = {
    title: 'Composed/UserCard',
    component: UserCard,
    tags: ['autodocs'],
    parameters: { layout: 'centered' },
    argTypes: {
        variant: { control: 'select', options: ['compact', 'default', 'detailed'] },
        isFollowing: { control: 'boolean' },
    },
};

export default meta;
type Story = StoryObj<typeof UserCard>;

export const Compact: Story = {
    args: {
        user: MOCK_USER,
        variant: 'compact',
    },
    render: (args) => <div className="w-72"><UserCard {...args} /></div>,
};

export const Default: Story = {
    args: {
        user: MOCK_USER,
        variant: 'default',
        onMessage: () => {},
        onFollow: () => {},
        onEmail: () => {},
    },
    render: (args) => <div className="w-72"><UserCard {...args} /></div>,
};

export const Detailed: Story = {
    args: {
        user: MOCK_USER,
        variant: 'detailed',
        onMessage: () => {},
        onFollow: () => {},
        onEmail: () => {},
        isFollowing: false,
    },
    render: (args) => <div className="w-72"><UserCard {...args} /></div>,
};

export const Offline: Story = {
    args: {
        user: { ...MOCK_USER, status: 'offline' },
        variant: 'default',
    },
    render: (args) => <div className="w-72"><UserCard {...args} /></div>,
};
