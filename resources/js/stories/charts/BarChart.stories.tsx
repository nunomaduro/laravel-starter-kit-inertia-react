import type { Meta, StoryObj } from '@storybook/react';

import { BarChart } from '@/components/charts/bar-chart';

const DATA = [
    { quarter: 'Q1', sales: 4200, returns: 320 },
    { quarter: 'Q2', sales: 5800, returns: 410 },
    { quarter: 'Q3', sales: 5100, returns: 380 },
    { quarter: 'Q4', sales: 7200, returns: 520 },
];

const meta: Meta<typeof BarChart> = {
    title: 'Charts/BarChart',
    component: BarChart,
    tags: ['autodocs'],
    parameters: { layout: 'centered' },
    argTypes: {
        horizontal: { control: 'boolean' },
        stacked: { control: 'boolean' },
        showGrid: { control: 'boolean' },
        showLegend: { control: 'boolean' },
        skeleton: { control: 'boolean' },
        height: { control: { type: 'range', min: 100, max: 600, step: 50 } },
    },
};

export default meta;
type Story = StoryObj<typeof BarChart>;

export const Vertical: Story = {
    args: { data: DATA, dataKeys: ['sales'], xKey: 'quarter', height: 300 },
    render: (args) => (
        <div className="w-[500px]">
            <BarChart {...args} />
        </div>
    ),
};

export const Grouped: Story = {
    args: {
        data: DATA,
        dataKeys: ['sales', 'returns'],
        xKey: 'quarter',
        showLegend: true,
        height: 300,
    },
    render: (args) => (
        <div className="w-[500px]">
            <BarChart {...args} />
        </div>
    ),
};

export const Horizontal: Story = {
    args: {
        data: DATA,
        dataKeys: ['sales'],
        xKey: 'quarter',
        horizontal: true,
        height: 300,
    },
    render: (args) => (
        <div className="w-[500px]">
            <BarChart {...args} />
        </div>
    ),
};

export const Stacked: Story = {
    args: {
        data: DATA,
        dataKeys: ['sales', 'returns'],
        xKey: 'quarter',
        stacked: true,
        showLegend: true,
        height: 300,
    },
    render: (args) => (
        <div className="w-[500px]">
            <BarChart {...args} />
        </div>
    ),
};
