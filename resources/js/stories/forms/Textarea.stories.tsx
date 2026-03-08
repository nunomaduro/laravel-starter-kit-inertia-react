import type { Meta, StoryObj } from '@storybook/react';

import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

const meta: Meta<typeof Textarea> = {
    title: 'Forms/Textarea',
    component: Textarea,
    tags: ['autodocs'],
    parameters: { layout: 'centered' },
    argTypes: {
        disabled: { control: 'boolean' },
        placeholder: { control: 'text' },
        rows: { control: { type: 'number', min: 2, max: 20 } },
    },
};

export default meta;
type Story = StoryObj<typeof Textarea>;

export const Default: Story = {
    args: { placeholder: 'Enter your message…', rows: 4 },
    render: (args) => <Textarea className="w-80" {...args} />,
};

export const WithLabel: Story = {
    render: () => (
        <div className="grid w-80 gap-1.5">
            <Label htmlFor="bio">Bio</Label>
            <Textarea id="bio" placeholder="Tell us about yourself…" rows={4} />
            <p className="text-xs text-muted-foreground">Max 200 characters.</p>
        </div>
    ),
};

export const Disabled: Story = {
    args: { disabled: true, defaultValue: 'This field is read-only.', rows: 3 },
    render: (args) => <Textarea className="w-80" {...args} />,
};
