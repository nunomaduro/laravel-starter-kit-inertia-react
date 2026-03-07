import type { Meta, StoryObj } from '@storybook/react';
import React from 'react';

import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';

const meta: Meta = {
    title: 'Data Display/Avatar',
    component: Avatar,
    tags: ['autodocs'],
    parameters: { layout: 'centered' },
};

export default meta;

export const WithImage: StoryObj = {
    render: () => (
        <Avatar>
            <AvatarImage src="https://github.com/shadcn.png" alt="shadcn" />
            <AvatarFallback>SC</AvatarFallback>
        </Avatar>
    ),
};

export const WithFallback: StoryObj = {
    render: () => (
        <Avatar>
            <AvatarFallback>JD</AvatarFallback>
        </Avatar>
    ),
};

export const Sizes: StoryObj = {
    render: () => (
        <div className="flex items-end gap-4">
            {['size-6', 'size-8', 'size-10', 'size-12', 'size-16'].map((sz, i) => (
                <Avatar key={sz} className={sz}>
                    <AvatarFallback className="text-[10px]">{['XS', 'SM', 'MD', 'LG', 'XL'][i]}</AvatarFallback>
                </Avatar>
            ))}
        </div>
    ),
};

export const Group: StoryObj = {
    render: () => (
        <div className="flex -space-x-2">
            {['JD', 'AB', 'KR', 'ML'].map((initials) => (
                <Avatar key={initials} className="size-9 ring-2 ring-background">
                    <AvatarFallback className="text-xs">{initials}</AvatarFallback>
                </Avatar>
            ))}
            <div className="flex size-9 items-center justify-center rounded-full bg-muted text-xs font-medium ring-2 ring-background">
                +5
            </div>
        </div>
    ),
};
