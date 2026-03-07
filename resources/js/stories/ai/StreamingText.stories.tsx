import type { Meta, StoryObj } from '@storybook/react';
import React from 'react';

import { StreamingText } from '@/components/ai/streaming-text';

const meta: Meta<typeof StreamingText> = {
    title: 'AI/StreamingText',
    component: StreamingText,
    tags: ['autodocs'],
    parameters: { layout: 'centered' },
    argTypes: {
        text: { control: 'text' },
        isStreaming: { control: 'boolean' },
        speed: { control: { type: 'range', min: 5, max: 100, step: 5 } },
    },
};

export default meta;
type Story = StoryObj<typeof StreamingText>;

export const Static: Story = {
    args: {
        text: 'This is a fully loaded AI response. All characters are visible immediately.',
        isStreaming: false,
    },
    render: (args) => <div className="max-w-sm text-sm"><StreamingText {...args} /></div>,
};

export const Streaming: Story = {
    args: {
        text: 'I am currently generating this response character by character to simulate streaming output from the AI model.',
        isStreaming: true,
        speed: 20,
    },
    render: (args) => <div className="max-w-sm text-sm"><StreamingText {...args} /></div>,
};

export const LongResponse: Story = {
    args: {
        text: 'Based on your query, here is a comprehensive analysis of the topic. The key factors to consider are performance, scalability, and maintainability. Each of these dimensions requires careful thought and deliberate architectural decisions.',
        isStreaming: false,
    },
    render: (args) => <div className="max-w-md text-sm leading-relaxed"><StreamingText {...args} /></div>,
};
