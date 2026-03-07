import type { Meta, StoryObj } from '@storybook/react';
import React from 'react';

import { UsageMeter } from '@/components/saas/usage-meter';

const meta: Meta<typeof UsageMeter> = {
    title: 'SaaS/UsageMeter',
    component: UsageMeter,
    tags: ['autodocs'],
    parameters: { layout: 'centered' },
    argTypes: {
        used: { control: { type: 'range', min: 0, max: 100 } },
        limit: { control: { type: 'number' } },
        label: { control: 'text' },
        unit: { control: 'text' },
    },
};

export default meta;
type Story = StoryObj<typeof UsageMeter>;

export const Normal: Story = {
    args: { used: 5, limit: 10, label: 'Team seats', unit: 'seats' },
    render: (args) => <div className="w-72"><UsageMeter {...args} /></div>,
};

export const Warning: Story = {
    args: { used: 9, limit: 10, label: 'Team seats', unit: 'seats' },
    render: (args) => <div className="w-72"><UsageMeter {...args} /></div>,
};

export const AtLimit: Story = {
    args: { used: 10, limit: 10, label: 'Team seats', unit: 'seats' },
    render: (args) => <div className="w-72"><UsageMeter {...args} /></div>,
};

export const Multiple: Story = {
    render: () => (
        <div className="space-y-6 w-80">
            <UsageMeter used={5} limit={10} label="Team seats" unit="seats" />
            <UsageMeter used={4800} limit={5000} label="API calls" unit="calls" />
            <UsageMeter used={2400} limit={2500} label="Storage" unit="MB" />
        </div>
    ),
};
