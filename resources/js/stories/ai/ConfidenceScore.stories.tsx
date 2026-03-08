import type { Meta, StoryObj } from '@storybook/react';

import { ConfidenceScore } from '@/components/ai/confidence-score';

const meta: Meta<typeof ConfidenceScore> = {
    title: 'AI/ConfidenceScore',
    component: ConfidenceScore,
    tags: ['autodocs'],
    parameters: { layout: 'centered' },
    argTypes: {
        score: { control: { type: 'range', min: 0, max: 1, step: 0.01 } },
        showLabel: { control: 'boolean' },
        size: { control: 'select', options: ['sm', 'md', 'lg'] },
    },
};

export default meta;
type Story = StoryObj<typeof ConfidenceScore>;

export const High: Story = {
    args: { score: 0.92, showLabel: true, size: 'md' },
    render: (args) => (
        <div className="w-64">
            <ConfidenceScore {...args} />
        </div>
    ),
};

export const Medium: Story = {
    args: { score: 0.64, showLabel: true, size: 'md' },
    render: (args) => (
        <div className="w-64">
            <ConfidenceScore {...args} />
        </div>
    ),
};

export const Low: Story = {
    args: { score: 0.31, showLabel: true, size: 'md' },
    render: (args) => (
        <div className="w-64">
            <ConfidenceScore {...args} />
        </div>
    ),
};

export const Sizes: Story = {
    render: () => (
        <div className="w-64 space-y-4">
            {(['sm', 'md', 'lg'] as const).map((size) => (
                <div key={size} className="space-y-1">
                    <p className="text-xs text-muted-foreground">{size}</p>
                    <ConfidenceScore score={0.78} size={size} />
                </div>
            ))}
        </div>
    ),
};
