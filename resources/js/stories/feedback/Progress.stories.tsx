import type { Meta, StoryObj } from '@storybook/react';

import { Progress } from '@/components/ui/progress';

const meta: Meta<typeof Progress> = {
    title: 'Feedback/Progress',
    component: Progress,
    tags: ['autodocs'],
    parameters: { layout: 'centered' },
    argTypes: {
        value: { control: { type: 'range', min: 0, max: 100 } },
    },
};

export default meta;
type Story = StoryObj<typeof Progress>;

export const Default: Story = {
    args: { value: 60 },
    render: (args) => <Progress {...args} className="w-80" />,
};

export const Empty: Story = {
    args: { value: 0 },
    render: (args) => <Progress {...args} className="w-80" />,
};

export const Full: Story = {
    args: { value: 100 },
    render: (args) => <Progress {...args} className="w-80" />,
};

export const MultipleStates: Story = {
    render: () => (
        <div className="w-80 space-y-4">
            {[
                { label: 'Storage', value: 72 },
                { label: 'Memory', value: 45 },
                { label: 'CPU', value: 28 },
                { label: 'Bandwidth', value: 93 },
            ].map(({ label, value }) => (
                <div key={label} className="space-y-1.5">
                    <div className="flex justify-between text-sm">
                        <span className="font-medium text-foreground">
                            {label}
                        </span>
                        <span className="text-muted-foreground">{value}%</span>
                    </div>
                    <Progress value={value} />
                </div>
            ))}
        </div>
    ),
};
