import type { Meta, StoryObj } from '@storybook/react';
import React from 'react';

import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const meta: Meta<typeof Input> = {
    title: 'Forms/Input',
    component: Input,
    tags: ['autodocs'],
    parameters: { layout: 'centered' },
    argTypes: {
        type: {
            control: 'select',
            options: ['text', 'email', 'password', 'number', 'search', 'tel', 'url'],
        },
        disabled: { control: 'boolean' },
        placeholder: { control: 'text' },
    },
};

export default meta;
type Story = StoryObj<typeof Input>;

export const Default: Story = {
    args: { placeholder: 'Enter text…', type: 'text' },
};

export const WithLabel: Story = {
    render: () => (
        <div className="grid w-72 gap-1.5">
            <Label htmlFor="email">Email</Label>
            <Input id="email" type="email" placeholder="you@example.com" />
        </div>
    ),
};

export const Disabled: Story = {
    args: { placeholder: 'Disabled input', disabled: true, value: 'Cannot edit this' },
};

export const Password: Story = {
    args: { type: 'password', placeholder: 'Enter password' },
};

export const WithError: Story = {
    render: () => (
        <div className="grid w-72 gap-1.5">
            <Label htmlFor="username">Username</Label>
            <Input id="username" aria-invalid className="border-destructive focus-visible:ring-destructive" defaultValue="taken@" />
            <p className="text-xs text-destructive">Username is already taken.</p>
        </div>
    ),
};
