import type { Meta, StoryObj } from '@storybook/react';

import { Spinner } from '@/components/ui/spinner';

const meta: Meta<typeof Spinner> = {
    title: 'Feedback/Spinner',
    component: Spinner,
    tags: ['autodocs'],
    parameters: { layout: 'centered' },
    argTypes: {
        size: {
            control: 'select',
            options: ['xs', 'sm', 'md', 'lg', 'xl'],
        },
        variant: {
            control: 'select',
            options: ['default', 'muted', 'white', 'inherit'],
        },
    },
};

export default meta;
type Story = StoryObj<typeof Spinner>;

export const Default: Story = {
    args: { size: 'md', variant: 'default' },
};

export const AllSizes: Story = {
    render: () => (
        <div className="flex items-end gap-4">
            {(['xs', 'sm', 'md', 'lg', 'xl'] as const).map((size) => (
                <div key={size} className="flex flex-col items-center gap-1.5">
                    <Spinner size={size} />
                    <span className="text-xs text-muted-foreground">
                        {size}
                    </span>
                </div>
            ))}
        </div>
    ),
};

export const AllVariants: Story = {
    render: () => (
        <div className="flex flex-wrap gap-6">
            <div className="flex flex-col items-center gap-1.5">
                <Spinner variant="default" size="md" />
                <span className="text-xs text-muted-foreground">default</span>
            </div>
            <div className="flex flex-col items-center gap-1.5">
                <Spinner variant="muted" size="md" />
                <span className="text-xs text-muted-foreground">muted</span>
            </div>
            <div className="flex flex-col items-center gap-1.5 rounded bg-primary p-2">
                <Spinner variant="white" size="md" />
                <span className="text-xs text-primary-foreground">white</span>
            </div>
        </div>
    ),
};

export const InButton: Story = {
    render: () => (
        <button
            className="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground"
            disabled
        >
            <Spinner size="sm" variant="white" />
            Saving…
        </button>
    ),
};
