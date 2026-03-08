import type { Meta, StoryObj } from '@storybook/react';

import { Sparkline } from '@/components/charts/sparkline';

const DATA = [
    { v: 10 },
    { v: 25 },
    { v: 18 },
    { v: 40 },
    { v: 32 },
    { v: 55 },
    { v: 48 },
    { v: 62 },
    { v: 58 },
    { v: 75 },
    { v: 68 },
    { v: 90 },
];

const meta: Meta<typeof Sparkline> = {
    title: 'Charts/Sparkline',
    component: Sparkline,
    tags: ['autodocs'],
    parameters: { layout: 'centered' },
    argTypes: {
        variant: { control: 'select', options: ['line', 'area'] },
        height: { control: { type: 'range', min: 20, max: 120, step: 10 } },
    },
};

export default meta;
type Story = StoryObj<typeof Sparkline>;

export const Line: Story = {
    args: { data: DATA, dataKey: 'v', variant: 'line', height: 40 },
    render: (args) => (
        <div className="w-40">
            <Sparkline {...args} />
        </div>
    ),
};

export const Area: Story = {
    args: { data: DATA, dataKey: 'v', variant: 'area', height: 40 },
    render: (args) => (
        <div className="w-40">
            <Sparkline {...args} />
        </div>
    ),
};

export const InContext: Story = {
    render: () => (
        <div className="flex items-center gap-6 rounded-lg border border-border bg-card p-4">
            <div>
                <p className="text-xs text-muted-foreground">Weekly Revenue</p>
                <p className="text-xl font-bold">$12,450</p>
                <p className="text-xs text-success">+8.2%</p>
            </div>
            <div className="flex-1">
                <Sparkline data={DATA} dataKey="v" variant="area" height={48} />
            </div>
        </div>
    ),
};
