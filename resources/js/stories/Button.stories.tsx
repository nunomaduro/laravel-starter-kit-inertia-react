import type { Meta, StoryObj } from '@storybook/react';
import React from 'react';

import { Button } from '@/components/ui/button';

const meta: Meta<typeof Button> = {
    title: 'UI/Button',
    component: Button,
    parameters: {
        layout: 'centered',
    },
    tags: ['autodocs'],
    argTypes: {
        variant: {
            control: 'select',
            options: [
                'default',
                'destructive',
                'outline',
                'secondary',
                'ghost',
                'link',
            ],
        },
        size: {
            control: 'select',
            options: ['default', 'sm', 'lg', 'icon'],
        },
    },
};

export default meta;

type Story = StoryObj<typeof Button>;

export const Default: Story = {
    args: {
        children: 'Button',
        variant: 'default',
    },
    render: (args) => (
        <Button {...args}>{args.children as React.ReactNode}</Button>
    ),
};

export const Secondary: Story = {
    args: {
        children: 'Secondary',
        variant: 'secondary',
    },
    render: (args) => (
        <Button {...args}>{args.children as React.ReactNode}</Button>
    ),
};

export const Outline: Story = {
    args: {
        children: 'Outline',
        variant: 'outline',
    },
    render: (args) => (
        <Button {...args}>{args.children as React.ReactNode}</Button>
    ),
};

export const Destructive: Story = {
    args: {
        children: 'Destructive',
        variant: 'destructive',
    },
    render: (args) => (
        <Button {...args}>{args.children as React.ReactNode}</Button>
    ),
};

export const Ghost: Story = {
    args: {
        children: 'Ghost',
        variant: 'ghost',
    },
    render: (args) => (
        <Button {...args}>{args.children as React.ReactNode}</Button>
    ),
};
