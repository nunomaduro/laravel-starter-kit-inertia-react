import type { Meta, StoryObj } from '@storybook/react';

import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';

const meta: Meta<typeof Tabs> = {
    title: 'Navigation/Tabs',
    component: Tabs,
    tags: ['autodocs'],
    parameters: { layout: 'centered' },
    argTypes: {
        defaultValue: { control: 'text' },
    },
};

export default meta;
type Story = StoryObj<typeof Tabs>;

export const Default: Story = {
    render: () => (
        <Tabs defaultValue="overview" className="w-96">
            <TabsList>
                <TabsTrigger value="overview">Overview</TabsTrigger>
                <TabsTrigger value="analytics">Analytics</TabsTrigger>
                <TabsTrigger value="reports">Reports</TabsTrigger>
            </TabsList>
            <TabsContent
                value="overview"
                className="mt-2 rounded-md border border-border p-4 text-sm text-muted-foreground"
            >
                Overview content goes here.
            </TabsContent>
            <TabsContent
                value="analytics"
                className="mt-2 rounded-md border border-border p-4 text-sm text-muted-foreground"
            >
                Analytics content goes here.
            </TabsContent>
            <TabsContent
                value="reports"
                className="mt-2 rounded-md border border-border p-4 text-sm text-muted-foreground"
            >
                Reports content goes here.
            </TabsContent>
        </Tabs>
    ),
};

export const WithDisabled: Story = {
    render: () => (
        <Tabs defaultValue="active" className="w-96">
            <TabsList>
                <TabsTrigger value="active">Active</TabsTrigger>
                <TabsTrigger value="disabled" disabled>
                    Disabled
                </TabsTrigger>
                <TabsTrigger value="other">Other</TabsTrigger>
            </TabsList>
            <TabsContent
                value="active"
                className="mt-2 rounded-md border border-border p-4 text-sm text-muted-foreground"
            >
                Active tab is selected.
            </TabsContent>
            <TabsContent
                value="other"
                className="mt-2 rounded-md border border-border p-4 text-sm text-muted-foreground"
            >
                Other tab content.
            </TabsContent>
        </Tabs>
    ),
};
