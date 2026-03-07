import type { Meta, StoryObj } from '@storybook/react';
import React from 'react';

import { AreaChart } from '@/components/charts/area-chart';

const MONTHLY_DATA = [
    { month: 'Jan', users: 1200, revenue: 8400 },
    { month: 'Feb', users: 1500, revenue: 10500 },
    { month: 'Mar', users: 1350, revenue: 9450 },
    { month: 'Apr', users: 1800, revenue: 12600 },
    { month: 'May', users: 2100, revenue: 14700 },
    { month: 'Jun', users: 2400, revenue: 16800 },
    { month: 'Jul', users: 2250, revenue: 15750 },
    { month: 'Aug', users: 2700, revenue: 18900 },
    { month: 'Sep', users: 3000, revenue: 21000 },
    { month: 'Oct', users: 2850, revenue: 19950 },
    { month: 'Nov', users: 3300, revenue: 23100 },
    { month: 'Dec', users: 3600, revenue: 25200 },
];

const meta: Meta<typeof AreaChart> = {
    title: 'Charts/AreaChart',
    component: AreaChart,
    tags: ['autodocs'],
    parameters: { layout: 'centered' },
    argTypes: {
        stacked: { control: 'boolean' },
        showGrid: { control: 'boolean' },
        showLegend: { control: 'boolean' },
        showTooltip: { control: 'boolean' },
        skeleton: { control: 'boolean' },
        height: { control: { type: 'range', min: 100, max: 600, step: 50 } },
    },
};

export default meta;
type Story = StoryObj<typeof AreaChart>;

export const SingleSeries: Story = {
    args: {
        data: MONTHLY_DATA,
        dataKeys: ['users'],
        xKey: 'month',
        height: 300,
    },
    render: (args) => <div className="w-[600px]"><AreaChart {...args} /></div>,
};

export const MultiSeries: Story = {
    args: {
        data: MONTHLY_DATA,
        dataKeys: ['users', 'revenue'],
        xKey: 'month',
        showLegend: true,
        height: 300,
    },
    render: (args) => <div className="w-[600px]"><AreaChart {...args} /></div>,
};

export const Stacked: Story = {
    args: {
        data: MONTHLY_DATA,
        dataKeys: ['users', 'revenue'],
        xKey: 'month',
        stacked: true,
        showLegend: true,
        height: 300,
    },
    render: (args) => <div className="w-[600px]"><AreaChart {...args} /></div>,
};

export const LoadingSkeleton: Story = {
    args: {
        data: [],
        dataKeys: ['value'],
        xKey: 'date',
        skeleton: true,
        height: 300,
    },
    render: (args) => <div className="w-[600px]"><AreaChart {...args} /></div>,
};
