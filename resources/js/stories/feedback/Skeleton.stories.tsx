import type { Meta, StoryObj } from '@storybook/react';
import React from 'react';

import { Skeleton } from '@/components/ui/skeleton';

const meta: Meta = {
    title: 'Feedback/Skeleton',
    component: Skeleton,
    tags: ['autodocs'],
    parameters: { layout: 'centered' },
};

export default meta;

export const Default: StoryObj = {
    render: () => <Skeleton className="h-4 w-64" />,
};

export const CardSkeleton: StoryObj = {
    render: () => (
        <div className="flex items-center gap-4 w-80">
            <Skeleton className="size-12 rounded-full" />
            <div className="flex-1 space-y-2">
                <Skeleton className="h-4 w-full" />
                <Skeleton className="h-3 w-4/5" />
            </div>
        </div>
    ),
};

export const ArticleSkeleton: StoryObj = {
    render: () => (
        <div className="w-96 space-y-3">
            <Skeleton className="h-48 w-full rounded-lg" />
            <Skeleton className="h-5 w-3/4" />
            <Skeleton className="h-4 w-full" />
            <Skeleton className="h-4 w-5/6" />
            <Skeleton className="h-4 w-2/3" />
        </div>
    ),
};

export const TableSkeleton: StoryObj = {
    render: () => (
        <div className="w-[500px] space-y-2">
            {[...Array(5)].map((_, i) => (
                <div key={i} className="flex items-center gap-4 py-2">
                    <Skeleton className="size-8 rounded-full flex-shrink-0" />
                    <Skeleton className="h-4 w-32" />
                    <Skeleton className="h-4 flex-1" />
                    <Skeleton className="h-6 w-16 rounded-full" />
                </div>
            ))}
        </div>
    ),
};
