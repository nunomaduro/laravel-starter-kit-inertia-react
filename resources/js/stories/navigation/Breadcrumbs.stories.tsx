import type { Meta, StoryObj } from '@storybook/react';
import React from 'react';

import { Breadcrumbs } from '@/components/breadcrumbs';

const meta: Meta<typeof Breadcrumbs> = {
    title: 'Navigation/Breadcrumbs',
    component: Breadcrumbs,
    tags: ['autodocs'],
    parameters: { layout: 'centered' },
    argTypes: {
        breadcrumbs: { control: false },
    },
};

export default meta;
type Story = StoryObj<typeof Breadcrumbs>;

export const TwoLevels: Story = {
    args: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/' },
            { title: 'Settings', href: '/settings' },
        ],
    },
};

export const ThreeLevels: Story = {
    args: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/' },
            { title: 'Organizations', href: '/organizations' },
            { title: 'Acme Corp', href: '/organizations/1' },
        ],
    },
};

export const Deep: Story = {
    args: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/' },
            { title: 'Billing', href: '/billing' },
            { title: 'Invoices', href: '/billing/invoices' },
            { title: 'INV-2024-001', href: '/billing/invoices/1' },
        ],
    },
};
