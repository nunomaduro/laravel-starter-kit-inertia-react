import type { Meta, StoryObj } from '@storybook/react';

import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';

const meta: Meta<typeof Checkbox> = {
    title: 'Forms/Checkbox',
    component: Checkbox,
    tags: ['autodocs'],
    parameters: { layout: 'centered' },
    argTypes: {
        disabled: { control: 'boolean' },
        defaultChecked: { control: 'boolean' },
    },
};

export default meta;
type Story = StoryObj<typeof Checkbox>;

export const Default: Story = {};

export const Checked: Story = {
    args: { defaultChecked: true },
};

export const Disabled: Story = {
    args: { disabled: true },
};

export const WithLabel: Story = {
    render: () => (
        <div className="flex items-center gap-2">
            <Checkbox id="terms" />
            <Label htmlFor="terms">I agree to the terms and conditions</Label>
        </div>
    ),
};

export const CheckboxGroup: Story = {
    render: () => (
        <div className="space-y-2">
            {['React', 'TypeScript', 'Tailwind CSS', 'Laravel'].map((tech) => (
                <div key={tech} className="flex items-center gap-2">
                    <Checkbox id={tech} defaultChecked={tech === 'React'} />
                    <Label htmlFor={tech}>{tech}</Label>
                </div>
            ))}
        </div>
    ),
};
