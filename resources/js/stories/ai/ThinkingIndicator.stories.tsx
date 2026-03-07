import type { Meta, StoryObj } from '@storybook/react';
import React from 'react';

import { ThinkingIndicator } from '@/components/ai/thinking-indicator';

const meta: Meta<typeof ThinkingIndicator> = {
    title: 'AI/ThinkingIndicator',
    component: ThinkingIndicator,
    tags: ['autodocs'],
    parameters: { layout: 'centered' },
    argTypes: {
        variant: { control: 'select', options: ['dots', 'pulse', 'bars'] },
        label: { control: 'text' },
    },
};

export default meta;
type Story = StoryObj<typeof ThinkingIndicator>;

export const Dots: Story = {
    args: { variant: 'dots', label: 'Thinking…' },
};

export const Pulse: Story = {
    args: { variant: 'pulse', label: 'Processing…' },
};

export const Bars: Story = {
    args: { variant: 'bars', label: 'Generating…' },
};

export const AllVariants: Story = {
    render: () => (
        <div className="flex flex-col gap-6">
            {(['dots', 'pulse', 'bars'] as const).map((v) => (
                <div key={v} className="flex items-center gap-3">
                    <ThinkingIndicator variant={v} />
                    <span className="text-sm text-muted-foreground capitalize">{v}</span>
                </div>
            ))}
        </div>
    ),
};
