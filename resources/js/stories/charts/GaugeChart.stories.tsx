import type { Meta, StoryObj } from '@storybook/react';
import React from 'react';

import { GaugeChart } from '@/components/charts/gauge-chart';

const meta: Meta<typeof GaugeChart> = {
    title: 'Charts/GaugeChart',
    component: GaugeChart,
    tags: ['autodocs'],
    parameters: { layout: 'centered' },
    argTypes: {
        value: { control: { type: 'range', min: 0, max: 100 } },
        max: { control: { type: 'number' } },
        showValue: { control: 'boolean' },
        skeleton: { control: 'boolean' },
        size: { control: { type: 'range', min: 80, max: 300, step: 20 } },
    },
};

export default meta;
type Story = StoryObj<typeof GaugeChart>;

export const Default: Story = {
    args: { value: 72, label: 'Health Score', sublabel: 'Good' },
};

export const Low: Story = {
    args: { value: 23, label: 'Completion', sublabel: 'Needs work' },
};

export const Full: Story = {
    args: { value: 100, label: 'Uptime', sublabel: '99.99%' },
};

export const Grid: Story = {
    render: () => (
        <div className="flex gap-8 flex-wrap">
            <GaugeChart value={92} label="CPU" sublabel="92%" size={150} />
            <GaugeChart value={68} label="Memory" sublabel="68%" size={150} />
            <GaugeChart value={34} label="Storage" sublabel="34%" size={150} />
        </div>
    ),
};
