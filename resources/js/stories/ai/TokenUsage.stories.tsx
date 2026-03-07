import type { Meta, StoryObj } from '@storybook/react';
import React from 'react';

import { TokenUsageDisplay } from '@/components/ai/token-usage';

const meta: Meta<typeof TokenUsageDisplay> = {
    title: 'AI/TokenUsage',
    component: TokenUsageDisplay,
    tags: ['autodocs'],
    parameters: { layout: 'centered' },
    argTypes: {
        maxTokens: { control: { type: 'number' } },
    },
};

export default meta;
type Story = StoryObj<typeof TokenUsageDisplay>;

export const Default: Story = {
    args: {
        usage: { prompt: 1245, completion: 382, total: 1627 },
    },
};

export const WithBudget: Story = {
    args: {
        usage: { prompt: 2840, completion: 710, total: 3550 },
        maxTokens: 8192,
    },
    render: (args) => <div className="w-72"><TokenUsageDisplay {...args} /></div>,
};

export const NearLimit: Story = {
    args: {
        usage: { prompt: 7200, completion: 600, total: 7800 },
        maxTokens: 8192,
    },
    render: (args) => <div className="w-72"><TokenUsageDisplay {...args} /></div>,
};
