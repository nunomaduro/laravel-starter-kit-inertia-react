import type { Meta, StoryObj } from '@storybook/react';
import { UsersIcon } from 'lucide-react';

import { StatCard } from '@/components/ui/stat-card';

const meta: Meta<typeof StatCard> = {
    title: 'Data Display/StatCard',
    component: StatCard,
    tags: ['autodocs'],
    parameters: { layout: 'centered' },
    argTypes: {
        title: { control: 'text' },
        isLoading: { control: 'boolean' },
    },
};

export default meta;
type Story = StoryObj<typeof StatCard>;

export const Default: Story = {
    args: {
        title: 'Total Users',
        value: '12,345',
        description: 'Active accounts this month',
    },
};

export const WithTrend: Story = {
    args: {
        title: 'Monthly Revenue',
        value: '$48,230',
        description: 'vs last month',
        trend: { value: 12.5, label: '12.5%', direction: 'up' },
    },
};

export const WithIcon: Story = {
    args: {
        title: 'Active Users',
        value: '3,782',
        description: 'Last 30 days',
        icon: <UsersIcon className="size-4 text-muted-foreground" />,
        trend: { value: -3.2, direction: 'down' },
    },
};

export const Loading: Story = {
    args: {
        title: 'Orders',
        value: '—',
        isLoading: true,
    },
};

export const Grid: Story = {
    render: () => (
        <div className="grid w-[600px] grid-cols-2 gap-4">
            <StatCard
                title="Users"
                value="12,345"
                trend={{ value: 8, direction: 'up' }}
            />
            <StatCard
                title="Revenue"
                value="$48,230"
                trend={{ value: 12.5, direction: 'up' }}
            />
            <StatCard
                title="Churn"
                value="2.3%"
                trend={{ value: 0.5, direction: 'down' }}
            />
            <StatCard
                title="ARR"
                value="$578,760"
                trend={{ value: 15, direction: 'up' }}
            />
        </div>
    ),
};
