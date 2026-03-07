import type { Meta, StoryObj } from '@storybook/react';
import React from 'react';

import { LineChart } from '@/components/charts/line-chart';

const DATA = [
    { week: 'W1', signups: 120, churned: 8 },
    { week: 'W2', signups: 145, churned: 12 },
    { week: 'W3', signups: 132, churned: 7 },
    { week: 'W4', signups: 189, churned: 14 },
    { week: 'W5', signups: 210, churned: 9 },
    { week: 'W6', signups: 198, churned: 11 },
];

const meta: Meta<typeof LineChart> = {
    title: 'Charts/LineChart',
    component: LineChart,
    tags: ['autodocs'],
    parameters: { layout: 'centered' },
    argTypes: {
        curved: { control: 'boolean' },
        showDots: { control: 'boolean' },
        showGrid: { control: 'boolean' },
        showLegend: { control: 'boolean' },
        skeleton: { control: 'boolean' },
        height: { control: { type: 'range', min: 100, max: 600, step: 50 } },
    },
};

export default meta;
type Story = StoryObj<typeof LineChart>;

export const Default: Story = {
    args: { data: DATA, dataKeys: ['signups'], xKey: 'week', height: 300 },
    render: (args) => <div className="w-[500px]"><LineChart {...args} /></div>,
};

export const MultiLine: Story = {
    args: { data: DATA, dataKeys: ['signups', 'churned'], xKey: 'week', showLegend: true, height: 300 },
    render: (args) => <div className="w-[500px]"><LineChart {...args} /></div>,
};

export const Curved: Story = {
    args: { data: DATA, dataKeys: ['signups'], xKey: 'week', curved: true, showDots: true, height: 300 },
    render: (args) => <div className="w-[500px]"><LineChart {...args} /></div>,
};
