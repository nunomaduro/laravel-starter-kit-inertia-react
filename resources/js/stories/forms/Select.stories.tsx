import type { Meta, StoryObj } from '@storybook/react';

import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

const meta: Meta = {
    title: 'Forms/Select',
    component: Select,
    tags: ['autodocs'],
    parameters: { layout: 'centered' },
};

export default meta;

export const Default: StoryObj = {
    render: () => (
        <div className="grid w-64 gap-1.5">
            <Label>Country</Label>
            <Select>
                <SelectTrigger>
                    <SelectValue placeholder="Select a country" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="us">United States</SelectItem>
                    <SelectItem value="gb">United Kingdom</SelectItem>
                    <SelectItem value="au">Australia</SelectItem>
                    <SelectItem value="ca">Canada</SelectItem>
                    <SelectItem value="de">Germany</SelectItem>
                </SelectContent>
            </Select>
        </div>
    ),
};

export const WithPreselected: StoryObj = {
    render: () => (
        <Select defaultValue="gb">
            <SelectTrigger className="w-64">
                <SelectValue />
            </SelectTrigger>
            <SelectContent>
                <SelectItem value="us">United States</SelectItem>
                <SelectItem value="gb">United Kingdom</SelectItem>
                <SelectItem value="au">Australia</SelectItem>
            </SelectContent>
        </Select>
    ),
};

export const Disabled: StoryObj = {
    render: () => (
        <Select disabled>
            <SelectTrigger className="w-64">
                <SelectValue placeholder="Disabled" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem value="a">Option A</SelectItem>
            </SelectContent>
        </Select>
    ),
};
