import type { Meta, StoryObj } from '@storybook/react';
import React from 'react';

import { PieChart } from '@/components/charts/pie-chart';

const DATA = [
    { name: 'Direct', value: 400 },
    { name: 'Social', value: 300 },
    { name: 'Email', value: 200 },
    { name: 'Organic', value: 278 },
    { name: 'Other', value: 189 },
];

const meta: Meta<typeof PieChart> = {
    title: 'Charts/PieChart',
    component: PieChart,
    tags: ['autodocs'],
    parameters: { layout: 'centered' },
    argTypes: {
        donut: { control: 'boolean' },
        showLegend: { control: 'boolean' },
        showTooltip: { control: 'boolean' },
        skeleton: { control: 'boolean' },
        height: { control: { type: 'range', min: 100, max: 500, step: 50 } },
    },
};

export default meta;
type Story = StoryObj<typeof PieChart>;

export const Default: Story = {
    args: { data: DATA, height: 300 },
    render: (args) => <div className="w-[400px]"><PieChart {...args} /></div>,
};

export const Donut: Story = {
    args: { data: DATA, donut: true, height: 300 },
    render: (args) => <div className="w-[400px]"><PieChart {...args} /></div>,
};

export const NoLegend: Story = {
    args: { data: DATA, showLegend: false, height: 300 },
    render: (args) => <div className="w-[400px]"><PieChart {...args} /></div>,
};
