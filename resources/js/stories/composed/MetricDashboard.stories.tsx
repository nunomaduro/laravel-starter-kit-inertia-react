import type { Meta, StoryObj } from '@storybook/react';
import {
    ActivityIcon,
    DollarSignIcon,
    TrendingUpIcon,
    UsersIcon,
} from 'lucide-react';

import { MetricDashboard } from '@/components/composed/metric-dashboard';

const CHART_DATA = [
    { date: 'Jan', revenue: 8400, users: 1200 },
    { date: 'Feb', revenue: 10500, users: 1500 },
    { date: 'Mar', revenue: 9450, users: 1350 },
    { date: 'Apr', revenue: 12600, users: 1800 },
    { date: 'May', revenue: 14700, users: 2100 },
    { date: 'Jun', revenue: 16800, users: 2400 },
];

const METRICS = [
    {
        id: 'users',
        title: 'Total Users',
        value: '12,345',
        description: 'Active accounts',
        icon: <UsersIcon className="size-4" />,
        trend: { value: 8.2, direction: 'up' as const, label: '8.2%' },
    },
    {
        id: 'revenue',
        title: 'Monthly Revenue',
        value: '$48,230',
        description: 'vs last month',
        icon: <DollarSignIcon className="size-4" />,
        trend: { value: 12.5, direction: 'up' as const, label: '12.5%' },
    },
    {
        id: 'growth',
        title: 'Growth Rate',
        value: '23.4%',
        description: 'Month over month',
        icon: <TrendingUpIcon className="size-4" />,
        trend: { value: 3.1, direction: 'up' as const },
    },
    {
        id: 'churn',
        title: 'Churn Rate',
        value: '2.3%',
        description: 'This quarter',
        icon: <ActivityIcon className="size-4" />,
        trend: { value: -0.4, direction: 'down' as const },
    },
];

const meta: Meta<typeof MetricDashboard> = {
    title: 'Composed/MetricDashboard',
    component: MetricDashboard,
    tags: ['autodocs'],
    parameters: { layout: 'fullscreen' },
    argTypes: {
        chartType: { control: 'select', options: ['area', 'bar'] },
        columns: { control: 'select', options: [2, 3, 4] },
        isLoading: { control: 'boolean' },
    },
};

export default meta;
type Story = StoryObj<typeof MetricDashboard>;

export const Default: Story = {
    args: {
        metrics: METRICS,
        chartData: CHART_DATA,
        chartXKey: 'date',
        chartDataKeys: ['revenue'],
        chartTitle: 'Revenue Over Time',
        periodOptions: [
            { label: 'Last 6 months', value: '6m' },
            { label: 'Last year', value: '1y' },
            { label: 'All time', value: 'all' },
        ],
        columns: 4,
    },
    render: (args) => (
        <div className="bg-background p-6">
            <MetricDashboard {...args} />
        </div>
    ),
};

export const Loading: Story = {
    args: {
        metrics: METRICS,
        chartData: [],
        chartDataKeys: ['revenue'],
        chartXKey: 'date',
        isLoading: true,
    },
    render: (args) => (
        <div className="bg-background p-6">
            <MetricDashboard {...args} />
        </div>
    ),
};

export const BarChart: Story = {
    args: {
        metrics: METRICS.slice(0, 2),
        chartData: CHART_DATA,
        chartXKey: 'date',
        chartDataKeys: ['revenue', 'users'],
        chartType: 'bar',
        chartTitle: 'Revenue vs Users',
        columns: 2,
    },
    render: (args) => (
        <div className="bg-background p-6">
            <MetricDashboard {...args} />
        </div>
    ),
};
