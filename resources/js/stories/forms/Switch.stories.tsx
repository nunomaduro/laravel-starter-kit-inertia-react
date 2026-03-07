import type { Meta, StoryObj } from '@storybook/react';
import React from 'react';

import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';

const meta: Meta<typeof Switch> = {
    title: 'Forms/Switch',
    component: Switch,
    tags: ['autodocs'],
    parameters: { layout: 'centered' },
    argTypes: {
        disabled: { control: 'boolean' },
        defaultChecked: { control: 'boolean' },
    },
};

export default meta;
type Story = StoryObj<typeof Switch>;

export const Default: Story = {};

export const Checked: Story = {
    args: { defaultChecked: true },
};

export const Disabled: Story = {
    args: { disabled: true },
};

export const WithLabel: Story = {
    render: () => (
        <div className="flex items-center gap-2">
            <Switch id="notifications" defaultChecked />
            <Label htmlFor="notifications">Enable notifications</Label>
        </div>
    ),
};

export const FormGroup: Story = {
    render: () => (
        <div className="space-y-4">
            {['Email notifications', 'Push notifications', 'SMS alerts', 'Weekly digest'].map((label, i) => (
                <div key={label} className="flex items-center justify-between rounded-lg border border-border p-4">
                    <div>
                        <p className="text-sm font-medium">{label}</p>
                        <p className="text-xs text-muted-foreground">Receive {label.toLowerCase()}</p>
                    </div>
                    <Switch defaultChecked={i % 2 === 0} />
                </div>
            ))}
        </div>
    ),
};
