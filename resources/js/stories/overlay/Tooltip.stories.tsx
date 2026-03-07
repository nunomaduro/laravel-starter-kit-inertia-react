import type { Meta, StoryObj } from '@storybook/react';
import React from 'react';

import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';

const meta: Meta = {
    title: 'Overlay/Tooltip',
    component: Tooltip,
    tags: ['autodocs'],
    parameters: { layout: 'centered' },
    decorators: [(Story) => <TooltipProvider><Story /></TooltipProvider>],
};

export default meta;

export const Default: StoryObj = {
    render: () => (
        <Tooltip>
            <TooltipTrigger asChild>
                <Button variant="outline">Hover me</Button>
            </TooltipTrigger>
            <TooltipContent>This is a tooltip</TooltipContent>
        </Tooltip>
    ),
};

export const WithSide: StoryObj = {
    render: () => (
        <div className="flex gap-4 items-center">
            {(['top', 'right', 'bottom', 'left'] as const).map((side) => (
                <Tooltip key={side}>
                    <TooltipTrigger asChild>
                        <Button variant="outline" size="sm">{side}</Button>
                    </TooltipTrigger>
                    <TooltipContent side={side}>Tooltip on {side}</TooltipContent>
                </Tooltip>
            ))}
        </div>
    ),
};
