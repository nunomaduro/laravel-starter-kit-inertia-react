import type { Meta, StoryObj } from '@storybook/react';

import { Badge } from '@/components/ui/badge';

const meta: Meta<typeof Badge> = {
    title: 'Data Display/Badge',
    component: Badge,
    tags: ['autodocs'],
    parameters: { layout: 'centered' },
    argTypes: {
        variant: {
            control: 'select',
            options: [
                'default',
                'secondary',
                'destructive',
                'outline',
                'filled',
                'soft',
            ],
        },
        children: { control: 'text' },
    },
};

export default meta;
type Story = StoryObj<typeof Badge>;

export const Default: Story = {
    args: { children: 'Badge', variant: 'default' },
};

export const AllVariants: Story = {
    render: () => (
        <div className="flex flex-wrap gap-2">
            <Badge variant="default">Default</Badge>
            <Badge variant="secondary">Secondary</Badge>
            <Badge variant="destructive">Destructive</Badge>
            <Badge variant="outline">Outline</Badge>
            <Badge variant="filled" color="success">
                Success
            </Badge>
            <Badge variant="filled" color="warning">
                Warning
            </Badge>
            <Badge variant="filled" color="info">
                Info
            </Badge>
            <Badge variant="soft" color="success">
                Success Soft
            </Badge>
            <Badge variant="soft" color="error">
                Error Soft
            </Badge>
        </div>
    ),
};

export const InContext: Story = {
    render: () => (
        <div className="flex items-center gap-2">
            <span className="text-sm font-medium">Status</span>
            <Badge variant="filled" color="success">
                Active
            </Badge>
        </div>
    ),
};
